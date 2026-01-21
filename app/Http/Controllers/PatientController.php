<?php

namespace App\Http\Controllers;

use App\Imports\PatientImport;
use App\Models\Location;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Excel;

class PatientController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role;
        // Check permission or specific role ID
        if ($role?->hasPermission('view_patient') || $user->role_id <= 2) {
            return view("organization.patients.index");
        }
        return redirect('/dashboard')->with('error', 'You do not have permission to view this page.');
    }

    public function importPatients(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $import = new PatientImport();
        Excel::import($import, $request->file('csv_file'));
        if (!empty($import->getskippedpatients())) {
            session()->flash('warning', 'Some patients were skipped. Download skipped_patients.csv for details.');
            return $import->downloadSkippedCsv();
        }

        //  session()->flash('success', 'Patients imported successfully!');
        //  return back();
        return redirect()->route('patient.index')->with('success', 'Patients imported successfully!');
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        return view('organization.patients.patient-details', compact('patient'));
        //resources\views\organization\patients\patient-details.blade.php
    }


    public function getPatientsData()
    {
        $user = auth()->user();
        $patients = Patient::with(['details.product'])
            ->where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->get();

        $formatted = collect();

        foreach ($patients as $patient) {
            $locationName = Location::where('id', $patient->location)->first()->name ?? null;
            foreach ($patient->details as $detail) {

                $formatted->push([
                    'id' => $patient->id,
                    'chartnumber' => $patient->chartnumber,
                    'initials' => $patient->initials,
                    'ins_type' => $patient->ins_type,
                    'provider' => $patient->provider,
                    'icd' => $patient->icd,
                    'address' => $patient->address,
                    'city' => $patient->city,
                    'state' => $patient->state,
                    'country' => $patient->country,
                    'pin_code' => $patient->pin_code,
                    'is_active' => $patient->is_active,
                    'organization_id' => $patient->organization_id,
                    'location' => $locationName ?? null,
                    'date_given' => $detail->date_given,
                    'product_name' => $detail->product->product_name ?? 'N/A',
                    'quantity' => $detail->quantity,
                    'dose' => $detail->dose,
                    'frequency' => $detail->frequency,
                    'paid' => $detail->paid,
                    'our_cost' => $detail->our_cost,
                    'price' => $detail->price,
                    'pt_copay' => $detail->pt_copay,
                    'profit' => $detail->profit,
                    'batch_number' => $detail->batch_number,
                    'expiry_date' => $detail->expiry_date,
                    'unit' => $detail->unit,
                    'detail_id' => $detail->id,
                ]);
            }
            if ($patient->details->isEmpty()) {
                $formatted->push([
                    'id' => $patient->id,
                    'chartnumber' => $patient->chartnumber,
                    'initials' => $patient->initials,
                    'ins_type' => $patient->ins_type,
                    'provider' => $patient->provider,
                    'icd' => $patient->icd,
                    'address' => $patient->address,
                    'city' => $patient->city,
                    'state' => $patient->state,
                    'country' => $patient->country,
                    'pin_code' => $patient->pin_code,
                    'is_active' => $patient->is_active,
                    'organization_id' => $patient->organization_id,
                    'location' => $locationName,
                    'date_given' => null,
                    'product_name' => null,
                    'quantity' => null,
                    'dose' => null,
                    'frequency' => null,
                    'paid' => null,
                    'our_cost' => null,
                    'pt_copay' => null,
                    'profit' => null,
                    'batch_number' => null,
                    'expiry_date' => null,
                    'unit' => null,
                    'detail_id' => null
                ]);
            }
        }

        return response()->json($formatted);
    }



}
