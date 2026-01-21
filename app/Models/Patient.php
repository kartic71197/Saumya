<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'chartnumber',
        'ins_type',
        'provider',
        'icd',
        'address',
        'city',
        'state',
        'country',
        'pin_code',
        'is_active',
        'organization_id',
        'location',
        'initials'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function details()
    {
        return $this->hasMany(PatientDetails::class);
    }

    public function Patientlocation()
    {
        return $this->belongsTo(Location::class, 'location');
    }
}
