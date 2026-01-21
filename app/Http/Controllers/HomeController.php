<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\Organization;
use App\Models\PickingDetailsModel;
use App\Models\PurchaseOrder;
use App\Models\StockCount;
use App\Models\Supplier;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;

class HomeController extends Controller
{
    protected $stock_onhand;
    protected $value_onhand;
    protected $stock_to_receive;
    protected $pending_value;
    protected $locationId = '0';
    protected $low_on_stock;
    protected $product_avialable;
    protected $product_not_avialable;
    protected $total_products;
    protected $active_products;
    protected $low_stock_products_list = [];
    protected $get_low_stock_products_list;
    protected $get_products_not_avaialable_list;
    protected $products_not_avaialable_list = [];
    protected $ordered_status_count;
    protected $partial_status_count;
    protected $in_cart_count;
    protected $purchase_order_stats;
    protected $recent_purchase_orders_list = [];
    protected $get_recent_purchase_orders_list;
    protected $get_location_orgs;
    protected $locations_list;

    protected $supplier_list = [];

    protected $get_supplier_data;

    protected $top_picks;
    //added for date filter
    protected $fromDate;
    protected $toDate;



    public function repDashboard()
    {
        return view('medical_rep.dashboard');
    }

    public function index()
    {
        $this->stockOnhand();
        $this->valueOnhand();
        $this->stockToReceive();
        $this->pendingValue();
        $this->low_on_stock();
        $this->product_avialable();
        $this->product_not_avialable();
        $this->total_products();
        $this->total_products();
        $this->get_low_stock_products_list();
        $this->get_products_not_avaialable_list();
        $this->get_purchase_order_stats();
        $this->get_recent_purchase_orders_list();
        $this->get_location_orgs();
        $this->get_supplier_data();
        $this->topPickups();
        $selectedLocation = request('location_id') ?? $this->locationId;



        return view("dashboard.index", [
            'stock_onhand' => $this->stock_onhand,
            'value_onhand' => $this->value_onhand,
            'stock_to_receive' => $this->stock_to_receive,
            'pending_value' => $this->pending_value,
            'low_on_stock' => $this->low_on_stock,
            'product_avialable' => $this->product_avialable,
            'product_not_avialable' => $this->product_not_avialable,
            'total_products' => $this->total_products,
            'active_products' => $this->active_products,
            'low_stock_products_list' => $this->low_stock_products_list,
            'products_not_avaialable_list' => $this->get_products_not_avaialable_list,
            'ordered_status_count' => $this->ordered_status_count,
            'partial_status_count' => $this->partial_status_count,
            'in_cart_count' => $this->in_cart_count,
            'recent_purchase_orders_list' => $this->recent_purchase_orders_list,
            'locations_list' => $this->locations_list,
            'supplier_list' => $this->supplier_list,
            'top_picks' => $this->top_picks,
            'selectedLocation' => $selectedLocation

        ]);
    }

    public function get_supplier_data(): void
    {
        // Log entry for debugging (useful in production for tracing)
        Log::info('Fetching supplier data', [
            'user_id' => auth()->id(),
            'location_id' => $this->locationId ?? null,
            'user_organization_id' => auth()->user()->organization_id,
        ]);

        $user = auth()->user();

        // Start query for active suppliers
        $query = Supplier::query()
            ->where('is_active', true)
            ->leftJoin('purchase_orders', function ($join) use ($user) {

                $join->on('suppliers.id', '=', 'purchase_orders.supplier_id');

                // Global date filter
                if ($this->fromDate && $this->toDate) {
                    $join->whereBetween('purchase_orders.created_at', [
                        Carbon::parse($this->fromDate)->startOfDay(),
                        Carbon::parse($this->toDate)->endOfDay(),
                    ]);
                }
                if ($user->role_id == 2) {
                    // Admin
                    $join->where('purchase_orders.organization_id', $user->organization_id);

                    if ($this->locationId > 0) {
                        $join->where('purchase_orders.location_id', $this->locationId);
                    }
                } else {
                    // Employee or other roles
                    $join->where('purchase_orders.location_id', $user->location_id);
                }
            });

        // Select required fields with aggregate
        $this->supplier_list = $query->select(
            'suppliers.id',
            'suppliers.supplier_name',
            DB::raw('COALESCE(SUM(purchase_orders.total), 0) as total_cost')
        )
            ->groupBy('suppliers.id', 'suppliers.supplier_name')
            ->orderBy('suppliers.supplier_name') // Consistent order in production
            ->get();

        logger('count($this->supplier_list): ' . ($this->supplier_list));
    }


