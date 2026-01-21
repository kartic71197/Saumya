<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentServiceTag extends Model
{
    protected $table = 'appointment_service_tags';

    protected $fillable = [
        'appointment_service_id',
        'tag',
    ];
}
