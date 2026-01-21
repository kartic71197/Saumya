<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldRep extends Model
{
    protected $fillable = [
        'organization_id',
        'supplier_id',
        'medrep_name',
        'medrep_phone',
        'medrep_email',
        'is_deleted',
    ];

    // Each Field Rep belongs to one Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Each Field Rep belongs to one Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
