<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'product_id',
        'stock_id',
        'quantity',
        'price',
        'unit',
        'location_id',
        'user_id',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stock()
    {
        // previously named batch(), but your table uses stock_id
        return $this->belongsTo(StockCount::class, 'stock_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
