<?php

namespace App\Livewire\Tables\Reports;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Log;

final class ProductReport extends PowerGridComponent
{
    public string $tableName = 'product-report-zkls7p-table';

    public $selectedLocation = '';
    public $startDate = '';
    public $endDate = '';
    public $organization = '';
    public $location = '';

    protected $listeners = [
        'productLocationChanged' => 'updateFilters'
    ];

    #[On('productLocationChanged')]
    public function updateFilters($start_date, $end_date, $location, $organization)
    {

        $this->startDate = $start_date;
        $this->endDate = $end_date;
        $this->selectedLocation = $location;
        $this->organization = $organization;

        $this->resetPage();
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    public function validateDateRange()
    {
        if ($this->startDate && $this->endDate) {
            try {
                $sd = Carbon::createFromFormat('m/d/Y', $this->startDate);
                $this->startDate = $sd->format('Y-m-d');
            } catch (\Exception $e) {
            }
            try {
                $ed = Carbon::createFromFormat('m/d/Y', $this->endDate);
                $this->endDate = $ed->format('Y-m-d');
            } catch (\Exception $e) {
            }

            if ($this->endDate < $this->startDate) {
                [$this->startDate, $this->endDate] = [$this->endDate, $this->startDate];
            }
        }
    }

    use WithExport;


    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('export')
                ->striped()
                ->columnWidth([
                    2 => 30,
                ])
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            // PowerGrid::header()
            //     ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /**
     * Datasource for Product Report (PowerGrid)
     *
     * What this function does:
     * -----------------------
     * - Returns ONLY active products that actually participated in purchase orders
     * - Aggregates purchase + picking data per product
     * - Applies role-based organization filtering
     * - Applies optional date range & location filters
     *
     * Why this logic exists:
     * ----------------------
     * - Prevents showing products that were never ordered
     * - Ensures totals come ONLY from Purchase Orders & Pickings
     * - Keeps Super Admin (role_id = 1) behavior consistent across orgs
     * - Avoids duplicated rows by using subqueries instead of joins
     */
    public function datasource(): Builder
    {
        $user = auth()->user();

        // -------------------------------------------------
        // Normalize date inputs
        // -------------------------------------------------
        // Convert UI date filters into proper Carbon ranges.
        // - startDate → start of day
        // - endDate   → end of day
        // If no end date is provided, default to "now".
        $from = $this->startDate
            ? Carbon::parse($this->startDate)->startOfDay()
            : null;

        $to = $this->endDate
            ? Carbon::parse($this->endDate)->endOfDay()
            : Carbon::now();

        // -------------------------------------------------
        // Resolve organization scope
        // -------------------------------------------------
        // Super Admin (role_id = 1):
        //   - If no organization selected → include ALL active organizations
        //
        // Other users:
        //   - Restricted to selected org or their own org
        $organizationIds = ($user->role_id == 1 && empty($this->organization))
            ? DB::table('organizations')
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->pluck('id')
                ->toArray()
            : [(int) ($this->organization ?: $user->organization_id)];

        // -------------------------------------------------
        // Helper closures (reused across queries)
        // -------------------------------------------------

        /**
         * Apply organization filter to any query.
         * Keeps org filtering consistent across all subqueries.
         */
        $applyOrgFilter = function ($query, $column = 'organization_id') use ($organizationIds) {
            if (!empty($organizationIds)) {
                $query->whereIn($column, $organizationIds);
            }
        };

        /**
         * Apply date range filter to any timestamp column.
         * Supports:
         * - Both start & end date
         * - Only start date
         * - Only end date
         */
        $applyDateFilter = function ($query, $column = 'created_at') use ($from, $to) {
            if ($from && $to) {
                $query->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $query->where($column, '>=', $from);
            } elseif ($to) {
                $query->where($column, '<=', $to);
            }
        };

        // -------------------------------------------------
        // Resolve locations
        // -------------------------------------------------
        // If user selects a location → use only that
        // Otherwise → include all locations for selected organizations
        $locations = $this->selectedLocation
            ? [$this->selectedLocation]
            : DB::table('locations')
                ->whereIn('org_id', $organizationIds)
                ->pluck('id')
                ->toArray();

        // -------------------------------------------------
        // Base Product Query
        // -------------------------------------------------
        // - Only active products
        // - Only products belonging to active, non-deleted organizations
        // - Organization scope applied via role rules
        $productQuery = Product::query()
            ->where('is_active', true)
            ->whereIn('organization_id', function ($q) {
                $q->select('id')
                    ->from('organizations')
                    ->where('is_active', 1)
                    ->where('is_deleted', 0);
            })
            ->when(
                $organizationIds,
                fn($q) =>
                $q->whereIn('organization_id', $organizationIds)
            );

        // -------------------------------------------------
// Ensure product actually has purchase orders
// -------------------------------------------------
// WHY:
// - Ensures only products that have been ordered at least once appear in the report
// - Prevents showing unused products
// - Keeps product visibility independent from filters used for calculations
// - Detailed filters are applied only inside aggregate subqueries
        $productQuery->whereExists(function ($q) use ($organizationIds, $applyDateFilter) {
            $q->selectRaw(1)
                ->from('purchase_order_details')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
                ->whereColumn('purchase_order_details.product_id', 'products.id')
                ->whereIn('purchase_orders.organization_id', $organizationIds);

            $applyDateFilter($q, 'purchase_orders.created_at');
        });


        // -------------------------------------------------
        // Purchase base query (shared by subqueries)
        // -------------------------------------------------
        // This query acts as the foundation for:
        // - total orders
        // - total quantity ordered
        // - total spend
        $purchaseBase = DB::table('purchase_order_details')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
            ->whereColumn('purchase_order_details.product_id', 'products.id')
            ->whereIn('purchase_orders.location_id', $locations)
        ;

        $applyOrgFilter($purchaseBase, 'purchase_orders.organization_id');
        $applyDateFilter($purchaseBase, 'purchase_orders.created_at');

        // -------------------------------------------------
        // Purchase subqueries
        // -------------------------------------------------

        // Total distinct purchase orders per product
        $purchaseOrdersSub = (clone $purchaseBase)
            ->selectRaw('COUNT(DISTINCT purchase_order_details.purchase_order_id)');

        // Total quantity ordered per product
        $purchaseQtySub = (clone $purchaseBase)
            ->selectRaw('COALESCE(SUM(purchase_order_details.quantity), 0)');

        // Total purchase spend per product
        $purchaseSpendSub = (clone $purchaseBase)
            ->selectRaw('COALESCE(SUM(purchase_order_details.sub_total), 0)');

        // -------------------------------------------------
        // Picking base query
        // -------------------------------------------------
        // Used to calculate how much quantity was actually picked
        $pickingBase = DB::table('picking_details')
            ->join('pickings', 'pickings.id', '=', 'picking_details.picking_id')
            ->whereColumn('picking_details.product_id', 'products.id')
            ->whereIn('pickings.location_id', $locations);

        $applyOrgFilter($pickingBase, 'pickings.organization_id');
        $applyDateFilter($pickingBase, 'pickings.created_at');

        // Total picked quantity per product
        $pickedQtySub = (clone $pickingBase)
            ->selectRaw('COALESCE(SUM(picking_details.picking_quantity), 0)');

        // -------------------------------------------------
        // Attach all calculated fields to product query
        // -------------------------------------------------
        // Subqueries prevent row duplication and keep totals accurate
        $productQuery
            ->select('products.*')
            ->selectSub($purchaseOrdersSub, 'total_orders')
            ->selectSub($purchaseQtySub, 'total_qty_ordered')
            ->selectSub($purchaseSpendSub, 'total_spend')
            ->selectSub($pickedQtySub, 'total_picked_qty');

        // Order products by latest purchase order date
        // Shows products with recent purchase activity first
        $productQuery->orderByDesc(
            DB::table('purchase_orders')
                ->join(
                    'purchase_order_details',
                    'purchase_orders.id',
                    '=',
                    'purchase_order_details.purchase_order_id'
                )
                ->whereColumn('purchase_order_details.product_id', 'products.id')
                ->select('purchase_orders.created_at')
                ->latest()
                ->limit(1)
        );

        return $productQuery;
    }


    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            //  ->add('image', function ($item) {
            //     $images = json_decode($item->image, true);
            //     $imagePath = is_array($images) && !empty($images) ? $images[0] : $item->image;
            //     return '<img class="w-10 h-10 rounded-md" src="' . asset('storage/' . $imagePath) . '">';
            // })
            ->add('product_name')
            ->add('product_code')
            ->add('total_orders', function ($row) {
                return (int) ($row->total_orders ?? 0);
            })
            ->add('total_qty_ordered', function ($row) {
                return (float) ($row->total_qty_ordered ?? 0);
            })
            ->add('total_spend', function ($row) {
                return session('currency', '$') . ' ' . number_format((float) ($row->total_spend ?? 0), 2);
            })
            ->add('avg_monthly_ordered', function ($row) {
                $months = $this->calculateMonthsRange();
                $total = (float) ($row->total_qty_ordered ?? 0);
                $avg = $months > 0 ? ($total / $months) : $total;
                return round($avg, 2);
            })
            ->add('total_picked_qty', function ($row) {
                return (float) ($row->total_picked_qty ?? 0);
            })
            ->add('avg_monthly_picked', function ($row) {
                $months = $this->calculateMonthsRange();
                $total = (float) ($row->total_picked_qty ?? 0);
                $avg = $months > 0 ? ($total / $months) : $total;
                return round($avg, 2);
            });
    }

