<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductReportDetailsModal extends Component
{
    public $productId;
    public $productName;

    public $startDate;
    public $endDate;
    public $location;
    public $organization;

    public $orders = [];

    /**
     * Step 2:
     * Listen to event dispatched from Product Report
     * and receive product ID with filters.
     * This function loads all purchase orders for the selected product
     * applying the current filters (date, location, organization)
     */

    #[On('openProductOrdersModal')]
    public function loadProductOrders(
        $product_id,
        $start_date = null,
        $end_date = null,
        $location = null,
        $organization = null
    ) {
        $this->productId = $product_id;
        $this->startDate = $start_date;
        $this->endDate = $end_date;
        $this->location = $location;
        $this->organization = $organization;

        $this->productName = DB::table('products')
            ->where('id', $this->productId)
            ->value('product_name');

        // Fetch purchase orders where this product was ordered
        // Apply same filters used in report (date, location, organization)
        $this->orders = DB::table('purchase_order_details')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
            ->where('purchase_order_details.product_id', $this->productId)
            ->when(
                $this->organization,
                fn($q) =>
                $q->where('purchase_orders.organization_id', $this->organization)
            )
            ->when(
                $this->location,
                fn($q) =>
                $q->where('purchase_orders.location_id', $this->location)
            )
            ->when(
                $this->startDate && $this->endDate,
                fn($q) =>
                $q->whereBetween('purchase_orders.created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay(),
                ])
            )
            ->select(
                'purchase_orders.created_at',
                'purchase_orders.purchase_order_number',
                'purchase_order_details.quantity',
                'purchase_order_details.sub_total'
            )
            ->orderBy('purchase_orders.created_at', 'desc')
            ->get();

        // Open modal after data is ready
        $this->dispatch('open-modal', 'product_report_details_modal');
    }

    /**
     * Step 6: Render the modal view
     * This returns the Blade view where the orders will be displayed
     */
    
    public function render()
    {
        return view('livewire.reports.product-report-details-modal');
    }
}
