<?php

namespace App\Livewire\Organization\Customers;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class CustomerComponent extends Component
{
    public $customers;
    public $customer_id;
    public $customer_name;
    public $customer_email;
    public $customer_phone;
    public $customer_address;
    public $customer_city;
    public $customer_state;
    public $customer_pin_code;
    public $customer_country;
    public $customer_is_active = true;

    public $showAddModal = false;
    public $showEditModal = false;
    public $isEditing = false;

    public $selectedCountry = null;
    public $states = [];

    public $countries = [
        'USA' => [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
            'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
            'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
            'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
            'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
            'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
            'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon',
            'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
            'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
            'West Virginia', 'Wisconsin', 'Wyoming'
        ],
        'United Kingdom' => [
            'England', 'Scotland', 'Wales', 'Northern Ireland'
        ],
        'Canada' => [
            'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick',
            'Newfoundland and Labrador', 'Nova Scotia', 'Ontario',
            'Prince Edward Island', 'Quebec', 'Saskatchewan',
            'Northwest Territories', 'Nunavut', 'Yukon'
        ],
        'India' => [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
            'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
            'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan',
            'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
            'Uttarakhand', 'West Bengal', 'Delhi', 'Jammu and Kashmir', 'Ladakh'
        ],
        'Australia' => [
            'Australian Capital Territory', 'New South Wales', 'Northern Territory',
            'Queensland', 'South Australia', 'Tasmania', 'Victoria', 'Western Australia'
        ]
    ];

    protected function rules()
    {
        return [
            'customer_name' => 'required|string|max:255',

            // Either email OR phone must be present
            'customer_email' => [
                'nullable',
                'email',
                'max:255',
                'required_without:customer_phone',
                $this->customer_id
                    ? Rule::unique('customers', 'customer_email')->ignore($this->customer_id)
                    : Rule::unique('customers', 'customer_email'),
            ],

            'customer_phone' => [
                'nullable',
                'string',
                'max:20',
                'required_without:customer_email',
                $this->customer_id
                    ? Rule::unique('customers', 'customer_phone')->ignore($this->customer_id)
                    : Rule::unique('customers', 'customer_phone'),
            ],

            'customer_address' => 'nullable|string|max:500',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_pin_code' => 'nullable|string|max:20',
            'selectedCountry' => 'nullable|string|max:100',
            'customer_is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'customer_name.required' => 'Customer name is required.',

        'customer_email.required_without' => 'Email or phone number is required.',
        'customer_phone.required_without' => 'Phone number or email is required.',

        'customer_email.email' => 'Please enter a valid email address.',
        'customer_email.unique' => 'This email address is already taken.',
        'customer_phone.max' => 'Phone number may not be greater than 20 characters.',
    ];

    public function updatedSelectedCountry($country)
    {
        $this->states = $this->countries[$country] ?? [];
        $this->customer_state = null;
    }

    public function mount()
    {
        $this->loadCustomers();
    }

    public function loadCustomers()
    {
        $this->customers = Customer::latest()->get();
    }

    #[On('open-customer-modal')]
    public function openCustomerModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-modal', 'customer-modal');
    }

    #[On('openEditModal')]
    public function openEditModal($customerId)
    {
        $this->resetForm();
        $customer = Customer::findOrFail($customerId);

        $this->isEditing = true;
        $this->customer_id = $customer->id;
        $this->customer_name = $customer->customer_name;
        $this->customer_email = $customer->customer_email;
        $this->customer_phone = $customer->customer_phone;
        $this->customer_address = $customer->customer_address;
        $this->customer_city = $customer->customer_city;
        $this->customer_state = $customer->customer_state;
        $this->customer_pin_code = $customer->customer_pin_code;
        $this->customer_country = $customer->customer_country;
        $this->customer_is_active = (bool) $customer->customer_is_active;

        $this->selectedCountry = $customer->customer_country;
        $this->updatedSelectedCountry($customer->customer_country);

        $this->dispatch('open-modal', 'customer-modal');
    }

    public function resetForm()
    {
        $this->customer_id = null;
        $this->customer_name = '';
        $this->customer_email = '';
        $this->customer_phone = '';
        $this->customer_address = '';
        $this->customer_city = '';
        $this->customer_state = '';
        $this->customer_pin_code = '';
        $this->customer_country = '';
        $this->selectedCountry = null;
        $this->states = [];
        $this->customer_is_active = true;
        $this->resetErrorBag();
    }

    public function closeModals()
    {
        $this->dispatch('close-modal', 'customer-modal');
        $this->isEditing = false;
        $this->resetForm();
        $this->dispatch('pg:eventRefresh-customer-list-l0gx9s-table');
    }

    public function save()
    {
        $this->validate();

        Customer::create([
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_city' => $this->customer_city,
            'customer_state' => $this->customer_state,
            'customer_pin_code' => $this->customer_pin_code,
            'customer_country' => $this->selectedCountry,
            'customer_is_active' => $this->customer_is_active,
        ]);

        $this->loadCustomers();
        $this->closeModals();
        session()->flash('success', 'Customer added successfully!');
    }

    public function update()
    {
        $this->validate();

        $customer = Customer::findOrFail($this->customer_id);
        $customer->update([
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_city' => $this->customer_city,
            'customer_state' => $this->customer_state,
            'customer_pin_code' => $this->customer_pin_code,
            'customer_country' => $this->selectedCountry,
            'customer_is_active' => $this->customer_is_active,
        ]);

        $this->loadCustomers();
        $this->closeModals();
        session()->flash('success', 'Customer updated successfully!');
    }

    public function delete($customerId)
    {
        Customer::findOrFail($customerId)->delete();
        $this->loadCustomers();
        session()->flash('success', 'Customer deleted successfully!');
    }

    public function toggleCustomerStatus($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $customer->update([
            'customer_is_active' => !$customer->customer_is_active
        ]);

        $this->loadCustomers();
        session()->flash('success', 'Customer status updated!');
    }

    public function render()
    {
        return view('livewire.organization.customers.customer-component');
    }
}
