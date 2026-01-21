<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $table = 'subcategories';

    protected $fillable = [
        'subcategory',
        'category_id',
        'is_active'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products()
{
    return $this->hasMany(Product::class, 'subcategory_id');
}
}
