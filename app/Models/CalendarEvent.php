<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'email_recipients',
        'notification_sent',
        'notification_sent_at',
        'created_by',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date' => 'datetime',
        'event_time' => 'datetime',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
        'status' => 'boolean',
    ];

    /**
     * Get the user who created the event.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active events.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include inactive events.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to only include pending notifications.
     */
    public function scopePendingNotification($query)
    {
        return $query->where('notification_sent', false)
                     ->where('status', 1);
    }

    /**
     * Get email recipients as array.
     */
    public function getEmailRecipientsArrayAttribute()
    {
        return array_filter(array_map('trim', explode(',', $this->email_recipients)));
    }

    /**
     * Check if event notification should be sent.
     */
    public function shouldSendNotification()
    {
        if ($this->notification_sent || !$this->status) {
            return false;
        }

        $eventDateTime = Carbon::parse($this->event_date->format('Y-m-d') . ' ' . $this->event_time->format('H:i:s'));
        $now = Carbon::now();

        // Send notification if event time has passed or is within 5 minutes
        return $now->greaterThanOrEqualTo($eventDateTime->subMinutes(5));
    }
}
