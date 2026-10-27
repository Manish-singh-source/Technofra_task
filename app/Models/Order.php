<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'order_date' => 'date', 'vendor_expected_date' => 'date', 'vendor_received_date' => 'date',
        'client_delivery_date' => 'date', 'client_delivered_date' => 'date',
        'is_on_hold' => 'boolean', 'cancelled_at' => 'datetime',
    ];

    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function owner() { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function items() { return $this->hasMany(OrderItem::class)->orderBy('sort_order'); }
    public function history() { return $this->hasMany(OrderStatusHistory::class)->latest('changed_at'); }
    public function attachments() { return $this->hasMany(OrderAttachment::class); }

    public function getClientNameAttribute(): string { return $this->client?->name ?: 'Unknown client'; }
    public function getIsOverdueAttribute(): bool
    {
        $today = Carbon::today();
        return ($this->purchase_status !== 'Received' && $this->vendor_expected_date?->lt($today))
            || ($this->delivery_status !== 'Delivered' && $this->client_delivery_date?->lt($today));
    }
    public function getOverdueLegsAttribute(): array
    {
        $today = Carbon::today(); $legs = [];
        if ($this->purchase_status !== 'Received' && $this->vendor_expected_date?->lt($today)) $legs[] = 'Vendor';
        if ($this->delivery_status !== 'Delivered' && $this->client_delivery_date?->lt($today)) $legs[] = 'Client';
        return $legs;
    }
    public function refreshOverallStatus(): void
    {
        $this->overall_status = $this->overall_status === 'Cancelled' ? 'Cancelled' : (($this->purchase_status === 'Received' && $this->delivery_status === 'Delivered') ? 'Completed' : 'Open');
    }
}
