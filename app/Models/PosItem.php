<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_id',
        'stock_count_id',
        'product_id',
        'qty',
        'price',
        'total',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function sale()
    {
        return $this->belongsTo(Pos::class, 'pos_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
    }
}