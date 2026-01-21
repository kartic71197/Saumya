<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edi856 extends Model
{
    protected $fillable = ['id', 'poNumber', 'internalRefNumber', 'date', 'time', 'SCAC', 'carrier', 'invoiceNumber', 'product_code', 'product_desc', 'unitShipped', 'units', 'status', 'shippedDate', 'file_name'];
}
