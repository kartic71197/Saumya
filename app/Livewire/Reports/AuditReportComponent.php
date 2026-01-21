<?php

namespace App\Livewire\Reports;

use App\Models\Organization;
use App\Models\Location;
use Carbon\Carbon;
use Livewire\Component;

class AuditReportComponent extends Component
{
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public $selectedOrganization = '';
    public $organizations = [];
    public $selectedLocation = '';

public $locations = [];


    public function mount()
    {
        // ADMIN
        if (auth()->user()->role_id == 1) {
            $this->organizations = Organization::where('is_active', true)
                ->where('is_deleted', 0)
                ->where('is_rep_org', 0)
                ->orderBy('name')
                ->get();
        } 
        // NORMAL USER
        else {
            $this->locations = Location::where('org_id', auth()->user()->organization_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedSelectedOrganization($id)
    {
        $this->dispatch('auditOrganizationFilterChanged', $id);
    }

    public function updatedSelectedLocation($id)
    {
        $this->dispatch('auditLocationFilterChanged', $id);
    }


    private function formatDateForQuery(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
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

    public function render()
    {
        $formattedFromDate = $this->formatDateForQuery($this->fromDate);
        $formattedToDate = $this->formatDateForQuery($this->toDate);

        \Log::info('AuditReportComponent rendering', [
            'organizations_count' => count($this->organizations),
            'selected_organization' => $this->selectedOrganization
        ]);

        return view('livewire.reports.audit-report-component', [
            'formattedFromDate' => $formattedFromDate,
            'formattedToDate' => $formattedToDate,
        ]);
    }
}