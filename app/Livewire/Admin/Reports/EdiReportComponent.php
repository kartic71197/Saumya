<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use App\Models\Organization;

class EdiReportComponent extends Component
{
    public $selectedOrganization = null;
    public $organizations = [];

    public function mount()
    {
        $this->organizations = Organization::where('is_active', true)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedOrganization($orgId)
    {
        // Dispatch an event to the PowerGrid table
        $this->dispatch('ediFilterChanged', $orgId);
    }

    public function render()
    {
        return view('livewire.admin.reports.edi-report-component');
    }
}
