<?php

namespace App\Livewire\Admin\FieldReps;

use App\Models\FieldRep;
use App\Models\Supplier;
use App\Models\Organization;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class FieldRepComponent extends Component
{
    // Holds current Field Rep ID while editing
    public $field_rep_id = '';

    // Dropdown data for organization & supplier
    public $organizations = [];
    public $suppliers = [];

     // Form fields
    public $organization_id = '';
    public $supplier_id = '';

    public $medrep_name = '';
    public $medrep_phone = '';
    public $medrep_email = '';

    public $editing = false;


     /**
     * Load active organizations and suppliers on component load
     */
    public function mount()
    {
        $this->organizations = Organization::query()
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->orderBy('name', 'asc')
            ->get();

        // ✅ Suppliers — ONLY active 
        $this->suppliers = Supplier::query()
            ->where('is_active', 1)
            ->orderBy('supplier_name', 'asc')
            ->get();
    }

    /**
     * Open Add Field Rep modal
     */
    public function openAddFieldRepModal()
    {
        $this->resetForm();
        $this->editing = false;
        $this->resetErrorBag();
        $this->resetValidation();

        $this->dispatch('open-modal', 'add-field-rep-modal');
    }

    /**
     * Create a new Field Rep record
     */
    public function createFieldRep()
    {
        $this->validate([
            'organization_id' => 'required',
            'supplier_id' => 'required',
            'medrep_name' => 'required|string|max:255',
            'medrep_phone' => 'nullable|string|max:20',
            'medrep_email' => 'nullable|email',
        ]);

        try {
            FieldRep::create([
                'organization_id' => $this->organization_id,
                'supplier_id' => $this->supplier_id,
                'medrep_name' => $this->medrep_name,
                'medrep_phone' => $this->medrep_phone,
                'medrep_email' => $this->medrep_email,
                'is_deleted' => 0,
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
     * Load selected Field Rep data into edit modal
     */
    #[On('edit-field-rep')]
    public function startEdit($rowId)
    {
        //added resetting of previous errors
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();
        $this->editing = true;
        $this->field_rep_id = $rowId;

        $rep = FieldRep::findOrFail($rowId);

        $this->organization_id = $rep->organization_id;
        $this->supplier_id = $rep->supplier_id;
        $this->medrep_name = $rep->medrep_name;
        $this->medrep_phone = $rep->medrep_phone;
        $this->medrep_email = $rep->medrep_email;

        $this->dispatch('open-modal', 'edit-field-rep-modal');

        Log::info("Editing Field Rep: " . $rep->medrep_name);
    }

    /**
     * Update existing Field Rep details
     */
    public function updateFieldRep()
    {
        $this->validate([
            'organization_id' => 'required',
            'supplier_id' => 'required',
            'medrep_name' => 'required|string|max:255',
            'medrep_phone' => 'nullable|string|max:20',
            'medrep_email' => 'nullable|email',
        ]);

        try {
            $rep = FieldRep::findOrFail($this->field_rep_id);

            $rep->update([
                'organization_id' => $this->organization_id,
                'supplier_id' => $this->supplier_id,
                'medrep_name' => $this->medrep_name,
                'medrep_phone' => $this->medrep_phone,
                'medrep_email' => $this->medrep_email,
            ]);

            $this->resetForm();
            $this->dispatch('close-modal', 'edit-field-rep-modal');
            $this->dispatch('pg:eventRefresh-field-reps-list-fftt-table');

            session()->flash('success', 'Field Representative updated successfully!');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            session()->flash('error', 'Something went wrong while updating.');
        }
    }

    /**
     * Soft delete Field Rep using is_deleted flag
     */
    public function deleteFieldRep()
    {
        $rep = FieldRep::findOrFail($this->field_rep_id);

        $rep->is_deleted = 1;
        $rep->save();

        $this->dispatch('close-modal', 'edit-field-rep-modal');
        session()->flash('success', 'Field Representative deleted successfully!');
        $this->dispatch('pg:eventRefresh-field-reps-list-fftt-table');
    }


    /**
     * Reset all form-related properties
     */
    public function resetForm()
    {
        $this->reset([
            'field_rep_id',
            'organization_id',
            'supplier_id',
            'medrep_name',
            'medrep_phone',
            'medrep_email',
            'editing',
        ]);
    }

    /**
     * Render Livewire view
     */
    public function render()
    {
        return view('livewire.admin.field-reps.field-rep-component');
    }
}
