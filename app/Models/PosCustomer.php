<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'phone',
        'email',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function sales()
    {
        return $this->hasMany(Pos::class, 'customer_id');
    }
}