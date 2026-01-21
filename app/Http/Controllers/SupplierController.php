<?php

namespace App\Http\Controllers;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->role_id != '1') {
            return redirect()->back()->with('error', 'You are not authorized to access this module.');
        }
        return view('admin.supplier.index');
    }

    /**
     * API to get supplier costs with filters
     */
    /*  Method workflow:
     * 1. Authenticates the user and retrieves their role and organization ID.
     * 2. Parses query parameters for time range, organization, and location filters.
     * 3. Constructs a date filter based on the selected time range.
     * 4. Builds a query joining purchase_orders with suppliers, applying necessary filters.
     * 5. Aggregates total costs and last order dates per supplier.
     * 6. Returns the aggregated data as a JSON response.
     */
    public function getSupplierCosts(Request $request)
    {
        try {

            $user = auth()->user();
            $roleId = $user->role_id;
            $orgId = $user->organization_id;

            $time = $request->query('time', 'all');
            $org = $request->query('org');   // SuperAdmin org
            $loc = $request->query('loc');   // User location

            $now = Carbon::now()->timezone('Asia/Kolkata');

            // Date filter
            $dateFilter = null;
            switch ($time) {
                case 'today':
                    $dateFilter = [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
                    break;
                case 'yesterday':
                    $dateFilter = [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()];
                    break;
                case 'week':
                    $dateFilter = [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
                    break;
                case 'last_week':
                    $dateFilter = [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()];
                    break;
                case 'month':
                    $dateFilter = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
                    break;
                case 'last_month':
                    $dateFilter = [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()];
                    break;
                case 'year':
                    $dateFilter = [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
                    break;
                case 'last_year':
                    $dateFilter = [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()];
                    break;
            }

            //  Start from purchase_orders
            $query = DB::table('purchase_orders')
                ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
                ->where('suppliers.is_active', 1);

            //  Organization security
            if ($roleId != 1) {
                // normal user → only their org
                $query->where('purchase_orders.organization_id', $orgId);
            } else {
                // superadmin → selected org
                if (!empty($org) && $org !== 'all') {
                    $query->where('purchase_orders.organization_id', $org);
                }
            }

            //  Location (only if passed)
            if (!empty($loc) && $loc != 0) {
                $query->where('purchase_orders.location_id', $loc);
            }

            // Date
            if ($dateFilter) {
                $query->whereBetween('purchase_orders.created_at', $dateFilter);
            }

            //  Aggregate
            $suppliers = $query
                ->select(
                    'suppliers.id',
                    'suppliers.supplier_name',
                    DB::raw('SUM(purchase_orders.total) as total_cost'),
                    DB::raw('MAX(purchase_orders.created_at) as last_order_date')
                )
                ->groupBy('suppliers.id', 'suppliers.supplier_name')
                ->orderByDesc('total_cost')
                ->get();

            return response()->json($suppliers);

        } catch (\Exception $e) {
            \Log::error('Supplier Costs API Error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }



}


