<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    protected $fillable = [
        'organization_id',
        'appointment_category_id',
        'name',
        'description',
        'duration',
        'price',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(AppointmentCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(
            AppointmentTag::class,
            'appointment_service_tag',
            'appointment_service_id',   
            'appointment_tag_id'       
        );
    }

}
