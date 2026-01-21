<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'organization_id',
        'amount',
        'payment_method',
        'payment_status',
        'provider',
        'provider_payment_id',
        'provider_invoice_id',
        'provider_invoice_number',
        'provider_payload',
        'paid_at',
    ];

    protected $casts = [
        'provider_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
