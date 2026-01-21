<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'ordered_qty',
        'received_qty',
        'date_received',
        'received_by',
        'batch_number',
        "expiry_date",
        'user_note'
    ];

    /**
     * Relationship: A receipt belongs to a Purchase Order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Relationship: A receipt is recorded by a User
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