    private function calculateMonthsRange(): int
    {
        try {
            if ($this->startDate && $this->endDate) {
                $from = Carbon::createFromFormat('Y-m-d', $this->startDate);
                $to = Carbon::createFromFormat('Y-m-d', $this->endDate);
                $months = $from->diffInMonths($to) + 1;
                return max(1, (int) $months);
            }
            return 12;
        } catch (\Exception $e) {
            return 1;
        }
    }

    public function columns(): array
    {
        return [
            Column::make('Product Name', 'product_name')->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;'),
            Column::make('Product Code', 'product_code')->searchable()
                ->headerAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words')
                ->bodyAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words'),
            Column::make('Total Orders', 'total_orders')->sortable()
                ->headerAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words')
                ->bodyAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words'),
            Column::make('Total Qty Ordered', 'total_qty_ordered')->sortable()
                ->headerAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words')
                ->bodyAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words'),
            Column::make('Total Spent', 'total_spend')->sortable()
                ->headerAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words')
                ->bodyAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words'),
            Column::make('Avg Monthly Qty', 'avg_monthly_ordered')->sortable()
                ->headerAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words')
                ->bodyAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words'),
            Column::make('Picked Qty', 'total_picked_qty')->sortable(),
            Column::make('Avg Monthly Picked', 'avg_monthly_picked')->sortable()
                ->headerAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words')
                ->bodyAttribute('min-w-[110px] max-w-xl !whitespace-normal break-words'),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('product_name')->placeholder('Product Name')->operators([
                'contains'
            ]),
            Filter::inputText('product_code')->placeholder('Product Code')->operators([
                'contains'
            ]),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    /**
     * Step 1:
     * When user clicks "View", dispatch an event with
     * selected product ID and currently applied filters.
     * This keeps modal data consistent with report filters.
     */
    public function actions(Product $row): array
    {
        return [
            Button::add('view')
                ->slot('View')
                ->id()
                ->class('inline-flex items-center justify-center px-4 py-2 min-w-[80px] text-xs font-semibold text-white uppercase tracking-widest rounded-md border border-transparent bg-primary-md hover:bg-primary-lt focus:bg-primary-dk active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 transition duration-150 ease-in-out')
                ->dispatch('openProductOrdersModal', [
                    'product_id' => $row->id,
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'location' => $this->selectedLocation,
                    'organization' => $this->organization,
                ])
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
