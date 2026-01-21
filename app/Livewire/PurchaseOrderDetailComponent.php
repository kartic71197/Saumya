<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use App\Models\Edi855;
use App\Models\PurchaseOrderDetail;
use App\Models\Organization;
use App\Models\Location;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class PurchaseOrderDetailComponent extends Component
{

    public $purchase_order;
    public $purchase_data = [];
    public $edi855data = [];
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public $selectedOrganization = ''; 
    public $organizations = [];
    public $selectedLocation = ''; 
    public $locations = [];

     public function mount()
    {
        $user = auth()->user();
        
        // Load organizations for super admin
        if ($user->role_id == 1) {
            $this->organizations = Organization::where('is_active', true)
                ->where('is_deleted', 0)
                ->where('is_rep_org', 0)
                ->orderBy('name')
                ->get();
        }
        
        // Load locations for non-admin users (role ≥2)
        if ($user->role_id >= 2) {
            $this->locations = Location::where('org_id', $user->organization_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedSelectedOrganization($id)
    {
        $this->dispatch('organizationFilterChanged', $id);
    }

    public function updatedSelectedLocation($id)
    {
        $this->dispatch('locationFilterChanged', $id);
    }

    private function formatDateForQuery(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            // Parse MM/DD/YYYY format and convert to YYYY-MM-DD
            return Carbon::createFromFormat('m/d/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function resetDateFilters(): void
    {
        $this->fromDate = null;
        $this->toDate = null;
    }

    #[\Livewire\Attributes\On('dateFilterUpdated')]
    public function updateDateFilters($fromDate, $toDate)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->dispatch('applyDateFilters', ['fromDate' => $this->formatDateForQuery($fromDate), 'toDate' => $this->formatDateForQuery($toDate)]);
    }
    #[On('purchase-receive-view-modal')]
    public function purchaseData($rowId)
    {
        $this->purchase_order = PurchaseOrder::where('id', $rowId)->first();
        $this->purchase_data = PurchaseOrderDetail::where('purchase_order_id', $rowId)->get();
        $this->dispatch('open-modal', 'purchase_report_details_modal');
    }
    #[On('previewEdi855')]
    public function previewEdi855($poNumber)
    {
        $this->edi855data = Edi855::where('purchase_order', $poNumber)->get();
        $this->dispatch('open-modal', 'preview_edi855_modal');
    }
    public function getReceiptNotesProperty()
    {
        if (!$this->purchase_order || empty($this->purchase_order->notes)) {
            return collect();
        }

        return collect($this->purchase_order->notes)
            ->map(function ($note) {
                return [
                    'user' => optional(\App\Models\User::find($note['user']))->name ?? 'Unknown',
                    'notes' => $note['notes'] ?? '',
                    'datetime' => $note['datetime'] ?? null,
                ];
            })
            ->sortByDesc('datetime')
            ->values();
    }

    public function getReceiptImagesProperty()
    {
        if (!$this->purchase_order || empty($this->purchase_order->packing_slips)) {
            return collect();
        }
        return collect($this->purchase_order->packing_slips)
            ->map(function ($entry) {
                return [
                    'user' => optional(\App\Models\User::find($entry['user']))->name ?? 'Unknown',
                    'images' => $entry['images'] ?? [],
                    'datetime' => $entry['datetime'] ?? null,
                ];
            })
            ->sortByDesc('datetime')
            ->values();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'purchase_report_details_modal');
    }

    public function render()
    {
        $formattedFromDate = $this->formatDateForQuery($this->fromDate);
        $formattedToDate = $this->formatDateForQuery($this->toDate);

        logger('Parent - Raw: ' . $this->fromDate . ' | ' . $this->toDate);
        logger('Parent - Formatted: ' . $formattedFromDate . ' | ' . $formattedToDate);

        return view('livewire.purchase-order-detail-component', [
            'formattedFromDate' => $formattedFromDate,
            'formattedToDate' => $formattedToDate,
        ]);
    }
}