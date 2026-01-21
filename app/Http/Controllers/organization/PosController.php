<?php

namespace App\Http\Controllers\organization;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Pos;
use App\Models\PosCustomer;
use App\Models\PosItem;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StockCount;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{


    // Index Page funcation
    public function index()
    {
        $locations = [];
        if (auth()->user()->role_id <= 2) {
            $locations = Location::where('org_id', auth()->user()->organization_id)
                ->where('is_active', '1')
                ->get();
        }
        return view('organization.pos.index', compact('locations'));
    }

    // Inventory Page function

    public function stockCount(Request $request): JsonResponse
    {
        try {
            $query = StockCount::query()
                ->join('products', 'products.id', '=', 'stock_counts.product_id')
                ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
                ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
                ->join('product_units', function ($join) {
                    $join->on('product_units.product_id', '=', 'products.id')
                        ->where('product_units.is_base_unit', 1);
                })
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->join('units', 'units.id', '=', 'product_units.unit_id')
                ->where('suppliers.is_active', true)
                ->where('products.is_active', true)
                ->where('stock_counts.on_hand_quantity', '>', 0)
                ->select(
                    'stock_counts.*',
                    'products.id as product_id',
                    'products.is_sample',
                    'products.image as product_image',
                    'products.product_name',
                    'products.product_code',
                    'products.price',
                    'locations.name as location_name',
                    'units.unit_name as base_unit_name',
                    'suppliers.supplier_name',
                    'suppliers.supplier_slug',
                    'categories.category_name'
                );

            /**
             * ORGANIZATION FILTERING
             */
            if (auth()->user()->role_id == 1) {
                // Super Admin
                if ($request->filled('organization_id')) {
                    $query->where('products.organization_id', $request->organization_id);
                }
            } else {
                // Normal users
                $query->where('products.organization_id', auth()->user()->organization_id);
            }

            /**
             * LOCATION FILTER
             */

            if (auth()->user()->role_id > 2) {
                // Non-admin/manager roles: restrict to user's location
                $query->where('stock_counts.location_id', auth()->user()->location_id);
            } else if ($request->filled('location_id')) {
                $query->where('stock_counts.location_id', $request->location_id);
            }

            /**
             * SAMPLE PRODUCTS FILTER
             */
            if ($request->boolean('is_sample')) {
                $query->where('products.is_sample', 1);
            }

            /**
             * STOCK STATUS FILTER
             */
            if ($request->filled('status')) {
                match ($request->status) {
                    'low' => $query->whereRaw(
                        'stock_counts.on_hand_quantity <= stock_counts.alert_quantity'
                    ),
                    'expired' => $query->whereNotNull('stock_counts.expiry_date')
                        ->where('stock_counts.expiry_date', '<', now()),
                    'expiring_soon' => $query->whereBetween(
                        'stock_counts.expiry_date',
                        [now(), now()->addDays(30)]
                    ),
                    default => null,
                };
            }

            /**
             * SEARCH
             */
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('products.product_name', 'like', "%{$search}%")
                        ->orWhere('products.product_code', 'like', "%{$search}%")
                        ->orWhere('stock_counts.batch_number', 'like', "%{$search}%");
                });
            }

            /**
             * ORDERING (same as datasource)
             */
            $query->orderBy('products.product_name', 'asc')
                ->orderByRaw("CASE WHEN stock_counts.expiry_date IS NULL THEN 1 ELSE 0 END")
                ->orderBy('stock_counts.expiry_date', 'asc');

            return response()->json($query->get(), 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error fetching inventory',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // public function create()
    // {
    //     // Get locations for dropdown (only for admin/manager)
    //     $locations = [];
    //     if (auth()->user()->role_id <= 2) {
    //         $locations = Location::where('org_id', auth()->user()->organization_id)
    //             ->where('is_active', '1')
    //             ->get();
    //     }

    //     return view('organization.pos.create', compact('locations'));
    // }

    // public function search(Request $request)
    // {
    //     $query = $request->input('q');
    //     $locationId = $request->input('location_id');

    //     // Determine location based on role
    //     if (auth()->user()->role_id <= 2) {
    //         // Admin/Manager can select location
    //         if (!$locationId) {
    //             return response()->json([]);
    //         }
    //     } else {
    //         // Other roles use their assigned location
    //         $locationId = auth()->user()->location_id;
    //     }

    //     return StockCount::with([
    //         'product' => function ($q) use ($query) {
    //             $q->where(function ($q) use ($query) {
    //                 $q->where('product_name', 'like', "%{$query}%")
    //                     ->orWhere('product_code', 'like', "%{$query}%");
    //             });
    //         }
    //     ])
    //         ->whereHas('product', function ($q) use ($query) {
    //             $q->where(function ($q) use ($query) {
    //                 $q->where('product_name', 'like', "%{$query}%")
    //                     ->orWhere('product_code', 'like', "%{$query}%");
    //             });
    //         })
    //         ->where('on_hand_quantity', '>', 0)
    //         ->where('location_id', $locationId)
    //         ->where('organization_id', auth()->user()->organization_id)
    //         ->limit(20)
    //         ->get()
    //         ->map(function ($stock) {
    //             return [
    //                 'id' => $stock->id,
    //                 'expiry_date' => $stock->expiry_date,
    //                 'batch_number' => $stock->batch_number,
    //                 'product_name' => $stock->product->product_name,
    //                 'product_code' => $stock->product->product_code,
    //                 'on_hand_quantity' => $stock->on_hand_quantity,
    //                 'selling_price' => $stock->product->price,
    //             ];
    //         });
    // }

    public function customerSearch(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([
                'exists' => false,
                'customer' => null
            ]);
        }

        // Search by phone or email
        $customer = PosCustomer::where('organization_id', auth()->user()->organization_id)
            ->where(function ($q) use ($query) {
                $q->where('phone', $query)
                    ->orWhere('email', $query);
            })
            ->first();

        if ($customer) {
            return response()->json([
                'exists' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ]
            ]);
        }

        return response()->json([
            'exists' => false,
            'customer' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.batch_id' => 'required|exists:stock_counts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',

            'customer_id' => 'nullable|exists:customers,id',

            'payment_method' => 'required|string|in:cash,card,upi',
            'total_amount' => 'required|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
        ]);

        try {
            DB::beginTransaction();

            // Validate location access
            if (auth()->user()->role_id > 2 && $request->location_id != auth()->user()->location_id) {
                throw new \Exception('Unauthorized location access');
            }

            /**
             * Validate stock availability
             */
            foreach ($request->items as $item) {
                $stock = StockCount::find($item['batch_id']);

                if ($stock->on_hand_quantity < $item['quantity']) {
                    throw new \Exception(
                        "Insufficient stock for {$stock->product->product_name}. 
                    Available: {$stock->on_hand_quantity}, 
                    Requested: {$item['quantity']}"
                    );
                }
            }

            /**
             * Create Sale
             */
            $sale = Pos::create([
                'organization_id' => auth()->user()->organization_id,
                'location_id' => $request->location_id,
                'customer_id' => $request->customer_id,
                'payment_method' => $request->payment_method,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->total_amount, // card = full payment
                'change_amount' => 0,
                'sale_date' => now(),
                'created_by' => auth()->id(),
            ]);

            /**
             * Create Sale Items & Deduct Stock
             */
            $stockService = app(\App\Services\StockService::class);

            foreach ($request->items as $item) {
                $stock = StockCount::findOrFail($item['batch_id']);

                PosItem::create([
                    'pos_id' => $sale->id,
                    'stock_count_id' => $stock->id,
                    'product_id' => $stock->product_id,
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date,
                ]);

                /**
                 * Centralized stock deduction
                 */
                $stockService->decrementStock(
                    productId: $stock->product_id,
                    locationId: $request->location_id,
                    qtyToDeduct: $item['quantity'],
                    batchNumber: $stock->batch_number,
                    expiryDate: $stock->expiry_date
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale_id' => $sale->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    public function searchCustomers(Request $request): JsonResponse
    {
        logger()->info('Search Customers Request', $request->all());
        $search = $request->input('search');
        $limit = $request->input('limit', 5);

        if (empty(trim($search))) {
            return response()->json([
                'customers' => [],
                'count' => 0
            ]);
        }

        try {
            $customers = Customer::where(function ($query) use ($search) {
                $query->where('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_email', 'LIKE', "%{$search}%")
                    ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            })
                ->limit($limit)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->customer_name,
                        'email' => $customer->customer_email,
                        'phone' => $customer->customer_phone ?? null,
                    ];
                });

            return response()->json([
                'customers' => $customers,
                'count' => $customers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Customer search error: ' . $e->getMessage());

            return response()->json([
                'customers' => [],
                'count' => 0,
                'error' => 'An error occurred while searching'
            ], 500);
        }
    }


}