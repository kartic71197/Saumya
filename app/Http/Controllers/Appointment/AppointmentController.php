<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentCategory;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display the appointment calendar view
     */
    public function index(Request $request)
    {
        $organizationId = auth()->user()->organization_id;

        // Get date range (default to current week)
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek());
        $endDate = $request->input('end_date', Carbon::parse($startDate)->endOfWeek());

        // Get all staff members for this organization
        $staff = User::where('organization_id', $organizationId)
            ->where('is_active', true) // Adjust role name as per your setup
            ->orderBy('name')
            ->get();

        // Get appointments for the date range
        $appointments = Appointment::where('organization_id', $organizationId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->with(['staff', 'customer', 'category'])
            ->get();

        // logger($appointments);

        return view('appointments.index', compact('staff', 'appointments', 'startDate', 'endDate'));
    }

    /**
     * Get appointments for a specific date and staff (AJAX)
     */
    public function getAppointments(Request $request)
    {
        $organizationId = auth()->user()->organization_id;
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $staffId = $request->input('staff_id');

        $query = Appointment::where('organization_id', $organizationId)
            ->whereDate('appointment_date', $date)
            ->with(['staff', 'customer', 'category']);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $appointments = $query->get();

        return response()->json([
            'appointments' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'staff_id' => $appointment->staff_id,
                    'staff_name' => $appointment->staff->name ?? 'N/A',
                    'customer_name' => $appointment->customer->customer_name ?? 'N/A',
                    'service_name' => $appointment->category->name ?? 'Service',
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'duration' => $appointment->duration,
                    'status' => $appointment->status,
                    'price' => $appointment->price,
                ];
            })
        ]);
    }

    /**
     * Show appointment details
     */
    public function show($id)
    {
        $appointment = Appointment::with(['staff', 'customer', 'category', 'organization'])
            ->findOrFail($id);

        // Ensure user has access to this appointment
        if ($appointment->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show create appointment form
     */
    // public function create()
    // {
    //     $organizationId = auth()->user()->organization_id;

    //     $staff = User::where('organization_id', $organizationId)
    //         ->where('is_active', true)
    //         ->get();

    //     $categories = AppointmentCategory::where('organization_id', $organizationId)
    //         ->get();

    //     $customers = Customer::where('customer_is_active', true)
    //         ->get();

    //     return view('appointments.create', compact('staff', 'categories', 'customers'));
    // }

    /**
     * Store a new appointment
     */
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'staff_id' => 'required|exists:users,id',
    //         'customer_id' => 'required|exists:customers,id',
    //         'appointment_date' => 'required|date',
    //         'start_time' => 'required',
    //         'end_time' => 'required',
    //         'service_ids' => 'required|array',
    //         'duration' => 'required|integer',
    //         'price' => 'required|numeric',
    //         'status' => 'required|in:pending,confirmed,completed,cancelled',
    //     ]);

    //     $validated['organization_id'] = auth()->user()->organization_id;
    //     $validated['created_by'] = auth()->id();

    //     $appointment = Appointment::create($validated);

    //     return redirect()->route('appointments.index')
    //         ->with('success', 'Appointment created successfully!');
    // }

    /**
     * Update appointment status
     */
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Ensure user has access
        if ($appointment->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Appointment status updated successfully',
            'appointment' => $appointment
        ]);
    }
}