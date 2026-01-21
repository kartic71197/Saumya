<?php

namespace App\Livewire\Organization\Patients;

use App\Exports\PatientsExport;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\PatientDetails;
use App\Models\Product;
use App\Models\StockCount;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PatientsComponent extends Component
{
    public $notifications = [];
    public $selectedPatient = null;
    public $editMode = false;

    // Form fields
    public $patientId = null, $chartnumber = null, $address = null, $city = null, $state = null, $pin_code = null, $organization_id = null, $ins_type = null, $provider = null, $icd = null, $account_number = null, $drug = null, $dose = null, $frequency = null, $location = null, $pa_expires = null, $date_given = null;
    public $paid = 0;

    public $details;
    public $pt_copay = 0;
    public $our_cost = 0;
    public $price = 0;
    public $profit = 0;
    public $organization;
    public $custom_frequency = '';

    public $locationList = [];
    // Countries and states data
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
    public $selectedCountry = null;
    public Patient $patient;
    public string $formattedAddress;
    public $isLoading = false;
    public $isSaving = false;
    public $product_search = '';
    public $products = [];
    public $selected_product_name = '';
    public $show_dropdown = false;
    public $filtered_products = [];
    public $product_id = null;

    public $viewMode = false;
    public $saleId = null;
    public $prepareSale = false;
    public $sale;
    public $shipment;
    public $showShippingModal = false;
    public $showTrackingModal = false;
    public $trackingInfo = [];
    public $errorMessage = '';
    public $prescriptions = [];
    public $totalInsPaid = 0;
    public $totalCopay = 0;
    public $totalOurCost = 0;
    public $totalProfit = 0;
    public $profitPercent = 0;

    public $batch_number = null;
    public $expiry_date = null;

    public $quantity = 1;
    public $unit_cost = 1;
    public $initials;


    public $locations = [];
    public $states = [];
    public function updated($field)
    {
        if (in_array($field, ['paid', 'our_cost', 'pt_copay'])) {
            $this->calculateProfit();
        }
    }

    public function calculateProfit()
    {
        $paid = is_numeric($this->paid) ? (float) $this->paid : 0;
        $pt_copay = is_numeric($this->pt_copay) ? (float) $this->pt_copay : 0;
        $our_cost = is_numeric($this->our_cost) ? (float) $this->our_cost : 0;

        $this->profit = round($paid + $pt_copay - $our_cost, 2);
    }
    public function mount()
    {
        $this->organization = auth()->user()->organization;
        $this->calculateProfit();
        $this->locationList = Location::where('org_id', $this->organization->id)
            ->where('is_active', true)
            ->get();
    }

    public function updatedSelectedCountry($country)
    {
        $this->states = $this->countries[$country] ?? [];
        // Reset state when country changes
        $this->state = null;
    }

    public function createPatient()
    {
        $this->validate([
            'chartnumber' => 'required',
            'selectedCountry' => 'required',
            'initials' => 'required'
        ]);

        // Create the patient
        Patient::create([
            'chartnumber' => $this->chartnumber,
            'initials' => $this->initials,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->selectedCountry,
            'pin_code' => $this->pin_code,
            'organization_id' => auth()->user()->organization_id,
            'ins_type' => $this->ins_type,
            'provider' => $this->provider,
            'icd' => $this->icd,
            'location' => $this->location,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'patient-modal');
        // header("Location: " . $_SERVER['REQUEST_URI']);
        // exit;
        redirect()->route('patient.index')->with('success', 'Patient created successfully!');

    }

    public function updatePatient()
    {
        $this->validate([
            'chartnumber' => 'required',
            'selectedCountry' => 'required',
            'state' => 'required',
            'pin_code' => 'required',
            'initials' => 'required'
        ]);

        $patient = Patient::find($this->patientId);
        logger('Updating patient with ID: ' . $this->patientId);

        if (!$patient) {
            $this->dispatch('add-notification', 'Patient not found!', 'error');
            return;
        }

        $patient->update([
            'chartnumber' => $this->chartnumber,
            'initials' => $this->initials,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->selectedCountry,
            'pin_code' => $this->pin_code,
            'ins_type' => $this->ins_type,
            'provider' => $this->provider,
            'icd' => $this->icd,
            'location' => $this->location,
        ]);

        $this->dispatch('show-notification', 'Patient updated successfully!', 'success');
        $this->dispatch('close-modal', 'patient-modal');
        $this->dispatch('close-modal', 'patient-details-modal');
        //$this->dispatch('pg:eventRefresh-patients-list-gx9aih-table');
        $this->reset();

        // header("Location: " . $_SERVER['REQUEST_URI']);
        // exit;
        redirect()->route('patient.index')->with('success', 'Patient updated successfully!');
    }

    // public function exportPatients()
    // {
    //     logger('Exporting patients data');
    //     $data = $this->getFormattedPatients();

    //     return Excel::download(new PatientsExport($data), 'patients_export.xlsx');
    // }

    public function exportPatients($filteredData = null)
{
    if ($filteredData) {
        \Log::info('Filtered Data Received:', $filteredData); 
        $data = collect($filteredData);
    } else {
        // Fallback: export all data from DB
        $data = $this->getFormattedPatients();
    }


    return Excel::download(new PatientsExport($data), 'patients_export.xlsx');
}

    public function getFormattedPatients()
    {
        $user = auth()->user();
        $patients = Patient::with(['details.product','Patientlocation'])
            ->where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->get();

        $formatted = collect();

        foreach ($patients as $patient) {
            logger($patient->Patientlocation);
            foreach ($patient->details as $detail) {
                $formatted->push([
                    'chartnumber' => $patient->chartnumber,
                    'initials' => $patient->initials ?? '-',
                    'ins_type' => $patient->ins_type ?? '-',
                    'provider' => $patient->provider ?? '-',
                    'icd' => $patient->icd ?? '-',
                    'address' => $patient->address ?? '-',
                    'city' => $patient->city ?? '-',
                    'state' => $patient->state ?? '-',
                    'country' => $patient->country ?? '-',
                    'pin_code' => $patient->pin_code ?? '-',
                    'location' => $patient->Patientlocation?->name ?? '-',
                    'date_given' => $detail->date_given,
                    'product_name' => $detail?->product?->product_name ?? '-',
                    'quantity' => $detail->quantity ?? '-',
                    'dose' => $detail->dose ?? '-',
                    'frequency' => $detail->frequency ?? '-',
                    'paid' => $detail->paid,
                    'our_cost' => $detail->our_cost,
                    'pt_copay' => $detail->pt_copay,
                    'profit' => $detail->profit,
                    'batch_number' => $detail->batch_number,
                    'expiry_date' => $detail->expiry_date,
                    // 'unit' => $detail->unit,
                ]);
            }

            if ($patient->details->isEmpty()) {
                $formatted->push([
                    'chartnumber' => $patient->chartnumber ?? '-',
                    'initials' => $patient->initials ?? '-',
                    'ins_type' => $patient->ins_type ?? '-',
                    'provider' => $patient->provider ?? '-',
                    'icd' => $patient->icd ?? '-',
                    'address' => $patient->address ?? '-',
                    'city' => $patient->city ?? '-',
                    'state' => $patient->state ?? '-',
                    'country' => $patient->country ?? '-',
                    'pin_code' => $patient->pin_code ?? '-',
                    'location' => $patient->Patientlocation?->name ?? '-',
                    'date_given' => '-',
                    'product_name' => '-',
                    'quantity' => '-',
                    'dose' => '-',
                    'frequency' => '-',
                    'paid' => '-',
                    'our_cost' => '-',
                    'pt_copay' => '-',
                    'profit' => '-',
                    'batch_number' => '-',
                    'expiry_date' => '-',
                    // 'unit' => null,
                ]);
            }
        }
        return $formatted;
    }

    public function editPatient($patientId = null)
    {
        $this->editMode = true;

        $this->patientId = $this->patient->id;
        logger('Editing patient with ID: ' . $patientId);

        if (!$this->patientId) {
            $this->dispatch('show-notification', 'Patient not found!', 'error');
            return;
        }

        // Fill form with patient data
        $this->chartnumber = $this->patient->chartnumber;
        $this->selectedCountry = $this->patient->country;
        // Load states for the selected country
        $this->updatedSelectedCountry($this->patient->country);
        $this->state = $this->patient->state;
        $this->address = $this->patient->address;
        $this->city = $this->patient->city;
        $this->pin_code = $this->patient->pin_code;
        $this->ins_type = $this->patient->ins_type;
        $this->provider = $this->patient->provider;
        $this->icd = $this->patient->icd;
        $this->location = $this->patient->location;
        $this->initials = $this->patient->initials;
        // Open the modal
        $this->dispatch('close-modal', 'patient-details-modal');
        $this->dispatch('open-modal', 'patient-modal');
    }
    #[On('deletePatient')]
    public function deletePatient($patientId)
    {
        $this->patientId = $patientId;
        $patient = Patient::find($patientId);
        $this->chartnumber = $patient->chartnumber;
        if (!$patient) {
            $this->dispatch('show-notification', 'Patient not found!', 'error');
            return;
        }
        $this->dispatch('open-modal', 'delete-patient-modal');
    }
    public function confirmdeletePatient()
    {
        $patient = Patient::find($this->patientId);

        if (!$patient) {
            $this->dispatch('show-notification', 'Patient not found!', 'error');
            return;
        }
        $patient->is_active = false;
        $patient->save();

        $this->dispatch('show-notification', 'Patient data deleted successfully!', 'success');
        $this->dispatch('close-modal', 'delete-patient-modal');
        $this->dispatch('pg:eventRefresh-patients-list-gx9aih-table');
    }

    public function addPatient()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->dispatch('open-modal', 'patient-modal');
    }

    public function resetForm()
    {
        $this->reset([
            'patientId',
            'chartnumber',
            'address',
            'city',
            'state',
            'selectedCountry',
            'pin_code',
            'states',
            'editMode',
            'ins_type',
            'provider',
            'icd',
            'account_number',
            'drug',
            'dose',
            'frequency',
            'location',
            'pa_expires'
        ]);
    }


    public function downloadSampleCsv()
    {
        $headers = [
            'chartnumber',
            'initials',
            'location',
            'city',
            'state',
            'address',
            'country',
            'pin_code',
            'ins_type',
            'provider',
            'icd',
        ];

        $sampleData = [
            [
                'chartnumber' => '123456',
                'initials' => 'AB',
                'location' => 'Clinic A',
                'city' => 'New York',
                'state' => 'NY',
                'address' => '123 Main St',
                'country' => 'USA',
                'pin_code' => '10001',
                'ins_type' => 'Insurance Type',
                'provider' => 'Provider Name',
                'icd' => 'ICD Code',
            ],
        ];
        $csv = implode(',', $headers) . "\n";
        foreach ($sampleData as $row) {
            $csv .= implode(',', $row) . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'sample_patients_import.csv');
    }

    public function openPrescribeModal($id = null)
    {
           $this->products = [];
           $this->product_search = '';
            $this->product_id = null;
           $this->show_dropdown = false;
        $this->patient = Patient::find($id);
        $this->location = $this->patient->location;
        $this->date_given = Carbon::now()->format('Y-m-d');
        $this->loadProducts();
        $this->dispatch('open-modal', 'add-prescription-modal');
    }

    private function formatAddress(Patient $patient): string
    {
        $address = $patient->address ?? 'N/A';
        $parts = [];
        if (!empty($patient->state))
            $parts[] = $patient->state;
        if (!empty($patient->country))
            $parts[] = $patient->country;
        if (!empty($patient->pin_code))
            $parts[] = $patient->pin_code;
        $locationString = implode(', ', $parts);
        return !empty($locationString) ? "$address<br>$locationString" : $address;
    }

    public function openDetails($id)
    {
        $this->patient = Patient::where('id', $id)->first();
        $this->formattedAddress = $this->formatAddress($this->patient) ?? 'N/A';
        $this->organization = auth()->user()->organization;
        $this->calculateProfit();
        $this->fetchTable();
        $this->locations = Location::select('id', 'name')
            ->where('is_active', true)
            ->where('org_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get();

        logger('Opening details for patient: ' . $this->patient);
        logger('Formatted address: ' . $this->formattedAddress);

        $this->dispatch('open-modal', 'patient-details-modal');
    }
    public function fetchTable()
    {
        $this->prescriptions = PatientDetails::where('patient_id', $this->patient->id)
            ->with(['product'])
            ->orderBy('date_given', 'desc')
            ->get();
        $this->totalInsPaid = $this->prescriptions->sum('paid');
        $this->totalCopay = $this->prescriptions->sum('pt_copay');
        $this->totalOurCost = $this->prescriptions->sum('our_cost');
        $this->totalProfit = round($this->totalInsPaid + $this->totalCopay - $this->totalOurCost, 2);
        $this->profitPercent = $this->totalOurCost > 0 ? round(($this->totalProfit / $this->totalOurCost) * 100, 2) : 0;
    }
    public function hideDropdown()
    {
        $this->show_dropdown = false;
    }

    public function showDropdown()
    {
        if (!empty($this->product_search)) {
            $this->show_dropdown = true;
            $this->updatedProductSearch(); // Trigger filtering
        }
    }
    public function loadProducts()
    {
        $query = StockCount::join('products', function ($join) {
                $join->on('products.id', '=', 'stock_counts.product_id')
                    ->where('products.is_active', '=', 1)
                    ->where('products.organization_id', '=', auth()->user()->organization_id);
            })
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('categories.category_name', '=', 'biological')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->where('stock_counts.on_hand_quantity', '>', 0)
            ->join('product_units', function ($join) {
                $join->on('product_units.product_id', '=', 'products.id')
                    ->where('product_units.is_base_unit', '=', 1);
            })
            ->where('stock_counts.location_id', $this->location)
            ->join('units', 'units.id', '=', 'product_units.unit_id')
            ->select(
                'stock_counts.*',
                'products.product_code as product_code',
                'products.product_name as product_name',
                'products.is_sample as is_sample',
                'locations.name as location_name',
                'units.unit_name as base_unit_name',
            );

        // Filter by location if selected
        if ($this->location) {
            $query->where('stock_counts.location_id', $this->location);
        }
        if ($product_search = $this->product_search) {
            $query->where(function ($q) use ($product_search) {
                $q->where('products.product_name', 'like', '%' . $product_search . '%')
                    ->orWhere('products.product_code', 'like', '%' . $product_search . '%');
            });
        }

        $this->products = $query->get();
    }
    public function updatedProductSearch()
    {
        if (empty($this->product_search)) {
           $this->products = collect(); // Empty collection when no search
            $this->product_id = null;
            $this->show_dropdown = false;
        } else {
            $this->show_dropdown = true;
            $this->loadProducts();
        }
    }
    public function selectProduct($id)
    {
        $product = $this->products->firstWhere('id', $id);
        // logger($product);
        if ($product) {
            $this->product_id = $product->product->id;
            $this->product_search = $product->product->product_name;
            $this->show_dropdown = false;
            $this->batch_number = $product->batch_number ?? null;
            $this->expiry_date = $product->expiry_date ?? null;
            $this->dose = $product->product->dose ?? null;
            $this->our_cost = $product->product->price;
            $this->unit_cost = $product->product->price;;
        }
    }
    public function createPrescription()
    {
        // Validate the request
        try {
        $this->validate([
            'date_given' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'frequency' => 'required|string|max:255',
            'custom_frequency' => 'required_if:frequency,Custom|string|max:255',
            'location' => 'required|exists:locations,id',
            'paid' => 'nullable|numeric',
            'our_cost' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'pt_copay' => 'nullable|numeric',
            'quantity' => 'nullable|numeric|min:1',
        ]);
        // \Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }


        if (!$this->dose) {
            $this->dispatch('show-notification', __('Please add dose in master catalog!!.'), 'error');
            return;
        }

        if (!$this->product_id) {
            $this->dispatch('show-notification', __('Please select a product.'));
            return;
        }
        if (empty($this->date_given)) {
            $this->dispatch('show-notification', __('Date given is required.'));
            return;
        }
        if (empty($this->location)) {
            $this->dispatch('show-notification', __('Location is required.'));
            return;
        }

        $stockCount = StockCount::where('product_id', $this->product_id)
            ->where('location_id', $this->location)
            ->where('batch_number', $this->batch_number)
            ->where('expiry_date', $this->expiry_date)
            ->first();

        if ($stockCount && $stockCount->on_hand_quantity < $this->quantity) {
            $this->dispatch('show-notification', __('Available qty is less than selected qty.'));
            return;
        }

        // Determine the frequency to save
    $frequencyToSave = $this->frequency === 'Custom' ? $this->custom_frequency : $this->frequency;


        // Get the selected product from the stock counts collection
        $selectedStockItem = $this->products->where('product_id', $this->product_id)
            ->where('location_id', $this->location)
            ->where('batch_number', $this->batch_number)
            ->where('expiry_date', $this->expiry_date)
            ->first();

        // Get product details directly from database for batch/expiry info
        $productDetails = Product::find($this->product_id);

        $patient_details = PatientDetails::create([
            'patient_id' => $this->patient->id,
            // 'date_given' => Carbon::now(),
            'date_given' => $this->date_given,
            'product_id' => $this->product_id,
            'dose' => $this->dose,
            // 'frequency' => $this->frequency,
            'frequency' => $frequencyToSave,
            'paid' => $this->paid,
            'our_cost' => $this->our_cost,
            'price' => $this->price,
            'pt_copay' => $this->pt_copay,
            'profit' => $this->profit,
            'batch_number' => $selectedStockItem->batch_number ?? null,
            'expiry_date' => $selectedStockItem->expiry_date ?? null,
            'unit_id' => $productDetails?->unit?->first()?->id ?? null,
            'quantity' => $this->quantity,
        ]);

        $stockCount->on_hand_quantity -= $this->quantity;
        $stockCount->save();

        $this->reset([
            'product_search',
            'product_id',
            'dose',
            'frequency',
            'custom_frequency',
            'paid',
            'pt_copay',
            'our_cost',
            'price',
            'profit',
            'date_given',
            'expiry_date',
            'batch_number',
            'quantity',
        ]);

        $this->dispatch('close-modal', 'add-prescription-modal');
        $this->dispatch('show-notification', __('Prescription added successfully.'));

        // header("Location: " . $_SERVER['REQUEST_URI']);
        // exit;
        redirect()->route('patient.index')->with('success', 'Prescription assigned successfully!');


    }
    public function resetPrescriptionForm()
{
    $this->reset([
        'product_search',
        'product_id',
        'products',
        'dose',
        'frequency',
        'custom_frequency',
        'paid',
        'pt_copay',
        'our_cost',
        'price',
        'profit',
        'date_given',
        'expiry_date',
        'batch_number',
        'quantity',
        'location',
        
    ]);
    $this->profit = 0;
    $this->show_dropdown = false;
}

public function editPayments($detailId)
    {
        logger('Editing payment details with ID: ' . $detailId);
        $this->details = PatientDetails::with(['patient'])->find($detailId);
        if (!$this->details) {
            $this->dispatch('show-notification', 'Detail not found!', 'error');
            return;
        }
        $this->chartnumber = $this->details->patient->chartnumber;
        $this->patientId = $this->details->patient_id;
        $this->paid = $this->details->paid;
        $this->our_cost = $this->details->our_cost;
        $this->pt_copay = $this->details->pt_copay;
        // Open the modal
        $this->dispatch('open-modal', 'edit-payment-modal');
    }

    public function updatePayment()
    {
        $this->validate([
            'paid' => 'nullable|numeric',
            'our_cost' => 'nullable|numeric',
            'pt_copay' => 'nullable|numeric',
        ]);

        if (!$this->details) {
            $this->dispatch('show-notification', 'Detail not found!', 'error');
            return;
        }

        $this->details->update([
            'paid' => $this->paid,
            'our_cost' => $this->our_cost,
            'pt_copay' => $this->pt_copay,
            'profit' => round($this->paid + $this->pt_copay - $this->our_cost, 2),
        ]);

        $this->dispatch('show-notification', 'Payment details updated successfully!', 'success');
        $this->dispatch('close-modal', 'edit-payment-modal');
        redirect()->route('patient.index')->with('success', 'Payment details updated successfully!');
    }
    public function incrementQuantity()
    {
        $this->quantity = min(100, $this->quantity + 1);
        $this->calculateOurCost();
    }

    public function decrementQuantity()
    {
        $this->quantity = max(1, $this->quantity - 1);
        $this->calculateOurCost();
    }

    public function updatedQuantity()
    {
       if($this->quantity<0){
        $this->quantity = 1;
       }
        $this->calculateOurCost();
    }

    public function updatedUnitCost()
    {
        $this->calculateOurCost();
    }

    private function calculateOurCost()
    {
        if ($this->quantity &&  $this->our_cost && $this->unit_cost) {
            $this->our_cost = number_format($this->quantity * $this->unit_cost, 2, '.', '');
        }
    }

    public function render()
    {
        return view('livewire.organization.patients.patients-component');
    }

}