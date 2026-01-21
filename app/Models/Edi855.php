<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edi855 extends Model
{
    protected $fillable = [
        'purchase_order',
        'ack_date',
        'bill_to',
        'ship_to',
        'product_name',
        'product_code',
        'ordered_qty',
        'ordered_unit',
        'unit_price',
        'ack_qty',
        'ack_unit',
        'ack',
        'file_name',
        'ack_type'
    ];
}
