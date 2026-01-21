<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use App\Services\Stripe\OrganizationStripeService;
use App\Services\Stripe\OrganizationSubscriptionStripeService;
use Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewOrganizationMail;
use App\Services\StaffRoleCreation;



class OrganizationDetailComponent extends Component
{

    use WithFileUploads;
    public $currentStep = 1;
    public $totalSteps = 2;

    public $id = '';
    public $name = '';
    public $plan_id = '1';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $state = '';
    public $city = '';
    public $country = '';
    public $pin = '';
    public $selectedCountry;
    public $states = [];

    public $logo;

    public $plans = [];

    public $value = '0';


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

    public function nextStep()
    {
        if ($this->currentStep == 1) {
            $this->validate([
                'name' => 'required|unique:organizations,name|min:3|max:25',
                'email' => 'required|email',
                'phone' => 'nullable|string|max:15',
            ]);
        } elseif ($this->currentStep == 2) {
            $this->validate([
                'selectedCountry' => 'required',
                'state' => 'required',
                'address' => 'required|string|max:255',
                'pin' => 'required'
            ]);
        }

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function mount($id = null)
    {
        if ($id) {
            $org = Organization::find($id);
            if ($org) {
                $this->id = $org->id;
                $this->name = $org->name;
                $this->email = $org->email;
                $this->plan_id = $org->plan_id;
                $this->selectedCountry = $org->country;
                $this->state = $org->state;
                $this->city = $org->city;
                $this->pin = $org->pin;
                $this->states = $this->countries[$this->selectedCountry] ?? [];
                $this->plan_valid = $org->plan_valid;
            }
        }
        $this->email = auth()->user()->email;
        $this->plans = Plan::where('is_active', true)->get();
    }

    public function updatedSelectedCountry($country)
    {
        $this->states = $this->countries[$country] ?? [];
    }
    public function createOrganization()
    {

        $this->validate([
            'name' => 'required|unique:organizations,name|min:3|max:25',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:15',
            'plan_id' => 'required',
            'selectedCountry' => 'required',
            'state' => 'required',
            'address' => 'required|string|max:255',
            'pin' => 'required',
            'logo' => 'nullable|image|max:2048',
        ]);
        $this->country = $this->selectedCountry;
        $uploadedImage = null;
        if (!empty($this->logo)) {
            $uploadedImage = $this->logo->store('organization_logos', 'public');
        }

        $subs = Plan::where('id', $this->plan_id)->first();
        $plan_valid_until = now()->addMonths((int) $subs->duration);
        // Create the organizations
        // $code = Organization::fetchCode();
        $org = Organization::create([
            'name' => $this->name,
            // 'organization_code' => $code+1,
            'email' => $this->email,
            'phone' => $this->phone,
            'plan_id' => null,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'address' => $this->address,
            'pin' => $this->pin,
            'plan_valid' => $plan_valid_until,
            'image' => $uploadedImage,
            'is_rep_org' => auth()->user()->is_medical_rep ? true : false,
        ]);
        $org->organization_code = 10000 + $org->id;
        $org->save();

        $plan = Plan::findOrFail($this->plan_id);

        app(OrganizationStripeService::class)->syncOrganization($org);

        // Create Staff role and assign permissions
        app(StaffRoleCreation::class)->create($org->id);
        $user = auth()->user();
        $user->organization_id = $org->id;
        $user->save();
        if (auth()->user()->is_medical_rep) {
            return redirect()->route('medical_rep.dashboard');
        }
        $orgName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $org->name));

        // User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@' . $orgName . '.com', // unique per org
        //     'password' => Hash::make('Admin123!'), // default password
        //     'avatar' => 'avatar (8).png',
        //     'role_id' => '2',
        //     'organization_id' => $org->id,
        //     'system_locked' => true,
        //     'is_medical_rep' => auth()->user()->is_medical_rep,
        // ]);

        
        // User creation has been centralized in CreateUserService.
        $email = 'admin@' . $orgName . '.com';

        app(\App\Services\CreateUserService::class)->create([
            'name' => 'Admin',
            'email' => 'admin@' . $orgName . '.com',
            'password' => 'Admin123!',
            'avatar' => 'avatar (8).png',
            'role_id' => 2,
            'organization_id' => $org->id,
            'system_locked' => true,
            'is_medical_rep' => auth()->user()->is_medical_rep,
            'created_by' => auth()->user()->id,
        ], [
            'audit' => true,       // optional: logs creation
            'notify' => new NewUserNotification($email, 'Admin123!') // optional: email
        ]);

        try {
            Mail::to(config('app.email'))->send(new NewOrganizationMail($user, $org));
        } catch (\Exception $e) {
            \Log::error('Failed to queue NewOrganizationMail: ' . $e->getMessage(), [
                'org_id' => $org->id,
                'user_id' => $user->id,
            ]);
        }

        return redirect()->route('pricing');
    }
    public function selectPlan($id)
    {
        $this->plan_id = $id;
    }
    public function render()
    {

        return view('livewire.organization.organization-detail-component');
    }
}
