<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'id',
        'supplier_name',
        'supplier_slug',
        'supplier_email',
        'supplier_phone',
        'supplier_address',
        'supplier_city',
        'supplier_state',
        'supplier_country',
        'supplier_zip',
        'supplier_vat',
        'created_by',
        'updated_by',
        'is_active',
        'int_type', //removed 'is_edi' and added 'int_type'
        'verified'

    ];
    // New methods to check integration type
    public function isEdi(): bool
    {
        return $this->int_type === 'EDI';
    }

    public function isEmail(): bool
    {
        return $this->int_type === 'EMAIL';
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
    // In your Supplier model
public function products()
{
    return $this->hasMany(Product::class, 'product_supplier_id');
}
}
