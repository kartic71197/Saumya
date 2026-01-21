<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAvailability extends Model
{
    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time'
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
