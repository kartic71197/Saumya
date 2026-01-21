<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\AppointmentCategory;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientAppointmentController extends Controller
{
    public function index()
    {
        $organizations = Organization::WhereHas('appointmentCategories')->where('is_active', true)->get();
        return view('appointments.client.index', compact('organizations'));
    }

    public function create(Organization $organization)
    {
        $categories = AppointmentCategory::where('organization_id', $organization->id)
            ->with([
                'services' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->get();

        // Get staff members for this organization
        $staffMembers = User::where('organization_id', $organization->id)
            ->where('is_active', true)
            ->select('id', 'name', 'email')
            ->get();

        return view('appointments.client.create', compact('organization', 'categories', 'staffMembers'));
    }

    public function store(Request $request, Organization $organization)
    {
        // logger()->info('Appointment Booking Request:', $request->all());

        $validated = $request->validate([
            'services' => 'required|array|min:1',
            'services.*' => 'exists:appointment_services,id', // IMPORTANT
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'staff_id' => 'required|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // 1️⃣ Find or create customer
            $customer = Customer::firstOrCreate(
                ['customer_email' => $validated['customer_email']],
                [
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                ]
            );

            // 2️⃣ Fetch services (from services table, not categories)
            $services = AppointmentService::whereIn('id', $validated['services'])->get();

            if ($services->isEmpty()) {
                throw new \Exception('No valid services found.');
            }

            // 3️⃣ Calculate totals
            $totalPrice = $services->sum('price');
            $totalDuration = $services->sum('duration');

            // 4️⃣ Calculate end time
            $startTime = \Carbon\Carbon::parse(
                $validated['appointment_date'] . ' ' . $validated['start_time']
            );

            $endTime = $startTime->copy()->addMinutes($totalDuration);

            // 5️⃣ Create appointment (JSON service IDs stored)
            $appointment = Appointment::create([
                'organization_id' => $organization->id,
                'service_ids' => json_encode(array_map('intval', $validated['services'])),
                'staff_id' => $validated['staff_id'],
                'customer_id' => $customer->id,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $endTime->format('H:i:s'),
                'price' => $totalPrice,
                'duration' => $totalDuration,
                'status' => 'pending',
                'created_by' => auth()->user()->id ?? $customer->id,
            ]);

            DB::commit();

            return redirect()
                ->route('appointments.client.confirmation', $appointment)
                ->with('success', 'Appointment booked successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();

            logger()->error('Appointment Booking Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to book appointment. Please try again.');
        }
    }

    public function confirmation(Appointment $appointment)
    {
        $appointment->load(['customer', 'staff', 'category', 'organization']);
        return view('appointments.client.confirmation', compact('appointment'));
    }
}