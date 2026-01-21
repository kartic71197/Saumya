<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PurchaseOrder extends Model
{
    protected $table = "purchase_orders";
    protected $fillable = [
        'purchase_order_number',
        'merge_id',
        'supplier_id',
        'organization_id',
        'location_id',
        'bill_to_location_id',
        'ship_to_location_id',
        'status',
        'total',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'invoice',
        'note',
        'notes',
        'packing_slips',
        'is_order_placed',
        'bill_to_number',
        'ship_to_number',
        'invoice_path',
        'acknowledgment_path',
        'invoice_uploaded_at',
        'tracking_link',
        'external_order'

    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->payments()
            ->where('payment_status', 'completed')
            ->sum('amount') >= $this->total;
    }


    public function edi810s()
    {
        return $this->hasMany(Edi810::class, 'po_number', 'purchase_order_number');
    }

    protected $casts = [
        'invoice_uploaded_at' => 'datetime',
        'notes' => 'array',
        'packing_slips' => 'array',
    ];

    // Helper methods to get file URLs
    public function getInvoiceUrlAttribute()
    {
        return $this->invoice_path ? Storage::url($this->invoice_path) : null;
    }

    public function getAcknowledmentUrlAttribute()
    {
        return $this->acknowledgment_path ? Storage::url($this->acknowledgment_path) : null;
    }

    // Helper methods to check if files exist
    public function hasInvoice()
    {
        return !is_null($this->invoice_path) && Storage::exists($this->invoice_path);
    }


    public function hasAcknowledgment()
    {
        return !is_null($this->acknowledgment_path) && Storage::exists($this->acknowledgment_path);
    }
    public function purchasedProducts()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_id');
    }
    public function purchaseSupplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function purchaseLocation()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function shippingLocation()
    {
        return $this->belongsTo(Location::class, 'ship_to_location_id');
    }
    public static function generatePurchaseOrderNumber()
    {
        $year = date('Y');
        $lastOrder = self::where('purchase_order_number', 'LIKE', "PO-{$year}-%")
            ->latest('id')
            ->first();
        if (!$lastOrder) {
            $nextNumber = '000001';
        } else {
            $lastNumber = (int) substr($lastOrder->purchase_order_number, -6);
            $nextNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return "PO-{$year}-{$nextNumber}";
    }
    public static function generateSampleNumber()
    {
        $year = date('Y');
        $lastOrder = self::where('purchase_order_number', 'LIKE', "SO-{$year}-%")
            ->latest('id')
            ->first();
        if (!$lastOrder) {
            $nextNumber = '000001';
        } else {
            $lastNumber = (int) substr($lastOrder->purchase_order_number, -6);
            $nextNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return "SO-{$year}-{$nextNumber}";
    }
    public static function generateMergeId()
    {
        $year = date('Y');

        $lastMergeOrder = self::where('merge_id', 'LIKE', "MR-{$year}-%")
            ->latest('id')
            ->first();

        if (!$lastMergeOrder) {
            $nextNumber = '000001';
        } else {
            $lastNumber = (int) substr($lastMergeOrder->merge_id, -6);
            $nextNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return "MR-{$year}-{$nextNumber}";
    }
    public function billingLocation()
    {
        return $this->belongsTo(Location::class, 'bill_to_location_id');
    }
    public function receipts()
    {
        return $this->hasMany(PoReceipt::class);
    }

    public function edi855s()
    {
        return $this->hasMany(\App\Models\Edi855::class, 'purchase_order', 'purchase_order_number');
    }

    public function edi856s()
    {
        return $this->hasMany(\App\Models\Edi856::class, 'poNumber', 'purchase_order_number');
    }
    public function getSupplierProgressStepAttribute()
    {
        // 1 = Ordered
        // 2 = Acknowledged
        // 3 = Shipped
        // 4 = Delivered / Partial
        if ($this->edi856s()->exists() || $this->tracking_link) {
            // Check delivered qty
            // $total = $this->purchasedProducts()->sum('quantity');
            // $received = $this->receipts()->sum('received_qty');

            // if ($received >= $total && $total > 0) {
            //     return 4; // Delivered
            // }

            // if ($received > 0) {
            //     return 4; // Partial also in same bar end
            // }

            return 3; // Shipped
        }

        if ($this->edi855s()->exists() || $this->acknowledgment_path || $this->edi810s()->exists()) {
            return 2;
        }



        return 1; // Ordered (default)
    }




}
