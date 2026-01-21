<?php

namespace App\Livewire\Organization\FieldReps;

use App\Models\FieldRep;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class FieldRepComponent extends Component
{
    public $field_rep_id = '';

    // Dropdown
    public $suppliers = [];

    // Form fields
    public $supplier_id = '';
    public $medrep_name = '';
    public $medrep_phone = '';
    public $medrep_email = '';

    public $editing = false;

    /**
     * Load suppliers only
     */
    public function mount()
    {
        $this->suppliers = Supplier::where('is_active', 1)
            ->orderBy('supplier_name')
            ->get();
    }

    /**
     * Open Add Modal
     */
    public function openAddFieldRepModal()
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editing = false;

        $this->dispatch('open-modal', 'add-field-rep-modal');
    }

    /**
     * Create Field Rep 
     */
    public function createFieldRep()
    {
        $this->validate([
            'supplier_id'   => 'required',
            'medrep_name'   => 'required|string|max:255',
            'medrep_phone'  => 'nullable|string|max:20',
            'medrep_email'  => 'nullable|email',
        ]);

        try {
            FieldRep::create([
                'organization_id' => auth()->user()->organization_id, 
                'supplier_id'     => $this->supplier_id,
                'medrep_name'     => $this->medrep_name,
                'medrep_phone'    => $this->medrep_phone,
                'medrep_email'    => $this->medrep_email,
                'is_deleted'      => 0,
            ]);

            $this->resetForm();
            $this->dispatch('close-modal', 'add-field-rep-modal');
            $this->dispatch('pg:eventRefresh-field-reps-list-fftt-table');

            session()->flash('success', 'Field Representative created successfully!');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            session()->flash('error', 'Error creating Field Representative.');
        }
    }

    /**
     * Edit
     */
    #[On('edit-field-rep')]
    public function startEdit($rowId)
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editing = true;
        $this->field_rep_id = $rowId;

        $rep = FieldRep::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($rowId);

        $this->supplier_id    = $rep->supplier_id;
        $this->medrep_name    = $rep->medrep_name;
        $this->medrep_phone   = $rep->medrep_phone;
        $this->medrep_email   = $rep->medrep_email;

        $this->dispatch('open-modal', 'edit-field-rep-modal');
    }

    /**
     * Update
     */
    public function updateFieldRep()
    {
        $this->validate([
            'supplier_id'   => 'required',
            'medrep_name'   => 'required|string|max:255',
            'medrep_phone'  => 'nullable|string|max:20',
            'medrep_email'  => 'nullable|email',
        ]);

        $rep = FieldRep::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($this->field_rep_id);

        $rep->update([
            'supplier_id'   => $this->supplier_id,
            'medrep_name'   => $this->medrep_name,
            'medrep_phone'  => $this->medrep_phone,
            'medrep_email'  => $this->medrep_email,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'edit-field-rep-modal');
        $this->dispatch('pg:eventRefresh-field-reps-list-fftt-table');

        session()->flash('success', 'Field Representative updated successfully!');
    }

    /**
     * Delete
     */
    public function deleteFieldRep()
    {
        $rep = FieldRep::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($this->field_rep_id);

        $rep->update(['is_deleted' => 1]);

        $this->dispatch('close-modal', 'edit-field-rep-modal');
        $this->dispatch('pg:eventRefresh-field-reps-list-fftt-table');

        session()->flash('success', 'Field Representative deleted successfully!');
    }

    public function resetForm()
    {
        $this->reset([
            'field_rep_id',
            'supplier_id',
            'medrep_name',
            'medrep_phone',
            'medrep_email',
            'editing',
        ]);
    }

    public function render()
    {
        return view('livewire.organization.field-reps.field-rep-component');
    }
}
