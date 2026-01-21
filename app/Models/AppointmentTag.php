<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AppointmentTag extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
    ];

   public function services()
{
    return $this->belongsToMany(
        AppointmentService::class,
        'appointment_service_tag',
        'appointment_tag_id',       
        'appointment_service_id' 
    );
}

}
