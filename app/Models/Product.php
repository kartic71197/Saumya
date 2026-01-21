<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'image',
        'product_name',
        'product_code',
        'product_supplier_id',
        'product_description',
        'has_expiry_date',
        'created_by',
        'updated_by',
        'manufacture_code',
        'organization_id',
        'category_id',
        'subcategory_id',
        'cost',
        'price',
        'is_active',
        'brand_id',
        'weight',
        'length',
        'width',
        'height',
        'is_sample',
        'dose'
    ];

    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }
    public function unit()
    {
        return $this->hasMany(ProductUnit::class)->where('is_base_unit', 1)->with('unit');
    }

    public function baseUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base_unit', 1)->with('unit');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'product_supplier_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function purchaseOrderDetails()
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }
    public function cycleCounts()
    {
        return $this->hasMany(CycleCount::class, 'product_id');
    }
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

}
