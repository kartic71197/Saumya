<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Organization;
use Livewire\Component;

class PickingReportComponent extends Component
{
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public $selectedOrganization = ''; // Add this
    public $organizations = [];
    public $selectedLocation = '';
    public $locations = [];


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

    // Add organization filter method
    public function updatedSelectedOrganization($id)
    {
        $this->dispatch('pickingOrganizationFilterChanged', $id);
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

    public function render()
    {
        $formattedFromDate = $this->formatDateForQuery($this->fromDate);
        $formattedToDate = $this->formatDateForQuery($this->toDate);

        logger('Parent - Raw: ' . $this->fromDate . ' | ' . $this->toDate);
        logger('Parent - Formatted: ' . $formattedFromDate . ' | ' . $formattedToDate);

        return view('livewire.reports.picking-report-component', [
            'formattedFromDate' => $formattedFromDate,
            'formattedToDate' => $formattedToDate,
        ]);
        // return view('livewire.reports.picking-report-component');
    }
}