    public function updateDashboard(Request $request)
    {
        $this->locationId = $request->route('location_id');
        
        $this->organization_id = auth()->user()->organization_id;
        // Get date parameters from request
        $this->fromDate = $request->input('from_date');
        $this->toDate = $request->input('to_date');

        // Log the received dates for debugging
        Log::info('Date parameters received:', [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'location_id' => $this->locationId
        ]);

        logger('Location ID: ' . $this->locationId);

        $this->stockOnhand();
        $this->valueOnhand();
        $this->stockToReceive();
        $this->pendingValue();
        $this->low_on_stock();
        $this->product_avialable();
        $this->product_not_avialable();
        $this->total_products();
        $this->get_low_stock_products_list();
        $this->get_products_not_avaialable_list();
        $this->get_purchase_order_stats();
        $this->get_recent_purchase_orders_list();
        $this->get_location_orgs();
        $this->get_supplier_data();
        $this->topPickups();

        return [
            'stock_onhand' => $this->stock_onhand,
            'value_onhand' => $this->value_onhand,
            'stock_to_receive' => $this->stock_to_receive,
            'pending_value' => $this->pending_value,
            'low_on_stock' => $this->low_on_stock,
            'product_avialable' => $this->product_avialable,
            'product_not_avialable' => $this->product_not_avialable,
            'total_products' => $this->total_products,
            'active_products' => $this->active_products,
            'low_stock_products_list' => $this->low_stock_products_list,
            'products_not_avaialable_list' => $this->products_not_avaialable_list,
            'ordered_status_count' => $this->ordered_status_count,
            'partial_status_count' => $this->partial_status_count,
            'in_cart_count' => $this->in_cart_count,
            'recent_purchase_orders_list' => $this->recent_purchase_orders_list,
            'locations_list' => $this->locations_list,
            'supplier_list' => $this->supplier_list,
            'top_picks' => $this->top_picks


        ];
    }

    public function get_location_orgs()
    {
        $user = auth()->user();

        //removed organization list fetch as it's not used in the dashboard view
        $this->locations_list = Location::where('is_active', true)
            ->where('org_id', $user->organization_id)
            ->orderBy('name')
            ->get();
    }


    public function get_recent_purchase_orders_list()
{
    $user = auth()->user();
     // $query = PurchaseOrder::query()
        //     ->with(['purchaseSupplier', 'purchaseLocation'])
        //     ->whereHas('purchaseSupplier', function ($q) {
        //         $q->where('is_edi', 1); // Only EDI suppliers
        //     });

    $query = PurchaseOrder::query()
        ->with(['purchaseSupplier', 'purchaseLocation']);

    if ($this->locationId != '0') {
        $query->where('purchase_orders.location_id', $this->locationId);
    }
    if ($user->role_id != 1) {
        $query->where('organization_id', $user->organization_id);
    }

    // Fetch latest 10 records
    $this->recent_purchase_orders_list = $query
        ->latest()
        ->take(10)
        ->whereNotIn('status', ['completed', 'canceled'])
        ->get()
        ->map(function($po) {
            // Add supplier_progress_step to each order for JS
            $po->supplier_progress_step = $po->supplier_progress_step; // calls accessor
            return $po;
        });
}


