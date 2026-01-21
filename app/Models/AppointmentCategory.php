<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentCategory extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'description',
    ];

    public function services()
    {
        return $this->hasMany(AppointmentService::class, 'appointment_category_id');
    }
}
