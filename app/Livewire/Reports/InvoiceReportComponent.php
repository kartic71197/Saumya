<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Organization;

class InvoiceReportComponent extends Component
{

    public $fromDate = null;
    public $toDate = null;
    public $selectedOrganization = '';
    public $organizations = [];

    public $selectedLocation = '';
    public $locations = [];



    public function mount()
    {
        if (auth()->user()->role_id == 1) {$this->organizations = Organization::where('is_active', true)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->orderBy('name')
            ->get();
        }

        if (auth()->user()->role_id >= 2) {
        $this->locations = \App\Models\Location::where('is_active', true)
            ->where('org_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get();
        }
    }

    public function updatedSelectedOrganization($id)
    {
        $this->dispatch('invoiceFilterChanged', $id);
    }
    public function updatedSelectedLocation($id)
    {
        $this->dispatch('invoiceLocationFilterChanged', $id);
    }

    public function updatedFromDate($date)
    {
        $this->dispatch('invoiceDateChanged', $this->fromDate, $this->toDate);
    }

    public function updatedToDate($date)
    {
        $this->dispatch('invoiceDateChanged', $this->fromDate, $this->toDate);
    }

    public function render()
    {
        return view('livewire.reports.invoice-report-component');
    }
}
