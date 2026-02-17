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
        'whatsapp_recipients',
        'notification_sent',
        'notification_sent_at',
        'reminder_10min_sent',
        'reminder_10min_sent_at',
        'event_time_notification_sent',
        'event_time_notification_sent_at',
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
        'reminder_10min_sent' => 'boolean',
        'reminder_10min_sent_at' => 'datetime',
        'event_time_notification_sent' => 'boolean',
        'event_time_notification_sent_at' => 'datetime',
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
     * Get WhatsApp recipients as array.
     */
    public function getWhatsappRecipientsArrayAttribute()
    {
        if (empty($this->whatsapp_recipients)) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $this->whatsapp_recipients)));
    }

    /**
     * Get event datetime as Carbon instance.
     */
    public function getEventDateTimeAttribute()
    {
        return Carbon::parse($this->event_date->format('Y-m-d') . ' ' . $this->event_time->format('H:i:s'));
    }

    /**
     * Check if 10-minute reminder should be sent.
     */
    public function shouldSend10MinReminder()
    {
        if ($this->reminder_10min_sent || !$this->status) {
            return false;
        }

        $eventDateTime = $this->event_date_time;
        $reminderTime = $eventDateTime->copy()->subMinutes(10);
        $now = Carbon::now();

        // Send reminder if current time is at or past the reminder time
        return $now->greaterThanOrEqualTo($reminderTime) && $now->lessThan($eventDateTime);
    }

    /**
     * Check if event time notification should be sent.
     */
    public function shouldSendEventTimeNotification()
    {
        if ($this->event_time_notification_sent || !$this->status) {
            return false;
        }

        $eventDateTime = $this->event_date_time;
        $now = Carbon::now();

        // Keep a short grace window to avoid missing notifications due to cron/queue delays.
        return $now->greaterThanOrEqualTo($eventDateTime) && $now->lessThanOrEqualTo($eventDateTime->copy()->addMinutes(5));
    }

    /**
     * Scope a query to only include events pending 10-min reminder.
     */
    public function scopePending10MinReminder($query)
    {
        return $query->where('reminder_10min_sent', false)
                     ->where('status', 1);
    }

    /**
     * Scope a query to only include events pending event-time notification.
     */
    public function scopePendingEventTimeNotification($query)
    {
        return $query->where('event_time_notification_sent', false)
                     ->where('status', 1);
    }

    /**
     * Check if event notification should be sent.
     * @deprecated Use shouldSend10MinReminder() or shouldSendEventTimeNotification() instead
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