    public function get_purchase_order_stats()
    {
        $user = auth()->user();
        $query = PurchaseOrder::query();

        if ($this->locationId != '0') {
            $query->where('location_id', $this->locationId);
        }
        if ($user->role_id != 1) {
            $query->where('organization_id', $user->organization_id);
        }
        if ($user->role_id == 3) {
            $query->where('location_id', $user->location_id);
        }
        $this->ordered_status_count = (clone $query)->where('status', 'ordered')->count();
        $this->partial_status_count = (clone $query)->where('status', 'partial')->count();

        $carts = Cart::query();
        if ($user->role_id != 1) {
            $query->where('organization_id', $user->organization_id);
        }
        if ($this->locationId != '0') {
            $carts->where('location_id', $this->locationId);
        }

        $carts = $carts->get();
        $groupedBySupplier = $carts->groupBy(fn($cart) => optional($cart->product)->product_supplier_id);
        $this->in_cart_count = $groupedBySupplier->count();
    }

    // public function stockOnhand()
    // {
    //     $user = auth()->user();
    //     $query = StockCount::query();

    //     if ($this->locationId != '0') {
    //         $query->where('location_id', $this->locationId);
    //     }
    //     if ($user->role_id != 1) {
    //         $query->where('organization_id', $user->organization_id);
    //     }
    //     if ($user->role_id == 3) {
    //         $query->where('location_id', $user->location_id);
    //     }

    //     $this->stock_onhand = $query->sum('on_hand_quantity');
    // }

