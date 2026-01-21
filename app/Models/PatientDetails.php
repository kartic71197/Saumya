<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDetails extends Model
{
    protected $fillable = [
        'date_given',
        'patient_id',
        'product_id',
        'quantity',
        'dose',
        'frequency',
        'paid',
        'our_cost',
        'price',
        'pt_copay',
        'profit',
        'batch_number',
        'expiry_date',
        'unit'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
