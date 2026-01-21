<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\Organization;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class InventoryComponent extends Component
{
    public $organizations = [];
    public $selectedOrganization = null;

    public function mount()
    {
        $this->organizations = Organization::orderBy('name')->where('is_active', true)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->get();
    }

    public function updatedSelectedOrganization($orgId)
    {
        $this->dispatch('inventoryFilterChanged', $orgId, null);
    }

    public function render()
    {
        return view('livewire.admin.inventory.inventory-component', [
            'selectedOrganization' => $this->selectedOrganization
        ]);
    }
}