    public function stockOnhand()
    {
        $user = auth()->user();
        $query = StockCount::query();

        if ($this->locationId != '0') {
            $query->where('location_id', $this->locationId);
        }

        if ($user->role_id != 1) {
            $query->whereHas('product', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        if ($user->role_id == 3) {
            $query->where('location_id', $user->location_id);
        }
        //  DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        // Count unique products where on_hand_quantity > 0
        $this->stock_onhand = $query->where('on_hand_quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');
    }


    public function valueOnhand()
    {
        $user = auth()->user();
        $query = StockCount::query();

        if ($this->locationId != '0') {
            $query->where('location_id', $this->locationId);
        }
        if ($user->role_id != 1) {
            $query->whereHas('product', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }
        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $this->value_onhand = $query->with('product')
            ->get()
            ->sum(fn($row) => $row->on_hand_quantity * ($row->product->price ?? 0));
        logger($this->value_onhand);
    }

    public function stockToReceive()
    {
        $user = auth()->user();
        $query = PurchaseOrder::join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
            ->whereNotIn('purchase_orders.status', ['completed', 'canceled']);

        if ($this->locationId != '0') {
            $query->where('purchase_orders.location_id', $this->locationId);
        }
        if ($user->role_id > '1') {
            $query->where('purchase_orders.organization_id', $user->organization_id);
        }

        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('purchase_orders.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }
        $this->stock_to_receive = $query->sum(\DB::raw('quantity - received_quantity'));
    }

    public function pendingValue()
    {
        $user = auth()->user();

        $query = PurchaseOrder::query()
            ->whereNotIn('status', ['completed', 'canceled']);

        if ($this->locationId != '0') {
            $query->where('location_id', $this->locationId);
        }
        // This condition is now redundant since we already filter by organization_id above
        if ($user->role_id != 1) {
            $query->where('purchase_orders.organization_id', $user->organization_id);
        }
        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('purchase_orders.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }
        $orders = $query->with('purchasedProducts.product')->get();

        $pendingValue = $orders->sum(function ($order) {
            return $order->purchasedProducts->sum(function ($product) {
                $price = $product->product->price ?? 0;
                return ($product->quantity - $product->received_quantity) * $price;
            });
        });
        $this->pending_value = number_format($pendingValue, 2, '.', '');
        logger($pendingValue);
    }


    public function low_on_stock()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('locations.org_id', auth()->user()->organization_id);

        if ($this->locationId != '0') {
            $query->where('stock_counts.location_id', $this->locationId);
        }

        if (auth()->user()->role_id != 1) {
            $query->where('stock_counts.organization_id', auth()->user()->organization_id);
        }

        // ✅ DATE FILTER (REQUIRED)
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $this->low_on_stock = $query->where('stock_counts.on_hand_quantity', '<', 'stock_counts.alert_quantity')
            ->where('stock_counts.on_hand_quantity', '>', '0')
            ->count();
    }

    public function product_avialable()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('locations.org_id', auth()->user()->organization_id);

        if ($this->locationId != '0') {
            $query->where('stock_counts.location_id', $this->locationId);
        }

        if (auth()->user()->role_id != 1) {
            $query->where('stock_counts.organization_id', auth()->user()->organization_id);
        }

        if (auth()->user()->role_id == 3) {
            $query->where('stock_counts.location_id', auth()->user()->location_id);
        }

        // ✅ DATE FILTER - Add this to all methods
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $this->product_avialable = $query->where('stock_counts.on_hand_quantity', '>', '0')
            ->count();
    }

    public function product_not_avialable()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('locations.org_id', auth()->user()->organization_id);

        if ($this->locationId != '0') {
            $query->where('stock_counts.location_id', $this->locationId);
        }

        if (auth()->user()->role_id != 1) {
            $query->where('stock_counts.organization_id', auth()->user()->organization_id);
        }

        if (auth()->user()->role_id == 3) {
            $query->where('stock_counts.location_id', auth()->user()->location_id);
        }

        // ✅ DATE FILTER - Add this to all methods
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $this->product_not_avialable = $query->where('stock_counts.on_hand_quantity', '<=', '0')
            ->count();
    }

    public function total_products()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('locations.org_id', auth()->user()->organization_id);

        if ($this->locationId != '0') {
            $query->where('stock_counts.location_id', $this->locationId);
        }

        if (auth()->user()->role_id != 1) {
            $query->where('stock_counts.organization_id', auth()->user()->organization_id);
        }

        if (auth()->user()->role_id == 3) {
            $query->where('stock_counts.location_id', auth()->user()->location_id);
        }

        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $this->total_products = $query->count() == 0 ? 1 : $query->count();
        $this->active_products = ($this->product_avialable / $this->total_products) * 100;
    }


   public function get_low_stock_products_list()
{
    $user = auth()->user();
    if (!$user) {
        $this->low_stock_products_list = collect();
        return;
    }


    $query = Mycatalog::query()
        ->join('products', 'products.id', '=', 'mycatalogs.product_id')
        ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
        ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
        ->where('suppliers.is_active', true)
        ->where('products.is_active', true)
        ->selectRaw('
            mycatalogs.product_id,
            mycatalogs.location_id,
            mycatalogs.total_quantity,
            mycatalogs.created_at,
            alert_quantity,
            par_quantity
        ')
        ->with(['location', 'product.supplier']);

    
    
    if ($user->role_id != 1) {
        $query->where('products.organization_id', $user->organization_id)
              ->where('locations.org_id', $user->organization_id);

        Log::info('Applied organization filter', [
            'organization_id' => $user->organization_id
        ]);
    }

    // Location filter
    if (!empty($this->locationId) && $this->locationId != '0') {
        $query->where('mycatalogs.location_id', $this->locationId);
        Log::info('Applied location filter', ['location_id' => $this->locationId]);
    }

    //  Date filter 
    if ($this->fromDate && $this->toDate) {
        $query->whereBetween('mycatalogs.created_at', [
            Carbon::parse($this->fromDate)->startOfDay(),
            Carbon::parse($this->toDate)->endOfDay(),
        ]);
    }

    //  ALWAYS execute query
    $results = $query->get();

    Log::info('Low stock products count BEFORE alert filter', [
        'count' => $results->count()
    ]);

    //  Alert quantity filter
    // Filter results based on alert_quantity and total_quantity
    $this->low_stock_products_list = $results
        ->filter(fn ($stock) =>
            $stock->total_quantity <= $stock->alert_quantity &&
            $stock->total_quantity >= 0
        )
        ->sortByDesc('total_quantity')
        ->values();

    Log::info('Low stock products count AFTER alert filter', [
        'count' => $this->low_stock_products_list->count()
    ]);
}



    public function get_products_not_avaialable_list()
    {
        $query = StockCount::query()
            ->where('on_hand_quantity', 0)
            ->whereHas('product')
            ->with('product');

        if (auth()->user()->role_id != 1) {
            $query->where('organization_id', auth()->user()->organization_id);
        }

        if ($this->locationId != '0') {
            $query->where('location_id', $this->locationId);
        }

        if (auth()->user()->role_id == 3) {
            $query->where('location_id', auth()->user()->location_id);
        }

        // ✅ DATE FILTER   
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $this->products_not_avaialable_list = $query->get();
    }

    public function topPickups()
{
    $user = auth()->user();
    // method to get top picked products based on picking details
    //removed time filter parameter as it's not used anymore

    $query = PickingDetailsModel::select(
        'picking_details.product_id',
        'locations.name as location_name',
        DB::raw('SUM(picking_details.picking_quantity) as total_picked_qty')
    )
        ->join('pickings', 'picking_details.picking_id', '=', 'pickings.id')
        ->join('locations', 'pickings.location_id', '=', 'locations.id')
        ->where('pickings.organization_id', $user->organization_id)
        ->groupBy('picking_details.product_id', 'locations.id', 'locations.name')
        ->orderByDesc('total_picked_qty')
        ->with(['product.supplier']);

    if ($this->locationId != 0) {
        $query->where('pickings.location_id', $this->locationId);
    }

    if ($this->fromDate && $this->toDate) {
        $query->whereBetween('pickings.created_at', [
            Carbon::parse($this->fromDate)->startOfDay(),
            Carbon::parse($this->toDate)->endOfDay(),
        ]);
    }

    $this->top_picks = $query->take(6)->get();
}

    public function filterTopPickups(Request $request)
    {
        Log::info('Top picks filter called');
        // Remove time parameter since we're not using it anymore
        $this->locationId = $request->query('location_id') ?? '0';
        $this->fromDate = $request->query('from_date');
        $this->toDate = $request->query('to_date');

        // Call without time filter parameter
        $this->topPickups();

        return response()->json([
            'top_picks' => $this->top_picks
        ]);
    }


    public function getTopPickups(Request $request)
    {
        $this->locationId = $request->location_id ?? auth()->user()->location_id ?? 0;
        $this->fromDate = $request->query('from_date');
        $this->toDate = $request->query('to_date');


        $time = $request->input('time');

        $this->topPickups(); // ← now it uses the filter!

        return response()->json([
            'top_picks' => $this->top_picks,
        ]);
    }

    public function barCharData(Request $request, $location_id)
{
    $user = auth()->user();

    // Organization logic is already handled at SuperAdmin level
    // So we directly scope data to the logged-in user's organization
    $query = PurchaseOrder::query()
        ->where('organization_id', $user->organization_id);

    // Apply location filter only when a specific location is selected
    // (0 means "All locations")
    if ($location_id != 0) {
        $query->where('location_id', $location_id);
    }

    // If custom date range is provided, use it
    // Otherwise default to last 6 months (for dashboard chart)
    if ($request->from_date && $request->to_date) {
        $query->whereBetween('created_at', [
            Carbon::parse($request->from_date)->startOfDay(),
            Carbon::parse($request->to_date)->endOfDay(),
        ]);
    } else {
        $query->where('created_at', '>=', now()->subMonths(5)->startOfMonth());
    }

    // Group data month-wise at DB level for better performance
    // This avoids multiple queries and heavy PHP loops
    return $query
        ->selectRaw("
            YEAR(created_at) as year,
            MONTH(created_at) as month_num,
            DATE_FORMAT(created_at, '%b %Y') as month,
            SUM(total) as total_purchase_cost
        ")
        ->groupBy('year', 'month_num', 'month')
        ->orderBy('year')
        ->orderBy('month_num')
        ->get();
}
}