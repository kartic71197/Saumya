<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\AppointmentCategory;
use App\Models\PosCustomer;
use App\Models\User;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Livewire\Component;
use App\Models\MedicalRepSales;
use App\Models\MedicalRepSalesProducts;
use App\Models\MedrepShipment;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\ProductUnit;
use App\Models\Shipment;
use App\Models\ShipmentProducts;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\Unit;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\FunctionPrefix;
use App\Services\UPSShippingService;

class Create extends Component
{
    public $category_id;
    public $staff_id;
    public $customer_id;
    public $date;
    public $start_time;

    public $leafCategories = [];

    public $staff = [];

    public $customers = [];

    public function mount()
    {
        $this->leafCategories = AppointmentCategory::where('organization_id', auth()->user()->organization_id)
            ->where('is_leaf', true)
            ->orderBy('name')
            ->get();
        $this->staff = User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get();

        $this->customers = PosCustomer::where(
                'organization_id',
                auth()->user()->organization_id
            )
            ->orderBy('name')
            ->get();

        
    }

    public function save()
    {
        $category = AppointmentCategory::findOrFail($this->category_id);

        if (!$category->is_leaf) {
            throw ValidationException::withMessages([
                'category_id' => 'Please select a final service.'
            ]);
        }

        Appointment::create([
            'organization_id' => auth()->user()->organization_id,
            'appointment_category_id' => $category->id,
            'staff_id' => $this->staff_id,
            'customer_id' => $this->customer_id,
            'appointment_date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => Carbon::parse($this->start_time)
                ->addMinutes($category->duration),
            'price' => $category->price,
            'duration' => $category->duration,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'Appointment booked successfully');
        return redirect()->route('appointments.calendar');
    }

    public function render()
    {
        return view('livewire.appointments.create');
    }
}
