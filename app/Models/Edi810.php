<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edi810 extends Model
{
    protected $fillable = [
        'po_number',
        'invoice_number',
        'invoice_date',
        'shipped_date',
        'time',
        'scac',
        'carrier_info',
        'transportation_method',
        'reference_qualifier',
        'reference_id',
        'product_code',
        'product_description',
        'unit',
        'price',
        'tax',
        'total_amount_due',
        'taxPercent',
        'qty',
        'file_name'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'purchase_order_number');
    }

}
