<?php

namespace App\Http\Controllers;

use App\Models\BillToLocation;
use App\Models\Location;
use App\Models\Organization;
use App\Models\ShipToLocation;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index($organization_id)
    {
        $user = auth()->user();
        if ($user->role_id != 1 && $user->organization_id != $organization_id) {
            abort(403, 'Unauthorized access.');
        }
        $locations = Location::where('is_active', true)
            ->where('org_id', $organization_id)
            ->get();

        // Fetch all suppliers
        $suppliers = Supplier::where('verified', 1)->get();

        // Fetch existing billing data and structure it
        $billingData = BillToLocation::where('organization_id', $organization_id)
            ->get()
            ->groupBy(function ($item) {
                return $item->location_id . '-' . $item->supplier_id;
            });

        // Fetch existing shipping data and structure it
        $shippingData = ShipToLocation::where('organization_id', $organization_id)
            ->get()
            ->groupBy(function ($item) {
                return $item->location_id . '-' . $item->supplier_id;
            });

        return view("billing_and_shipping.index", compact("suppliers", "user", "locations", "billingData", "organization_id", "shippingData"));
    }
    // Controller method
    public function getSupplierData($organization_id, $supplier_id)
    {

        // logger($organization_id);
        $locations = Location::where('is_active', true)->where('org_id', $organization_id)->get();

        $billingData = [];
        $shippingData = [];

        foreach ($locations as $location) {
            $key = $location->id . '-' . $supplier_id;
            $billingData[$key] = BillToLocation::where('location_id', $location->id)
                ->where('supplier_id', $supplier_id)
                ->value('bill_to') ?? '';
            $shippingData[$key] = ShipToLocation::where('location_id', $location->id)
                ->where('supplier_id', $supplier_id)
                ->value('ship_to') ?? '';
        }

        return response()->json([
            'success' => true,
            'locations' => $locations,
            'billing_data' => $billingData,
            'shipping_data' => $shippingData
        ]);
    }
    public function billingUpdate(Request $request, $organization_id)
    {
        $user = auth()->user();
        $organizationId = $organization_id;

        if ($user->role_id != 1) {
            abort(403, 'Unauthorized access.');
        }

        // Validate incoming data
        $validatedData = $request->validate([
            'billingData' => 'nullable|array',
            'billingData.*.*' => 'nullable|string|max:255',
            'default_location' => 'nullable|exists:locations,id', // Validate default location exists
        ]);

        // Handle billing data updates
        if (isset($validatedData['billingData'])) {
            foreach ($validatedData['billingData'] as $locationId => $suppliers) {
                foreach ($suppliers as $supplierId => $billToValue) {
                    // Check if an entry already exists
                    $existingEntry = BillToLocation::where('organization_id', $organizationId)
                        ->where('location_id', $locationId)
                        ->where('supplier_id', $supplierId)
                        ->first();

                    if ($existingEntry) {
                        // Update existing entry
                        if ($billToValue == null) {
                            $existingEntry->delete();
                        } else {
                            $existingEntry->update([
                                'bill_to' => $billToValue,
                                'updated_by' => $user->id,
                            ]);
                        }
                    } else {
                        // Create new entry
                        if ($billToValue == null) {
                            continue;
                        }
                        BillToLocation::create([
                            'organization_id' => $organizationId,
                            'location_id' => $locationId,
                            'supplier_id' => $supplierId,
                            'bill_to' => $billToValue,
                            'created_by' => $user->id,
                            'updated_by' => $user->id,
                        ]);
                    }
                }
            }
        }

        // Handle setting default location
        if (!empty($validatedData['default_location'])) {
            $defaultLocationId = $validatedData['default_location'];

            // Set `is_default` to false for all locations in the organization
            Location::where('org_id', $organizationId)
                ->update(['is_default' => false]);

            // Set the selected location as default
            Location::where('org_id', $organizationId)
                ->where('id', $defaultLocationId)
                ->update(['is_default' => true]);
        }


        return response()->json([
            'success' => true
        ]);
    }
    public function shippingUpdate(Request $request, $organization_id)
    {
        $user = auth()->user();
        $organizationId = $organization_id;
        if ($user->role_id != 1) {
            abort(403, 'Unauthorized access.');
        }

        // Validate incoming data
        $validatedData = $request->validate([
            'shippingData' => 'nullable|array',
            'shippingData.*.*' => 'nullable|string|max:255',
        ]);

        // Loop through submitted billing data
        foreach ($validatedData['shippingData'] as $locationId => $suppliers) {
            foreach ($suppliers as $supplierId => $shipToValue) {
                // Check if an entry already exists
                $existingEntry = ShipToLocation::where('organization_id', $organizationId)
                    ->where('location_id', $locationId)
                    ->where('supplier_id', $supplierId)
                    ->first();

                if ($existingEntry) {
                    // Update existing entry
                    if ($shipToValue == null) {
                        $existingEntry->delete();
                    } else {
                        $existingEntry->update([
                            'ship_to' => $shipToValue,
                            'updated_by' => $user->id,
                        ]);
                    }
                } else {
                    // Create new entry
                    if ($shipToValue == null) {
                        continue;
                    }
                    ShipToLocation::create([
                        'organization_id' => $organizationId,
                        'location_id' => $locationId,
                        'supplier_id' => $supplierId,
                        'ship_to' => $shipToValue,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function billingShipping()
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'You are not authorized to access this page.');
        }
        $org_data = Organization::where('is_active', true)->get();
        return view('admin.billingShipping.index', compact('org_data'));
    }

    public function updateDefaultLocation(Request $request, $organization_id)
    {
        try {
            $user = auth()->user();

            if ($user->role_id > 2) {
                throw new \Exception('You do not have permission.');
            }

            $request->validate([
                'type' => 'required|in:billing,shipping',
                'location_id' => 'required|integer|exists:locations,id',
            ]);

            $column = $request->type === 'billing'
                ? 'is_default'
                : 'is_default_shipping';

            // Remove default from all locations
            Location::where('org_id', $organization_id)
                ->update([$column => false]);

            // Set new default
            Location::where('id', $request->location_id)
                ->where('org_id', $organization_id)
                ->update([$column => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default location updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




}
