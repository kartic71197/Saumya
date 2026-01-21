<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    protected $table = "purchase_order_details";

    protected $fillable = [
        "purchase_order_id",
        "product_id",
        "quantity",
        "sub_total",
        "received_quantity",
        "unit_id",
        "tracking_link",
        "product_status",
        'canceled_by',
        'cancelation_note',
    ];

    // Relationship with PurchaseOrder
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    // Relationship with Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
    public function canceledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }
}
