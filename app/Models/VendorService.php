<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorService extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'service_name',
        'service_details',
        'plan_type',
        'start_date',
        'end_date',
        'billing_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'billing_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vendor that owns the vendor service.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'expired' => 'danger',
            'pending' => 'warning',
            default => 'primary'
        };
    }

    public function getEffectiveStatusAttribute(): string
    {
        $today = Carbon::today();
        $fiveDaysFromNow = $today->copy()->addDays(5);

        if (in_array($this->status, ['inactive', 'pending', 'expired'], true)) {
            return $this->status;
        }

        if ($this->end_date && $this->end_date->lt($today)) {
            return 'expired';
        }

        if ($this->end_date && $this->end_date->between($today, $fiveDaysFromNow)) {
            return 'upcoming';
        }

        return $this->status;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->effective_status) {
            'upcoming' => 'Up Coming',
            'pending' => 'Hold',
            default => ucfirst($this->effective_status),
        };
    }

    public function getEffectiveStatusBadgeAttribute(): string
    {
        return match ($this->effective_status) {
            'active' => 'success',
            'inactive' => 'danger',
            'expired' => 'orange',
            'pending' => 'warning',
            'upcoming' => 'info',
            default => 'primary',
        };
    }

    public function getTabKeyAttribute(): string
    {
        return match ($this->effective_status) {
            'upcoming' => 'upcoming',
            'active' => 'active',
            'inactive' => 'inactive',
            'pending' => 'pending',
            'expired' => 'expired',
            default => 'all',
        };
    }
}
