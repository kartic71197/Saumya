<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = [
        'product_id',
        'price',
        'cost',
        'effective_from',
        'effective_to',
        'changed_by'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
