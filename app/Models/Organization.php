<?php

namespace App\Models;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use Billable;
    protected $fillable = [
        'id',
        'image',
        'name',
        'plan_id',
        'email',
        'phone',
        'city',
        'state',
        'country',
        'pin',
        'address',
        'plan_valid',
        'is_active',
        'is_deleted',
        'currency',
        'timezone',
        'date_format',
        'time_format',
        'theme',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'is_rep_org',
        'organization_code',
        'slug'
    ];

    // public static function fetchCode()
    // {
    //     $lastId = Organization::max('id') ?? 0;
    //     return $lastId + 1;
    // }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            $organization->slug = Str::slug($organization->name);
        });
    }

    /**
     * An organization has many appointment categories
     */
    public function appointmentCategories()
    {
        return $this->hasMany(AppointmentCategory::class);
    }

    /**
     * An organization has many appointment services
     */
    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }


    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function locations()
    {
        return $this->hasMany(Location::class, 'org_id');
    }
    public function users()
    {
        return $this->hasMany(User::class, 'organization_id');
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'organization_id');
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }
    public function openTickets()
    {
        return $this->hasMany(Ticket::class, 'organization_id')->where('status', 'open');
    }

    public function openOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'organization_id')->whereIn('status', ['open', 'ordered', 'partial']);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

}
