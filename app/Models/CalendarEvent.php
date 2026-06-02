<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    public const REMINDER_WINDOW_DAY_BEFORE = 'day_before';
    public const REMINDER_WINDOW_DAY_OF_6AM = 'day_of_6am';
    public const REMINDER_WINDOW_ONE_HOUR_BEFORE = 'one_hour_before';

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
        'notification_channels',
        'reminder_delivery_log',
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
        'notification_channels' => 'array',
        'reminder_delivery_log' => 'array',
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
     * Get notification channels as array.
     */
    public function getNotificationChannelsArrayAttribute()
    {
        if (empty($this->notification_channels)) {
            return [];
        }

        if (is_array($this->notification_channels)) {
            return array_values(array_filter(array_map('trim', $this->notification_channels)));
        }

        $decoded = json_decode((string) $this->notification_channels, true);

        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }

        return [];
    }

    /**
     * Get the reminder delivery log as a normalized array.
     */
    public function getReminderDeliveryLogAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get the scheduled reminder anchor for this event.
     */
    public function getReminderAnchorDateTime(): ?Carbon
    {
        if (blank($this->event_date)) {
            return null;
        }

        $date = Carbon::parse($this->event_date)->startOfDay();

        if (blank($this->event_time)) {
            return $date->copy()->setTime(6, 0, 0);
        }

        $time = Carbon::parse($this->event_time)->format('H:i:s');

        return Carbon::parse($date->format('Y-m-d') . ' ' . $time);
    }

    /**
     * Get the reminder target time for a given window.
     */
    public function getReminderWindowTarget(string $window): ?Carbon
    {
        if (! $this->status) {
            return null;
        }

        $anchor = $this->getReminderAnchorDateTime();

        if (! $anchor) {
            return null;
        }

        return match ($window) {
            self::REMINDER_WINDOW_DAY_BEFORE => $anchor->copy()->subDay(),
            self::REMINDER_WINDOW_DAY_OF_6AM => Carbon::parse($anchor->format('Y-m-d') . ' 06:00:00'),
            self::REMINDER_WINDOW_ONE_HOUR_BEFORE => $anchor->copy()->subHour(),
            default => null,
        };
    }

    /**
     * Determine whether a reminder window is due right now.
     */
    public function isReminderWindowDue(string $window, ?Carbon $now = null, int $graceMinutes = 5): bool
    {
        $target = $this->getReminderWindowTarget($window);

        if (! $target) {
            return false;
        }

        $now = $now ?: Carbon::now();

        return $now->greaterThanOrEqualTo($target)
            && $now->lessThanOrEqualTo($target->copy()->addMinutes($graceMinutes));
    }

    /**
     * Get all due reminder windows for the current time.
     */
    public function getDueReminderWindows(?Carbon $now = null, int $graceMinutes = 5): array
    {
        $now = $now ?: Carbon::now();

        return collect([
            self::REMINDER_WINDOW_DAY_BEFORE,
            self::REMINDER_WINDOW_DAY_OF_6AM,
            self::REMINDER_WINDOW_ONE_HOUR_BEFORE,
        ])->filter(function (string $window) use ($now, $graceMinutes) {
            return ! $this->hasReminderWindowBeenSent($window)
                && $this->isReminderWindowDue($window, $now, $graceMinutes);
        })->values()->all();
    }

    /**
     * Check whether the given reminder window has already been sent.
     */
    public function hasReminderWindowBeenSent(string $window): bool
    {
        return filled(data_get($this->reminder_delivery_log, "{$window}.sent_at"));
    }

    /**
     * Mark a reminder window as sent in the delivery log.
     */
    public function markReminderWindowAsSent(string $window, ?Carbon $sentAt = null): void
    {
        $log = $this->reminder_delivery_log ?: [];

        $log[$window] = [
            'sent_at' => ($sentAt ?: Carbon::now())->toIso8601String(),
        ];

        $this->reminder_delivery_log = $log;
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
