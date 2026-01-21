<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class SupplierComponent extends Component
{

    public $id ='';
    public $supplier_name ='';

    public $supplier_slug ='';
    public $supplier_email ='';
    public $supplier_phone ='';
    public $supplier_address ='';
    public $supplier_city ='';
    public $supplier_state = '';
    public $supplier_country ='';
    public $supplier_zip ='';
    public $supplier_vat ='';
    public $is_active = false;
    public $int_type = 'NONE'; // EDI | EMAIL | NONE

    public $editing = false; // Flag to check if we are editing a supplier

    public $created_by ='';

    public $updated_by ='';

    public $supplier_id;


    public $selectedCountry;
    public $states = [];


    public $countries = [
        'USA' => [
            'Alabama',
            'Alaska',
            'Arizona',
            'Arkansas',
            'California',
            'Colorado',
            'Connecticut',
            'Delaware',
            'Florida',
            'Georgia',
            'Hawaii',
            'Idaho',
            'Illinois',
            'Indiana',
            'Iowa',
            'Kansas',
            'Kentucky',
            'Louisiana',
            'Maine',
            'Maryland',
            'Massachusetts',
            'Michigan',
            'Minnesota',
            'Mississippi',
            'Missouri',
            'Montana',
            'Nebraska',
            'Nevada',
            'New Hampshire',
            'New Jersey',
            'New Mexico',
            'New York',
            'North Carolina',
            'North Dakota',
            'Ohio',
            'Oklahoma',
            'Oregon',
            'Pennsylvania',
            'Rhode Island',
            'South Carolina',
            'South Dakota',
            'Tennessee',
            'Texas',
            'Utah',
            'Vermont',
            'Virginia',
            'Washington',
            'West Virginia',
            'Wisconsin',
            'Wyoming'
        ],
        'England' => [
            'Bedfordshire',
            'Berkshire',
            'Bristol',
            'Buckinghamshire',
            'Cambridgeshire',
            'Cheshire',
            'Cornwall',
            'Cumbria',
            'Derbyshire',
            'Devon',
            'Dorset',
            'Durham',
            'Essex',
            'Gloucestershire',
            'Greater London',
            'Greater Manchester',
            'Hampshire',
            'Herefordshire',
            'Hertfordshire',
            'Kent',
            'Lancashire',
            'Leicestershire',
            'Lincolnshire',
            'Merseyside',
            'Norfolk',
            'North Yorkshire',
            'Northamptonshire',
            'Northumberland',
            'Nottinghamshire',
            'Oxford',
            'Rutland',
            'Shropshire',
            'Somerset',
            'South Yorkshire',
            'Staffordshire',
            'Suffolk',
            'Surrey',
            'Tyne and Wear',
            'Warwickshire',
            'West Midlands',
            'West Sussex',
            'West Yorkshire',
            'Wiltshire'
        ],
        'Canada' => [
            'Alberta',
            'British Columbia',
            'Manitoba',
            'New Brunswick',
            'Newfoundland and Labrador',
            'Nova Scotia',
            'Ontario',
            'Prince Edward Island',
            'Quebec',
            'Saskatchewan'
        ],
        'India' => [
            'Andhra Pradesh',
            'Arunachal Pradesh',
            'Assam',
            'Bihar',
            'Chhattisgarh',
            'Goa',
            'Gujarat',
            'Haryana',
            'Himachal Pradesh',
            'Jharkhand',
            'Karnataka',
            'Kerala',
            'Madhya Pradesh',
            'Maharashtra',
            'Manipur',
            'Meghalaya',
            'Mizoram',
            'Nagaland',
            'Odisha',
            'Punjab',
            'Rajasthan',
            'Sikkim',
            'Tamil Nadu',
            'Telangana',
            'Tripura',
            'Uttar Pradesh',
            'Uttarakhand',
            'West Bengal'
        ]
    ];

    public function updatedSelectedCountry($value)
    {
        $this->states = $this->countries[$value] ?? [];
        $this->supplier_state = '';
    }
    //     public function addSupplier()
// {
//     $this->resetForm(); // Reset fields
//     $this->is_active = true; // Default checked only for new supplier
//     $this->editing = false; // Set editing flag to false
// }

    // Method to open add supplier modal with reset
    public function openAddSupplierModal()
    {
        $this->resetForm();
        $this->is_active = true; // Default checked for new supplier
        $this->editing = false; // Set editing flag to false
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('open-modal', 'add-supplier-modal');
    }


    public function createSupplier()
    {
        // Validate the input fields
        $this->validate([
            'supplier_name' => 'required|unique:suppliers,supplier_name',
            'supplier_email' => 'required|email|unique:suppliers,supplier_email',
            'supplier_slug' => 'required|unique:suppliers,supplier_slug',
            'supplier_phone' => 'nullable|string',
            'supplier_vat' => 'nullable|string',
            'supplier_address' => 'required|string',
            'supplier_city' => 'required|string',
            'supplier_state' => 'required|string',
            'selectedCountry' => 'required|string',
            'supplier_zip' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'int_type' => 'required|in:EDI,EMAIL,NONE',
        ]);

        try {
            Supplier::create([
                'supplier_name' => $this->supplier_name,
                'supplier_email' => $this->supplier_email,
                'supplier_slug' => $this->supplier_slug,
                'supplier_phone' => $this->supplier_phone,
                'supplier_vat' => $this->supplier_vat,
                'supplier_address' => $this->supplier_address,
                'supplier_city' => $this->supplier_city,
                'supplier_state' => $this->supplier_state,
                'supplier_country' => $this->selectedCountry,
                'supplier_zip' => $this->supplier_zip,
                'created_by' => auth()->user()->name,
                'updated_by' => auth()->user()->name,
                'is_active' => (bool) $this->is_active,
                'int_type' => $this->int_type,
                
            ]);

            //  Reset form after successful creation
            $this->resetForm();
            $this->resetErrorBag();
            $this->resetValidation();

            // Close modal
            $this->dispatch('pg:eventRefresh-suppliers-list-futvly-table');
            $this->dispatch('close-modal', 'add-supplier-modal');



            // Show success message
            session()->flash('message', 'Supplier created successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating supplier: ' . $e->getMessage());
        }
    }

#[On('edit-supplier')]
public function startEdit($rowId)
{
    $this->resetForm(); // Reset all fields
    $this->editing = true; // Indicate edit mode

    $this->supplier_id = $rowId; // Set the supplier ID

    $supplier = Supplier::findOrFail($rowId); // Retrieve the supplier details

    // Populate the fields with the supplier's data
    $this->supplier_name = $supplier->supplier_name;
    $this->supplier_email = $supplier->supplier_email;
    $this->supplier_slug = $supplier->supplier_slug;
    $this->supplier_phone = $supplier->supplier_phone;
    $this->supplier_address = $supplier->supplier_address;
    $this->supplier_city = $supplier->supplier_city;
    $this->supplier_country = $supplier->supplier_country;
    $this->supplier_zip = $supplier->supplier_zip;
    $this->supplier_vat = $supplier->supplier_vat;

    $this->is_active = (bool) $supplier->is_active;
    $this->int_type = $supplier->int_type ?? 'NONE';

    // Set the country and update the states dropdown
    $this->selectedCountry = $supplier->supplier_country;
    $this->states = $this->countries[$supplier->supplier_country] ?? [];
    $this->supplier_state = $supplier->supplier_state;

    // Open the modal
    $this->dispatch('open-modal', 'edit-supplier-modal');

        Log::info("Editing Supplier: " . $supplier->supplier_name);
}


    public function updateSupplier()
    {
        $this->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_email' => 'required|email|max:255',
            'supplier_slug' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:15',
            'supplier_vat' => 'nullable|string|max:50',
            'supplier_address' => 'required|string|max:500',
            'supplier_city' => 'required|string|max:255',
            'supplier_state' => 'required|string|max:255',
            'selectedCountry' => 'required|string|max:255',
            'supplier_zip' => 'nullable|string|max:10',
            'is_active' => 'required|boolean',
            'int_type' => 'required|in:EDI,EMAIL,NONE',
        ]);

        // Check for unique constraints on 'supplier_name', 'supplier_email', and 'supplier_slug'
        if (
            Supplier::where('supplier_name', $this->supplier_name)
                ->where('id', '!=', $this->supplier_id)
                ->exists()
        ) {
            $this->addError('supplier_name', 'The supplier name must be unique.');
            return;
        }

        if (
            Supplier::where('supplier_email', $this->supplier_email)
                ->where('id', '!=', $this->supplier_id)
                ->exists()
        ) {
            $this->addError('supplier_email', 'The supplier email must be unique.');
            return;
        }

        if (
            Supplier::where('supplier_slug', $this->supplier_slug)
                ->where('id', '!=', $this->supplier_id)
                ->exists()
        ) {
            $this->addError('supplier_slug', 'The supplier slug must be unique.');
            return;
        }



        try {
            // Find and update the supplier
            $supplier = Supplier::findOrFail($this->supplier_id);
            $supplier->update([
                'supplier_name' => $this->supplier_name,
                'supplier_email' => $this->supplier_email,
                'supplier_slug' => $this->supplier_slug,
                'supplier_phone' => $this->supplier_phone,
                'supplier_vat' => $this->supplier_vat,
                'supplier_address' => $this->supplier_address,
                'supplier_city' => $this->supplier_city,
                'supplier_state' => $this->supplier_state,
                'supplier_country' => $this->selectedCountry,
                'supplier_zip' => $this->supplier_zip,
                'is_active' => $this->is_active,
                'int_type' => $this->int_type,
                'updated_by' => auth()->user()->name,
            ]);

            // Reset state and dispatch events
            $this->resetForm();
            $this->resetErrorBag();
            $this->resetValidation();
            $this->dispatch('close-modal', 'edit-supplier-modal');
            session()->flash('success', 'Supplier updated successfully!');
            $this->dispatch('pg:eventRefresh-suppliers-list-futvly-table');
        } catch (\Throwable $e) {
            // Log the error and show a flash message
            Log::error($e->getMessage() . ' at line ' . $e->getLine());
            session()->flash('error', 'Something went wrong while updating the supplier.');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'supplier_name',
            'supplier_email',
            'supplier_slug',
            'supplier_phone',
            'supplier_vat',
            'supplier_address',
            'supplier_city',
            'supplier_state',
            'supplier_zip',
            'selectedCountry',
            'is_active',
            'int_type',
            'supplier_id',
            'editing',
        ]);

        // Reset states array separately
        $this->states = [];
    }



    public function render()
    {
        return view('livewire.admin.suppliers.supplier-component');
    }
}
