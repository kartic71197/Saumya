<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cycle extends Model
{
    protected $fillable = [
        'cycle_code',
        'cycle_name',
        'organization_id',
        'location_id',
        'description',
        'status',
        'ended_at',
        'created_by',
        'schedule_date', 
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'schedule_date' => 'date',
    ];

    protected $appends = ['status_badge'];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function cycleCounts()
    {
        return $this->hasMany(CycleCount::class);
    }

    // Scope for active cycles
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'completed', 'closed']);
    }

    public static function generateCycleName()
{
    $lastCycle = self::where('cycle_name', 'LIKE', 'CC%')
        ->latest('id')
        ->first();
        
    if (!$lastCycle) {
        $nextNumber = '01';
    } else {
        // Extract numbers from CC01, CC02, etc.
        $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastCycle->cycle_name);
        $nextNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
    }

    return "CC{$nextNumber}";
}

    // Status badge accessor
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'completed' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Completed'],
            'closed' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Closed'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Unknown']
        };
    }
}
