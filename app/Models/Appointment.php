<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    protected $fillable = [
        'organization_id',
        'service_ids',
        'staff_id',
        'customer_id',
        'appointment_date',
        'start_time',
        'end_time',
        'price',
        'duration',
        'status',
        'created_by'
    ];

    protected $casts = [
        'service_ids' => 'array',
        'appointment_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Get the organization that owns the appointment
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the primary category/service
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AppointmentCategory::class, 'appointment_category_id');
    }

    /**
     * Get the staff member assigned to this appointment
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the customer for this appointment
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the user who created this appointment
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('start_time');
    }

    /**
     * Scope for today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}