<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mycatalog extends Model
{
    protected $table = 'mycatalogs';

    protected $fillable = [
        'product_id',
        'location_id',
        'total_quantity',
        'alert_quantity',
        'par_quantity'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function stockCounts()
    {
        return $this->hasMany(StockCount::class, 'product_id', 'product_id')
            ->whereColumn('location_id', 'location_id');
    }
}
