<?php

namespace App\Livewire\Organization\Pos;

use App\Models\Pos;
use App\Models\Location;
use App\Models\Organization;
use Livewire\Attributes\On;
use Livewire\Component;

class PosComponent extends Component
{

       // Modal properties
    public bool $showViewModal = false;
    public $selectedPos = null;
    public $selectedItems = [];
    public $selectedOrganization = '';
    public $organizations = [];
    public $selectedLocation = '';
    public $locations = [];

    public function mount()
    {
        $user = auth()->user();
        
        // Load organizations for role_id == 1 (Super Admin)
        if ($user->role_id == 1) {
            $this->organizations = Organization::where('is_active', true)
                ->where('is_deleted', 0)
                ->where('is_rep_org', 0)
                ->orderBy('name')
                ->get();
        }
        // Load locations for role_id >= 2
        if ($user->role_id >= 2) {
            $this->locations = Location::where('org_id', $user->organization_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }

    // Add organization filter method for role_id == 1
    public function updatedSelectedOrganization($id)
    {
        $this->dispatch('posOrganizationFilterChanged', $id);
        
        // If organization changes and user is role_id >= 2, update locations
        $user = auth()->user();
        if ($user->role_id >= 2 && $id) {
            $this->locations = Location::where('org_id', $id)
                ->orderBy('name')
                ->get();
            $this->selectedLocation = ''; // Reset location when organization changes
        }
    }

    // Add location filter method for role_id >= 2
    public function updatedSelectedLocation($id)
    {
        $this->dispatch('posLocationFilterChanged', $id);
    }


    
    #[On('view')]
    public function view($id)
    {
        // logger("Viewing POS ID: $id");
        $this->selectedPos = Pos::with([
            'organization',
            'location',
            'customer',
            'creator',
            'items.product'
        ])->find($id);

        $this->selectedItems = $this->selectedPos?->items ?? [];
        $this->showViewModal = true;

        // Dispatch event to open modal
        $this->dispatch('open-modal',  'pos-view-modal');
    }
    
    public function closeModal()
    {
        $this->showViewModal = false;
        $this->selectedPos = null;
        $this->selectedItems = [];
    }
    public function render()
    {
        return view('livewire.organization.pos.pos-component');
    }
}
