<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\StockCount;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

class SettingController extends Controller
{
    public function settings()
    {
        return view('organization.settings.index');
    }

    public function cycle_counts()
    {
        return view('organization.settings.cycle_count.index');
    }
    public function categories()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('categories_settings') || $user->role_id <= 2) {
            return view('organization.settings.category_settings.index');
        }
        return redirect()->back()->with('error', 'You do not have permission to view this page.');

    }
    public function inventory_adjustment()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('inventory_adjustments') || $user->role_id <= 2) {
            return view('organization.settings.inventory_adjustments.index');
        }
        return redirect()->back()->with('error', 'You do not have permission to view this page.');

    }

    public function inventory_transfer()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('inventory_transfers') || $user->role_id <= 2) {
            return view('organization.settings.inventory_transfers.index');
        }
        return redirect()->back()->with('error', 'You do not have permission to view this page.');

    }

    public function organization_settings()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('view_organization_data') || $user->role_id <= 2) {
            $org = Organization::find($user->organization_id);

            return view('organization.settings.organization_settings.index', compact('org'));
        }

        // Abort with unauthorized action message if the permission check fails
        return redirect()->back()->with('error', 'You do not have permission to view this page.');

    }

    public function general_settings()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('general_settings') || $user->role_id <= 2) {
            $organization = Organization::where('id', auth()->user()->organization_id)->first();
            return view('organization.settings.general_settings.index', compact('organization'));
        }
        return redirect()->back()->with('error', 'You do not have permission to view this page.');

    }

    public function general_settings_update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'currency' => 'sometimes|string|max:3',
            'timezone' => 'sometimes|string|max:50',
            'date_format' => 'sometimes|string',
            'time_format' => 'sometimes|string',
        ]);

        $organization = Organization::where('id', auth()->user()->organization_id)->first();
        $organization->update($validated);
        $user = auth()->user();
        if ($user->organization) {
            session([
                'currency' => $user->organization->currency,
                'timezone' => $user->organization->timezone,
                'date_format' => $user->organization->date_format,
                'time_format' => $user->organization->time_format,
            ]);
        }
        return redirect()->back()->with('status', 'Organization updated successfully');
    }
    public function manufacturer()
    {
        $user = auth()->user();
        $role = $user->role;
        // Check permission or specific role ID
        if ($role?->hasPermission('manufacturer_settings') || $user->role_id <= 2) {
            return view('organization.settings.manufacturer.index');
        }
        return redirect()->back()->with('error', 'You do not have permission to view this page.');
    }

    public function roles()
    {
        $user = auth()->user();
        $role = $user->role;
        // Check permission or specific role ID
        if ($role?->hasPermission('roles_settings') || $user->role_id <= 2) {
            return view('organization.settings.roles.index');
        }
        return redirect()->back()->with('error', 'You do not have permission to view this page.');

    }
    public function customer_settings()
    {
        return view('organization.settings.customer.index');
    }
    /**
     * Display the Field Representatives management page for an organization.
     *
     * This method simply returns the organization-side Field Reps view.
     */
    public function field_reps()
    {

        return view('organization.settings.field_reps.index');

    }

    public function getProducts(Request $request)
    {
        try {
            logger('product called');
            $locationId = $request->location_id;
            $search = $request->search ?? '';
            $categoryId = $request->category_id ?? '';
            $page = $request->page ?? 1;
            $perPage = 10;

            if (!$locationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location ID is required'
                ]);
            }

            $query = StockCount::with(['product', 'product.category'])
                ->where('location_id', $locationId)
                ->where('stock_counts.organization_id', auth()->user()->organization_id)
                ->whereHas('product', function ($q) {
                    $q->where('is_active', true);
                });

            // Apply search filter
            if ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('product_name', 'like', '%' . $search . '%')
                        ->orWhere('product_code', 'like', '%' . $search . '%');
                });
            }

            // Apply category filter
            if ($categoryId) {
                $query->whereHas('product', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            $total = $query->count();
            $lastPage = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;

            $products = $query->join('products', 'stock_counts.product_id', '=', 'products.id')
                ->orderBy('products.product_name')
                ->select('stock_counts.*')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            $formattedProducts = $products->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->product_name,
                    'product_code' => $item->product->product_code,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date,
                    'on_hand_quantity' => $item->on_hand_quantity ?? 0,
                    'category_name' => $item->product->category->category_name ?? 'N/A'
                ];
            });

            return response()->json([
                'success' => true,
                'products' => [
                    'data' => $formattedProducts,
                    'total' => $total,
                    'current_page' => (int) $page,
                    'last_page' => (int) $lastPage,
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products: ' . $e->getMessage()
            ]);
        }
    }
}