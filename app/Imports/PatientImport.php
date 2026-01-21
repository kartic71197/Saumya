<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PatientImport implements ToModel, WithHeadingRow
{
    public $current = 0;
    private $userId;
    private $organizationId;
    private $activeLocations;
    private $skippedPatients = [];

    public function __construct()
    {
        $this->userId = auth()->user()->id;
        $this->organizationId = auth()->user()->organization_id;

        $this->activeLocations = Location::where('org_id', $this->organizationId)
            ->where('is_active', true)
            ->pluck('name')
            ->toArray();
    
    }

    public function model(array $row)
    {
        $this->current++;

        $chartnumber = $row['chartnumber'] ?? null;
        $initials    = $row['initials'] ?? null;
        $locationName    = $row['location'] ?? null;
        $address     = $row['address'] ?? null;
        $city        = $row['city'] ?? null;
        $state       = $row['state'] ?? null;
        $country     = $row['country'] ?? null;
        $pin_code    = $row['pin_code'] ?? null;
        $ins_type    = $row['ins_type'] ?? null;
        $provider    = $row['provider'] ?? null;
        $icd         = $row['icd'] ?? null;

            // 🔹 Convert location name to ID
        $locationId = null;
        if ($locationName) {
        $location = Location::where('name', $locationName)
            ->where('org_id', $this->organizationId)
            ->where('is_active', true)
            ->first();
            if ($location) {
            $locationId = $location->id;
        } else {
            // Location doesn't exist or not active
            $this->skippedPatients[] = [
                'chartnumber' => $chartnumber ?? 'N/A',
                'issue' => "Invalid location: {$locationName}"
            ];
            return null;
        }
    }

        // 🔹 Validate required fields
        if (!$chartnumber) {
            $this->skippedPatients[] = [
                'chartnumber' => 'N/A',
                'issue' => 'Chart number missing'
            ];
            return null;
        }

        if (!$initials) {
            $this->skippedPatients[] = [
                'chartnumber' => $chartnumber,
                'issue' => 'Initials missing'
            ];
            return null;
        }

        if (!$country) {
            $this->skippedPatients[] = [
                'chartnumber' => $chartnumber,
                'issue' => 'Country missing'
            ];
            return null;
        }

        if (!$pin_code) {
            $this->skippedPatients[] = [
                'chartnumber' => $chartnumber,
                'issue' => 'Pin_code missing'
            ];
            return null;
        }

        // 🔹 Ensure chartnumber unique in this org
        $exists = Patient::where('chartnumber', $chartnumber)
            ->where('organization_id', $this->organizationId)
            ->exists();

        if ($exists) {
            $this->skippedPatients[] = [
                'chartnumber' => $chartnumber,
                'issue' => 'Chart number already exists'
            ];
            return null;
        }

        //  Insert patient
        try {
            DB::beginTransaction();

            $patient = new Patient([
                'chartnumber'      => $chartnumber,
                'initials'         => $initials,
                'location'         => $locationId,
                'address'          => $address,
                'city'             => $city,
                'state'            => $state,
                'country'          => $country,
                'pin_code'         => $pin_code,
                'organization_id'  => $this->organizationId,
                'ins_type'         => $ins_type,
                'provider'         => $provider,
                'icd'              => $icd,
                'is_active'        => 1,
            ]);
             $patient->save();
            DB::commit();
            return $patient;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Patient import error: ' . $e->getMessage());

            $this->skippedPatients[] = [
                'chartnumber' => $chartnumber,
                'issue' => 'Database Error: ' . $e->getMessage()
            ];
            return null;
        }

    }

    // download skipped products as a CSV file
    public function downloadSkippedCsv()
    {
        if (empty($this->skippedPatients)) {
            return response()->json(['message' => 'No skipped patients to download'], 400);
        }

        $headers = ['chartnumber', 'issue'];
        $csv = implode(',', $headers) . "\n";

        foreach ($this->skippedPatients as $row) {
            $csv .= '"' . $row['chartnumber'] . '","' . $row['issue'] . '"' . "\n";
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'skipped_patients_' . now()->format('Ymd_His') . '.csv');
    }

    public function getskippedPatients()
    {
        return $this->skippedPatients;
    }
}
