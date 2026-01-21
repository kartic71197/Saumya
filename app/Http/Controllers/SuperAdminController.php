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
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SuperAdminController extends Controller
{

    protected $stock_onhand;
    protected $value_onhand;
    protected $stock_to_receive;
    protected $pending_value;
    protected $organizationId = '0';
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
    protected $org_list;

    protected $supplier_list = [];

    protected $get_supplier_data;

    protected $top_picks;
    protected $fromDate;
    protected $toDate;


    // Display a listing of the resource.
    // GET /super-admin/dashboard
    // Super Admin Dashboard
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

        $selectedOrganization = request('organization_id') ?? '0';

        return view("admin.dashboard.index", [
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
            'org_list' => $this->org_list,
            'supplier_list' => $this->supplier_list,
            'top_picks' => $this->top_picks ?? collect(), // Ensure it's always a collection
            'selectedOrganization' => $selectedOrganization,

        ]);
    }

    // Fetch active suppliers and calculate their total purchase value (used in supplier widget)
    public function get_supplier_data(): void
    {
        // Log entry for debugging (useful in production for tracing)
        Log::info('Fetching supplier data', [
            'user_id' => auth()->id(),
            'organization_id' => $this->organizationId ?? null
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

                    if ($this->organizationId > 0) {
                        $join->where('purchase_orders.organization_id', $this->organizationId);
                    }
                } else {
                    // Employee or other roles
                    $join->where('purchase_orders.organization_id', $user->organization_id);
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



    //it will update the dashboard data based on the selected organization and date filters without reloading the entire page.

    public function updateDashboard(Request $request)
    {
        $this->organizationId = $request->route('organization_id');
        $this->organization_id = auth()->user()->organization_id;
        // Get date parameters from request
        $this->fromDate = $request->input('from_date');
        $this->toDate = $request->input('to_date');

        // Log the received dates for debugging
        Log::info('Date parameters received:', [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'organization_id' => $this->organizationId
        ]);

        logger('Organization ID: ' . $this->organizationId);

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

        Log::info('UPDATE DASHBOARD LOW STOCK COUNT', [
            'count' => count($this->low_stock_products_list ?? [])
        ]);

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
            'org_list' => $this->org_list,
            'supplier_list' => $this->supplier_list,
            'top_picks' => $this->top_picks


        ];
    }

    // fetch organizations 
    public function get_location_orgs()
    {
        $user = auth()->user();

        $this->org_list = Organization::where('is_active', true)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->get();
    }


    // fetch recent purchase orders 
    //method workflow:
    //1. It starts by getting the authenticated user.
    //2. It builds a query to fetch purchase orders, including related data like supplier, location, and organization.
    //3. If a specific organization is selected (not '0'), it filters the purchase orders by that organization.
    //4. It further filters out purchase orders that are either 'completed' or 'canceled'.
    //5. The query is ordered by the latest purchase orders and limits the result to 10 entries.
    //6. Each purchase order is then mapped to expose the supplier progress step for frontend use.
    public function get_recent_purchase_orders_list()
    {
        $user = auth()->user();

        $query = PurchaseOrder::query()
            ->with(['purchaseSupplier', 'purchaseLocation', 'organization']);

        if ($this->organizationId != '0') {
            $query->where('purchase_orders.organization_id', $this->organizationId);
        }

        $this->recent_purchase_orders_list = $query
            ->whereNotIn('status', ['completed', 'canceled'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($po) {
                // 👇 expose accessor to JS
                $po->supplier_progress_step = $po->supplier_progress_step;
                return $po;
            });
        Log::info('[Dashboard] Recent Purchase Orders fetched', [
            'count' => $query->count(),
            'ids' => $this->recent_purchase_orders_list->pluck('id'),
        ]);
    }

    // fetch purchase order stats 
    //method workflow:
    //1. It retrieves the authenticated user.
    //2. It builds a query to count purchase orders, applying an organization filter if specified.
    //3. It counts purchase orders with 'ordered' and 'partial' statuses separately.
    //4. It builds another query to fetch carts, applying organization filters based on the user's role.
    //5. It groups the carts by supplier and counts the number of unique suppliers with items in the cart.
    //6. Finally, it assigns the counts to class properties for later use.
    public function get_purchase_order_stats()
    {
        $user = auth()->user();
        $query = PurchaseOrder::query();

        if ($this->organizationId != '0') {
            $query->where('organization_id', $this->organizationId);
        }
        $this->ordered_status_count = (clone $query)->where('status', 'ordered')->count();
        $this->partial_status_count = (clone $query)->where('status', 'partial')->count();

        $carts = Cart::query();
        if ($user->role_id != 1) {
            $query->where('organization_id', $user->organization_id);
        }
        if ($this->organizationId != '0') {
            $carts->where('organization_id', $this->organizationId);
        }

        $carts = $carts->get();
        $groupedBySupplier = $carts->groupBy(fn($cart) => optional($cart->product)->product_supplier_id);
        $this->in_cart_count = $groupedBySupplier->count();
    }

    // calculate stock on hand
    //method workflow:
    //1. It retrieves the authenticated user.
    //2. It builds a query on the StockCount model, applying an organization filter if specified.
    //3. It applies additional filters based on the user's role to restrict data access.
    //4. It applies a date range filter if both fromDate and toDate are provided
    //5. It counts the number of unique products with on_hand_quantity greater than zero.
    //6. Finally, it assigns the count to the stock_onhand property.
    public function stockOnhand()
    {
        $user = auth()->user();
        $query = StockCount::query();

        if ($this->organizationId != '0') {
            $query->where('organization_id', $this->organizationId);
        }

        if ($user->role_id != 1) {
            $query->whereHas('product', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        if ($user->role_id == 3) {
            $query->where('organization_id', $user->organiization_id);
        }
        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }
        // 🔹 Log after date filter
        Log::info('Stock On Hand - AFTER date range', [
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

        // Count unique products where on_hand_quantity > 0
        $this->stock_onhand = $query->where('on_hand_quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');
    }


    //calculate value on hand
    //method workflow:
    //1. It retrieves the authenticated user.
    //2. It builds a query on the StockCount model, applying an organization filter if specified.
    //3. It applies a date range filter if both fromDate and toDate are provided.
    //4. It logs the state of the query after applying the date filter for debugging purposes.
    //5. It retrieves the stock counts along with their associated products.

    public function valueOnhand()
    {
        // Fetch active locations for selected org
        $activeLocationIds = Location::where('is_active', true)
            ->where('org_id', $this->organizationId)
            ->pluck('id')
            ->toArray();

        // Base query
        $query = StockCount::query()
            ->whereHas('product', function ($q) {
                if ($this->organizationId != '0') {
                    $q->where('organization_id', $this->organizationId);
                }
            });

        // Filter by active locations only
        if (!empty($activeLocationIds)) {
            $query->whereIn('location_id', $activeLocationIds);
        }

        // Apply date filter if set
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        // Count & sum
        $this->stock_onhand = $query->where('on_hand_quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');

        $this->value_onhand = $query->with('product')
            ->get()
            ->sum(fn($row) => $row->on_hand_quantity * ($row->product->price ?? 0));

    }


    // calculate stock to receive
    //method workflow:
    //1. It retrieves the authenticated user.
    //2. It builds a query joining PurchaseOrder and PurchaseOrderDetails, filtering out completed
    //   and canceled orders.
    //3. It applies an organization filter if specified.
    //4. It applies a date range filter if both fromDate and toDate are provided.
    //5. It logs the state of the query after applying the date filter for debugging purposes.
    //6. It calculates the total stock to receive by summing the difference between quantity and
    //   received_quantity across all relevant purchase order details.
    public function stockToReceive()
    {
        $user = auth()->user();
        $query = PurchaseOrder::join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
            ->whereNotIn('purchase_orders.status', ['completed', 'canceled']);

        if ($this->organizationId != '0') {
            $query->where('purchase_orders.organization_id', $this->organizationId);
        }

        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('purchase_orders.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }
        // 🔹 AFTER DATE FILTER
        Log::info('Stock To Receive - AFTER date range', [
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

        $this->stock_to_receive = $query->sum(\DB::raw('quantity - received_quantity'));
    }

    // calculate pending value
    //method workflow:
    //1. It retrieves the authenticated user.
    //2. It builds a query on the PurchaseOrder model, filtering out completed and canceled orders.
    //3. It applies an organization filter if specified.
    //4. It applies a date range filter if both fromDate and toDate are provided.
    //5. It logs the state of the query after applying the date filter for debugging purposes
    //6. It retrieves the relevant purchase orders along with their purchased products and associated product details.
    //7. It calculates the pending value by summing the value of unreceived quantities across        
   public function pendingValue()
{
    $user = auth()->user();

    // 1️⃣ Get active locations for the selected organization (like user side)
    $activeLocationIds = Location::where('is_active', true)
        ->where('org_id', $this->organizationId)
        ->pluck('id')
        ->toArray();

    // 2️⃣ Join PurchaseOrders with PurchaseOrderDetails
    $query = PurchaseOrder::join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
        ->whereNotIn('purchase_orders.status', ['completed', 'canceled']);

    // 3️⃣ Filter by organization if selected
    if ($this->organizationId != '0') {
        $query->where('purchase_orders.organization_id', $this->organizationId);
    }

    // 4️⃣ Filter by active locations
    if (!empty($activeLocationIds)) {
        $query->whereIn('purchase_orders.location_id', $activeLocationIds);
    }

    // 5️⃣ Date filter
    if ($this->fromDate && $this->toDate) {
        $query->whereBetween('purchase_orders.created_at', [
            Carbon::parse($this->fromDate)->startOfDay(),
            Carbon::parse($this->toDate)->endOfDay(),
        ]);
    }

    // 6️⃣ Calculate pending value: sum of (quantity - received_quantity) * product price
    $pendingValue = $query
        ->join('products', 'products.id', '=', 'purchase_order_details.product_id')
        ->select('purchase_order_details.quantity', 'purchase_order_details.received_quantity', 'products.price')
        ->get()
        ->sum(function ($row) {
            $price = $row->price ?? 0;
            return ($row->quantity - $row->received_quantity) * $price;
        });

    $this->pending_value = number_format($pendingValue, 2, '.', '');
    logger('Super Admin Pending Value:', [$pendingValue]);
}


    // calculate low on stock
    //method workflow:
    //1. It builds a query joining StockCount with Products, Locations, and Suppliers, filtering for active suppliers and products.
    //2. It applies an organization filter if specified.
    //3. It applies a date range filter if both fromDate and toDate are provided.
    //4. It logs the state of the query after applying the date filter for debugging purposes.
    //5. It counts the number of products where the on_hand_quantity is less than the alert_quantity but greater than zero.
    //6. Finally, it assigns the count to the low_on_stock property.
    public function low_on_stock()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true);

        if ($this->organizationId != '0') {
            $query->where('stock_counts.organization_id', $this->organizationId);
        }

        // ✅ DATE FILTER (REQUIRED)
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }
        // 🔹 AFTER DATE FILTER
        Log::info('Low On Stock - AFTER date range', [
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

        $this->low_on_stock = $query->where('stock_counts.on_hand_quantity', '<', 'stock_counts.alert_quantity')
            ->where('stock_counts.on_hand_quantity', '>', '0')
            ->count();
    }

    // calculate product available
    //how it works:
    //1. It builds a query joining StockCount with Products, Locations, and Suppliers, filtering for active suppliers and products.
    //2. It applies an organization filter if specified.
    //3. It applies a date range filter if both fromDate and toDate are provided.
    //4. It logs the state of the query after applying the date filter for debugging purposes.
    //5. It counts the number of products where the on_hand_quantity is greater than zero.
    //6. Finally, it assigns the count to the product_avialable property.

    public function product_avialable()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true);

        if ($this->organizationId != '0') {
            $query->where('stock_counts.organization_id', $this->organizationId);
        }

        // ✅ DATE FILTER - Add this to all methods
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        // 🔹 AFTER DATE FILTER
        Log::info('Product Available - AFTER date range', [
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

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
            ->where('products.is_active', true);

        if ($this->organizationId != '0') {
            $query->where('stock_counts.organization_id', $this->organizationId);
        }

        // ✅ DATE FILTER - Add this to all methods
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }
        // 🔹 AFTER DATE FILTER
        Log::info('Product Not Available - AFTER date range', [
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

        $this->product_not_avialable = $query->where('stock_counts.on_hand_quantity', '<=', '0')
            ->count();
    }

    // calculate total products
    //how it works:
    //1. It builds a query joining StockCount with Products, Locations, and Suppliers, filtering for active suppliers and products.
    //2. It applies an organization filter if specified.
    //3. It applies a date range filter if both fromDate and toDate are provided.
    //4. It logs the state of the query after applying the date filter for debugging purposes.
    //5. It counts the total number of products.
    //6. Finally, it assigns the count to the total_products property and calculates the percentage of active products.
    public function total_products()
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true);

        if ($this->organizationId != '0') {
            $query->where('stock_counts.organization_id', $this->organizationId);
        }

        // 🔹 BEFORE DATE FILTER
        Log::info('Total Products - BEFORE date range', [
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

        // ✅ DATE FILTER
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('stock_counts.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        // 🔹 AFTER DATE FILTER
        Log::info('Total Products - AFTER date range', [
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'count' => $query->count(),
            'sample' => $query->take(5)->get()
        ]);

        $this->total_products = $query->count() == 0 ? 1 : $query->count();
        $this->active_products = ($this->product_avialable / $this->total_products) * 100;
    }



    // fetch low stock products list
    // how it works:
    //1. It retrieves the authenticated user.
    //2. It builds a query joining Mycatalog with Products, Locations, and Suppliers,
    //   filtering for active suppliers and products.
    //3. It applies an organization filter if specified.
    //4. It applies a date range filter if both fromDate and toDate are provided
    //5. It logs the state of the query after applying the date filter for debugging purposes.
    //6. It retrieves the results and filters them to include only those products
    //   where the total_quantity is less than or equal to the alert_quantity but greater than
    //   or equal to zero.

    public function get_low_stock_products_list()
    {
        $user = auth()->user();

        if (!$user) {
            $this->low_stock_products_list = collect();
            return;
        }

        Log::info('Fetching low stock products with filters', [
            'organization_id' => $this->organizationId,
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'user_role' => $user->role_id,
        ]);

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


        // ✅ Organization filter (optional)
        if (!empty($this->organizationId) && $this->organizationId != '0') {
            $query->where('locations.org_id', $this->organizationId);

            Log::info('Applied practice filter', ['organization_id' => $this->organizationId]);
        }

        // ✅ Date filter (optional)
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('mycatalogs.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);

            Log::info('Applied date filter', [
                'from' => $this->fromDate,
                'to' => $this->toDate
            ]);
        }

        // ✅ ALWAYS execute query
        $results = $query->get();

        Log::info('Low stock products count BEFORE alert filter', [
            'count' => $results->count()
        ]);

        // ✅ Alert quantity filter
        $this->low_stock_products_list = $results
            ->filter(
                fn($stock) =>
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

        if ($this->organizationId != '0') {
            $query->where('organization_id', $this->organizationId);
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

    // fetch top pickups
    //method workflow:
    //1. It builds a query joining picking_details with pickings, organizations, products,
    //   and suppliers to gather relevant data.
    //2. It selects necessary fields and calculates the total picked quantity for each product.
    //3. It applies an organization filter if specified.
    //4. It applies a date range filter if both fromDate and toDate are provided
    //5. It logs the state of the query after applying the date filter for debugging purposes.
    //6. It limits the results to the top 6 products based on total picked quantity.
    //7. Finally, it assigns the results to the top_picks property for use in views.
    public function topPickups()
    {
        $query = DB::table('picking_details')
            ->join('pickings', 'picking_details.picking_id', '=', 'pickings.id')
            ->join('organizations', 'pickings.organization_id', '=', 'organizations.id')
            ->join('products', 'picking_details.product_id', '=', 'products.id')
            ->leftJoin('suppliers', 'products.product_supplier_id', '=', 'suppliers.id')
            ->select(
                'products.id as product_id',
                'products.product_name',
                'products.product_code',
                'organizations.name as organization_name',
                'suppliers.supplier_name',
                DB::raw('SUM(picking_details.picking_quantity) as total_picked_qty'),
                DB::raw("
                CASE
                    WHEN COUNT(DISTINCT picking_details.picking_unit) > 1 THEN 'Mixed'
                    ELSE MAX(picking_details.picking_unit)
                END as picking_unit
            ")
            )
            ->groupBy(
                'products.id',
                'products.product_name',
                'products.product_code',
                'organizations.name',
                'suppliers.supplier_name'
            )
            ->orderByDesc('total_picked_qty');

        if (!empty($this->organizationId) && $this->organizationId != 0) {
            $query->where('pickings.organization_id', $this->organizationId);
        }

        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('pickings.created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ]);
        }

        $result = $query->limit(6)->get();

        // Assign to property for use in views
        $this->top_picks = $result;

        Log::info('topPickups fetched', [
            'count' => $result->count(),
            'organization_id' => $this->organizationId,
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
        ]);

        return $result;
    }


    /**
     * Summary of filterTopPickups
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * how it works:
     * 1. It retrieves filter parameters (organization_id, from_date, to_date) from the request.
     * 2. It logs the filter parameters for debugging purposes.
     * 3. It calls the topPickups method to fetch the filtered top pickups data.
     * 4. It returns the filtered top pickups data as a JSON response.
     */
    public function filterTopPickups(Request $request)
    {
        $this->organizationId = $request->query('organization_id') ?? 0;
        $this->fromDate = $request->query('from_date');
        $this->toDate = $request->query('to_date');

        Log::info('Top picks filter called', [
            'organization_id' => $this->organizationId,
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate
        ]);

        $topPicks = $this->topPickups();

        return response()->json([
            'top_picks' => $topPicks
        ]);
    }



    /**
     * Summary of getTopPickups
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * how it works:
     * 1. It retrieves filter parameters (organization_id, from_date, to_date) from the request.
     * 2. It sets the organization_id to the requested value or defaults to the authenticated user's organization_id.
     * 3. It calls the topPickups method to fetch the filtered top pickups data.
     * 4. It returns the filtered top pickups data as a JSON response.
     */
    public function getTopPickups(Request $request)
    {
        $this->organization_id = $request->organization_id ?? auth()->user()->organization_id ?? 0;
        $this->fromDate = $request->query('from_date');
        $this->toDate = $request->query('to_date');


        $time = $request->input('time');

        $this->topPickups(); // ← now it uses the filter!

        return response()->json([
            'top_picks' => $this->top_picks,
        ]);
    }

    /**
     * Summary of barCharData
     * @param Request $request
     * @param mixed $organization_id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * how it works:
     * 1. It retrieves filter parameters (organization_id, from_date, to_date) from the request.
     * 2. It logs the filter parameters for debugging purposes.
     * 3. It builds a base query on the PurchaseOrder model.
     * 4. It applies an organization filter if specified.
     * 5. It applies a date range filter based on the provided from_date and to_date or defaults to the last 5 months.
     * 6. It logs the count of records before grouping.
     * 7. It groups the purchase orders by year and month, calculating the total purchase cost for each month.
     * 8. It logs the grouped data for debugging purposes.
     * 9. It generates a complete list of months within the date range, ensuring months with no data are included with a total of zero.
     * 10. It logs the final response payload for debugging purposes.
     * 11. It returns the monthly purchase cost data as a JSON response.
     */
    public function barCharData(Request $request, $organization_id = 0)
    {
        $user = auth()->user();

        Log::info('BAR CHART REQUEST', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'organization_id_param' => $organization_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
        ]);

        // Base query
        $query = PurchaseOrder::query();

        // Org filter
        if ($organization_id != 0) {
            $query->where('organization_id', $organization_id);
            Log::info('BAR CHART: Organization filter applied', [
                'organization_id' => $organization_id
            ]);
        } else {
            Log::info('BAR CHART: No organization filter (Super Admin view)');
        }

        // Date range logic
        if ($request->from_date && $request->to_date) {
            $from = Carbon::parse($request->from_date)->startOfDay();
            $to = Carbon::parse($request->to_date)->endOfDay();

            Log::info('BAR CHART: Custom date filter applied', [
                'from' => $from,
                'to' => $to
            ]);
        } else {
            $from = now()->subMonths(5)->startOfMonth();
            $to = now()->endOfDay();

            Log::info('BAR CHART: Default last 5 months applied', [
                'from' => $from,
                'to' => $to
            ]);
        }

        $query->whereBetween('created_at', [$from, $to]);

        // 🔍 LOG count before grouping
        Log::info('BAR CHART: Records count BEFORE group', [
            'count' => $query->count()
        ]);

        // Grouping
        $purchaseData = $query
            ->selectRaw("
            YEAR(created_at) as year,
            MONTH(created_at) as month_num,
            SUM(total) as total_purchase_cost
        ")
            ->groupBy('year', 'month_num')
            ->orderBy('year')
            ->orderBy('month_num')
            ->get();

        // 🔍 LOG grouped result
        Log::info('BAR CHART: Grouped data', [
            'rows' => $purchaseData->count(),
            'data' => $purchaseData
        ]);

        // Generate full month range
        $months = collect();
        $current = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();

        while ($current <= $end) {
            $key = $current->year . '-' . $current->month;
            $row = $purchaseData->first(
                fn($item) => $item->year == $current->year && $item->month_num == $current->month
            );

            $months->push([
                'month' => $current->format('M Y'),
                'total_purchase_cost' => $row ? (float) $row->total_purchase_cost : 0
            ]);

            $current->addMonth();
        }

        // 🔍 FINAL OUTPUT LOG
        Log::info('BAR CHART: Final response payload', [
            'months_count' => $months->count(),
            'payload' => $months
        ]);

        return response()->json($months);
    }





}
