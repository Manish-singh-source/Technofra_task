<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'vendor_id',
        'service_name',
        'service_details',
        'remark_text',
        'remark_color',
        'start_date',
        'end_date',
        'billing_date',
        'status',
        'five_days_notified',
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
     * Get the client that owns the service.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the vendor that owns the service.
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
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'expired' => 'danger',
            'pending' => 'warning',
            default => 'primary'
        };
    }

    public function getRemarkBadgeStyleAttribute(): string
    {
        return match($this->remark_color) {
            'yellow' => 'background-color: #fff3cd; color: #664d03; border-color: #ffec99;',
            'red' => 'background-color: #f8d7da; color: #842029; border-color: #f1aeb5;',
            'green' => 'background-color: #d1e7dd; color: #0f5132; border-color: #a3cfbb;',
            'blue' => 'background-color: #cfe2ff; color: #084298; border-color: #9ec5fe;',
            'gray' => 'background-color: #e2e3e5; color: #41464b; border-color: #c4c8cb;',
            default => 'background-color: #fff3cd; color: #664d03; border-color: #ffec99;',
        };
    }
}
