<?php

namespace App\Livewire\Admin\AdminOrganization;

use App\Models\Organization;
use App\Models\Location;
use App\Models\Plan;
use App\Services\Stripe\OrganizationStripeService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Services\CreatePracticeService;


class AdminOrganization extends Component
{
    use WithFileUploads;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $plan_id = '';
    public $address = '';
    public $organization_id = '';
    public $isRepOrg = 0;

    public $is_active = '';
    public $plans = [];

    public $plan_name = '';

    public $show_org_details = false;
    public $organization = null;

    public $adminUser = null;
    public $state = '';
    public $city = '';
    public $country = '';
    public $selectedCountry;
    public $pin = '';
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
    public $states = [];
    public $logo;

    public function closeOrgDetails()
    {
        $this->show_org_details = false;
    }


    public function mount($id = null, $isRepOrg = 0)
    {
        $this->isRepOrg = $isRepOrg;
        if ($id) {
            $this->organization_id = $id;
            $this->loadOrganizationData();
        }
        $this->plans = Plan::all();
        $this->selectedCountry = $this->organization->country ?? '';
        $this->states = $this->countries[$this->selectedCountry] ?? [];
    }

    public function loadOrganizationData()
{
    $organization = Organization::find($this->organization_id);
    $this->organization = $organization;
    if ($organization) {
        $this->name = $organization->name;
        $this->email = $organization->email;
        $this->phone = $organization->phone;
        $this->location_id = $organization->location_id;
        $this->plan_name = $organization->plan?->name;
        $this->address = $organization->address;
        $this->is_active = (bool) $organization->is_active;
        $this->plan_id = $organization->plan_id;
        $this->adminUser = $organization->users()->where('system_locked', true)->first();
        $this->logo = null;
        $this->selectedCountry = $organization->country;
        $this->states = $this->countries[$this->selectedCountry] ?? [];
        $this->state = $organization->state;
        $this->city = $organization->city;
        $this->pin = $organization->pin;
    }
}

public function openCreatePracticeModal()
{
    $this->resetForm(); // Clear all properties
    $this->isRepOrg = 0; // Default unchecked
    $this->is_active = 1; // Optional, if you want new practice active by default
    $this->resetErrorBag();
    $this->resetValidation();

    $this->dispatch('open-modal', 'create-practice-modal');
}
public function resetForm()
{
    $this->name = '';
    $this->email = '';
    $this->phone = '';
    $this->address = '';
    $this->selectedCountry = '';
    $this->state = '';
    $this->city = '';
    $this->pin = '';
    $this->logo = null;
    $this->isRepOrg = 0;
    $this->states = [];
}


public function createPractice(CreatePracticeService $service)
{
    $this->validate([
        'name' => 'required|unique:organizations,name',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:15',
        'selectedCountry' => 'required',
        'state' => 'required',
        'city' => 'required',
        'address' => 'required',
        'pin' => 'required',
        'logo' => 'nullable|image|max:2048',
        'isRepOrg' => 'boolean',
        'is_active' => 'boolean',

    ]);

    $logoPath = $this->logo
        ? $this->logo->store('organization_logos', 'public')
        : null;

    $service->handle([
        'name' => $this->name,
        'email' => $this->email,
        'phone' => $this->phone,
        'country' => $this->selectedCountry,
        'state' => $this->state,
        'city' => $this->city,
        'address' => $this->address,
        'pin' => $this->pin,
        'logo' => $logoPath,
        'is_rep_org' => $this->isRepOrg,
    ]);

    $this->dispatch('pg:eventRefresh-organizations-list-kvvozg-table');
    $this->dispatch('close-modal', 'create-practice-modal');

    $this->reset();
}


    #[On('edit-organization')]
public function startEdit($rowId)
{
    $this->organization_id = $rowId;
    $this->loadOrganizationData();

        // Open the modal
    $this->dispatch('open-modal', 'edit-organization-modal');
}

    public function updateOrganization()
    {
        $organization = Organization::findOrFail($this->organization_id);

        // Validate the required fields
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'phone' => 'nullable|string|max:15',
            'address' => 'required|string|max:255',
            'selectedCountry' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'pin' => 'required|string|max:10',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Check if the organization name is unique, excluding the current organization
        if (
            Organization::where('name', $this->name)
                ->where('is_active', true)
                ->where('id', '!=', $this->organization_id)
                ->exists()
        ) {
            $this->addError('name', 'The organization name must be unique.');
            return;
        }

        if (
            Organization::where('email', $this->email)
                ->where('is_active', true)
                ->where('id', '!=', $this->organization_id)
                ->exists()
        ) {
            $this->addError('email', 'The email is already taken.');
            return;
        }

        try {
            // Store logo
            $uploadedLogoPath = $organization->image ?? null;
            if ($this->logo) {
                $uploadedLogoPath = $this->logo->store('organization_logos', 'public');
            }

            $organization->update([
                'name' => $this->name,
                'email' => $this->email,
                'plan_id' => $this->plan_id,
                'phone' => $this->phone,
                'address' => $this->address,
                'country' => $this->selectedCountry,
                'state' => $this->state,
                'city' => $this->city,
                'pin' => $this->pin,
                'is_active' => (bool) $this->is_active,
                'image' => $uploadedLogoPath,
            ]);

        app(OrganizationStripeService::class)->syncOrganization($organization);


            session()->flash('success', 'Organization details saved successfully!');

            $this->dispatch('pg:eventRefresh-organization-list-eyipxp-table');
            $this->dispatch('close-modal', 'edit-organization-modal');
            // $this->resetExcept('countries', 'plans');

            // $this->reset();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving organization: ' . $e->getMessage());
        }
    }

    #[On('showOrgDetails')]

    public function showOrgDetails($orgId)
    {
        $this->organization_id = $orgId;
        $this->loadOrganizationData();
        $this->show_org_details = true;
    }

    public function deleteOrganization()
    {
        $organization = Organization::find($this->organization_id);

        if (!$organization) {
            session()->flash('error', 'Organization not found!');
            return;
        }

        $user = auth()->user(); // Get the authenticated user

        if ($user->role_id == 1) { // Super Admin
            $organization->update(['is_active' => false, 'is_deleted' => true]);
        } elseif ($user->role_id == 2) { // Other Admins
            $organization->update(['is_active' => false]);
        }

        $this->reset();
        $this->dispatch('close-modal', 'edit-organization-modal');
        session()->flash('success', 'Organization deleted successfully!');
        $this->dispatch('pg:eventRefresh-organization-list-eyipxp-table');
    }
    public function updatedSelectedCountry($country)
    {
        $this->states = $this->countries[$country] ?? [];
        $this->state = '';
    }

    public function render()
    {
        $locations = Location::where('is_active', true)->where('org_id', auth()->user()->organization_id)->get();

        $plans = Plan::where('is_active', true)->get();

        $org_data = Organization::where('is_deleted', false)
            ->where('is_rep_org', $this->isRepOrg)
            ->with('plan')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.admin-organization.admin-organization', compact('locations', 'plans', 'org_data'));
    }
}
