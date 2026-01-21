<?php

namespace App\Livewire\Reports;

use App\Models\Organization;
use App\Models\PriceHistory;
use Livewire\Component;

class PriceHistoryReportComponent extends Component
{

    public ?string $fromDate = null;
    public ?string $toDate = null;

    public $selectedOrganization = '';
    public $organizations = [];

    public $selectedLocation = '';
    public $locations = [];

    // 🔥 NEW
    public bool $showHistoryModal = false;
    public $historyProductId = null;
    public $historyRows = [];


    public function mount()
    {
        $user = auth()->user();
        // Load organizations for the filter dropdown
        $this->organizations = Organization::where('is_active', true)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->orderBy('name')
            ->get();

        if ($user->role_id >= 2) {
            $this->locations = \App\Models\Location::where('org_id', $user->organization_id)
                ->orderBy('name')
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.reports.price-history-report-component');
    }

    #[\Livewire\Attributes\On('viewCostHistory')]
    public function viewPriceHistory($productId)
    {
        $this->historyProductId = $productId;

        $this->historyRows = PriceHistory::where('product_id', $productId)
            ->select('price', 'cost', 'created_at', 'changed_by')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();
        logger('viewPriceHistory called with productId: ' . $productId);
        $this->dispatch('open-modal', 'open-price-history-modal');
    }

    //Adding method to close modal
    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->dispatch('close-modal', 'open-price-history-modal'); // Close modal via Alpine/Modal
    }



}
