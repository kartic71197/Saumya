<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleCount extends Model
{
    protected $fillable = [
        'cycle_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'expected_qty',
        'counted_qty',
        'variance',
        'notes',
        'status',
        'user_id',
        'admin_updated_qty',
        'counted_at',
    ];
    protected $appends = ['variance_percentage', 'status_badge'];

    public function getVariancePercentageAttribute()
{
    if ($this->counted_qty === null || $this->expected_qty == 0) {
        return null; // or 0 if you prefer
    }

    return round((($this->counted_qty - $this->expected_qty) / $this->expected_qty) * 100, 2);
}

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'completed' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Completed'],
            'approved' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Approved'],
            'rejected' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Rejected'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Unknown']
        };
    }

}
