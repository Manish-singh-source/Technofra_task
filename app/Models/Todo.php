<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    public const REMINDER_WINDOW_DAY_BEFORE = 'day_before';
    public const REMINDER_WINDOW_DAY_OF_6AM = 'day_of_6am';
    public const REMINDER_WINDOW_ONE_HOUR_BEFORE = 'one_hour_before';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'attachments',
        'task_date',
        'task_time',
        'repeat_interval',
        'repeat_unit',
        'repeat_days',
        'reminder_time',
        'reminder_email',
        'reminder_whatsapp',
        'reminder_delivery_log',
        'starts_on',
        'ends_type',
        'ends_on',
        'ends_after_occurrences',
        'is_completed',
        'completed_at',
        'last_reminded_occurrence_on',
        'last_reminder_sent_at',
    ];

    protected $casts = [
        'task_date' => 'date',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'completed_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'reminder_delivery_log' => 'array',
        'repeat_days' => 'array',
        'attachments' => 'array',
        'is_completed' => 'boolean',
        'reminder_email' => 'boolean',
        'reminder_whatsapp' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    public function getRepeatDaysListAttribute()
    {
        return collect($this->repeat_days ?: [])->filter()->values()->all();
    }

    public function getDisplayScheduleAttribute()
    {
        $time = $this->formatDisplayTime($this->task_time) ?? 'Any time';
        $label = 'Every ' . $this->repeat_interval . ' ' . $this->repeat_unit;

        if ($this->repeat_interval > 1) {
            $label .= 's';
        }

        if ($this->repeat_unit === 'week' && !empty($this->repeat_days_list)) {
            $label .= ' on ' . collect($this->repeat_days_list)->map(function ($day) {
                return ucfirst(substr($day, 0, 3));
            })->implode(', ');
        }

        return $label . ' at ' . $time;
    }

    protected function formatDisplayTime(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        $format = strlen($time) === 5 ? 'H:i' : 'H:i:s';

        return Carbon::createFromFormat($format, $time)->format('h:i A');
    }

    public function getNextOccurrenceDate(?Carbon $from = null): ?Carbon
    {
        $fromDate = ($from ?: now())->copy()->startOfDay();
        $firstDate = $this->getFirstOccurrenceDate();

        if ($fromDate->lt($firstDate)) {
            $fromDate = $firstDate->copy();
        }

        $cursor = $fromDate->copy();
        $limit = $fromDate->copy()->addYears(10);

        while ($cursor->lte($limit)) {
            if ($this->isOccurrenceDate($cursor)) {
                return $cursor->copy();
            }

            $cursor->addDay();
        }

        return null;
    }

    public function getReminderDateTimeForOccurrence(Carbon $occurrenceDate): Carbon
    {
        $time = $this->reminder_time ?: ($this->task_time ?: '09:00:00');

        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return Carbon::parse($occurrenceDate->format('Y-m-d') . ' ' . $time);
    }

    public function getReminderWindowTargetForOccurrence(Carbon $occurrenceDate, string $window): ?Carbon
    {
        $occurrenceDate = $occurrenceDate->copy()->startOfDay();
        $anchor = $this->getReminderDateTimeForOccurrence($occurrenceDate);

        return match ($window) {
            self::REMINDER_WINDOW_DAY_BEFORE => $anchor->copy()->subDay(),
            self::REMINDER_WINDOW_DAY_OF_6AM => $occurrenceDate->copy()->setTime(6, 0, 0),
            self::REMINDER_WINDOW_ONE_HOUR_BEFORE => $anchor->copy()->subHour(),
            default => null,
        };
    }

    public function isReminderWindowDueForOccurrence(Carbon $occurrenceDate, string $window, Carbon $now, int $graceMinutes = 5): bool
    {
        $target = $this->getReminderWindowTargetForOccurrence($occurrenceDate, $window);

        if (! $target) {
            return false;
        }

        return $now->greaterThanOrEqualTo($target)
            && $now->lessThanOrEqualTo($target->copy()->addMinutes($graceMinutes));
    }

    public function getDueReminderWindows(Carbon $now, int $graceMinutes = 5): array
    {
        $occurrenceDates = collect([
            $now->copy()->startOfDay(),
            $now->copy()->addDay()->startOfDay(),
        ])->unique(fn (Carbon $date) => $date->format('Y-m-d'));

        $windows = [
            self::REMINDER_WINDOW_DAY_BEFORE,
            self::REMINDER_WINDOW_DAY_OF_6AM,
            self::REMINDER_WINDOW_ONE_HOUR_BEFORE,
        ];

        $due = [];

        foreach ($occurrenceDates as $occurrenceDate) {
            if (! $this->isOccurrenceDate($occurrenceDate)) {
                continue;
            }

            foreach ($windows as $window) {
                if ($this->hasReminderWindowBeenSentForOccurrence($window, $occurrenceDate)) {
                    continue;
                }

                if ($this->isReminderWindowDueForOccurrence($occurrenceDate, $window, $now, $graceMinutes)) {
                    $due[] = [
                        'occurrence_date' => $occurrenceDate->copy(),
                        'window' => $window,
                    ];
                }
            }
        }

        return $due;
    }

    public function getOccurrenceInReminderWindow(Carbon $now, int $graceMinutes = 5): ?Carbon
    {
        $due = $this->getDueReminderWindows($now, $graceMinutes);

        return $due[0]['occurrence_date'] ?? null;
    }

    public function hasReminderWindowBeenSentForOccurrence(string $window, Carbon $occurrenceDate): bool
    {
        $occurrenceKey = $occurrenceDate->format('Y-m-d');

        return filled(data_get($this->reminder_delivery_log, "{$occurrenceKey}.{$window}.sent_at"));
    }

    public function markReminderWindowAsSentForOccurrence(string $window, Carbon $occurrenceDate, ?Carbon $sentAt = null): void
    {
        $log = $this->reminder_delivery_log ?: [];
        $occurrenceKey = $occurrenceDate->format('Y-m-d');

        $log[$occurrenceKey][$window] = [
            'sent_at' => ($sentAt ?: Carbon::now())->toIso8601String(),
        ];

        $this->reminder_delivery_log = $log;
    }

    /**
     * Get a human-readable label for a reminder window.
     */
    public function getReminderWindowLabel(string $window): string
    {
        return match ($window) {
            self::REMINDER_WINDOW_DAY_BEFORE => '1 Day Before',
            self::REMINDER_WINDOW_DAY_OF_6AM => '6 AM',
            self::REMINDER_WINDOW_ONE_HOUR_BEFORE => '1 Hour Before',
            default => ucfirst(str_replace('_', ' ', $window)),
        };
    }

    public function getReminderWindowMessage(string $window): string
    {
        return match ($window) {
            self::REMINDER_WINDOW_DAY_BEFORE => 'one-day-before reminder',
            self::REMINDER_WINDOW_DAY_OF_6AM => '6 AM reminder',
            self::REMINDER_WINDOW_ONE_HOUR_BEFORE => 'one-hour-before reminder',
            default => $this->getReminderWindowLabel($window) . ' reminder',
        };
    }

    public function isOccurrenceDate(Carbon $candidate): bool
    {
        $candidate = $candidate->copy()->startOfDay();
        $firstDate = $this->getFirstOccurrenceDate();

        if ($candidate->lt($firstDate)) {
            return false;
        }

        if ($this->ends_type === 'on' && $this->ends_on && $candidate->gt(Carbon::parse($this->ends_on)->startOfDay())) {
            return false;
        }

        if (!$this->matchesBaseRule($candidate, $firstDate)) {
            return false;
        }

        if ($this->ends_type === 'after' && $this->ends_after_occurrences) {
            return $this->getOccurrenceIndex($candidate) <= (int) $this->ends_after_occurrences;
        }

        return true;
    }

    protected function matchesBaseRule(Carbon $candidate, Carbon $firstDate): bool
    {
        $interval = max((int) $this->repeat_interval, 1);

        if ($this->repeat_unit === 'day') {
            return $firstDate->diffInDays($candidate) % $interval === 0;
        }

        if ($this->repeat_unit === 'week') {
            $selectedDays = $this->repeat_days_list;

            if (empty($selectedDays)) {
                $selectedDays = [strtolower($firstDate->format('l'))];
            }

            if (!in_array(strtolower($candidate->format('l')), $selectedDays, true)) {
                return false;
            }

            $anchorWeek = $firstDate->copy()->startOfWeek(Carbon::SUNDAY);
            $candidateWeek = $candidate->copy()->startOfWeek(Carbon::SUNDAY);
            $weeks = (int) floor($anchorWeek->diffInDays($candidateWeek) / 7);

            return $weeks % $interval === 0;
        }

        if ($this->repeat_unit === 'month') {
            return $candidate->day === $firstDate->day && $firstDate->diffInMonths($candidate) % $interval === 0;
        }

        return $candidate->month === $firstDate->month
            && $candidate->day === $firstDate->day
            && $firstDate->diffInYears($candidate) % $interval === 0;
    }

    protected function getOccurrenceIndex(Carbon $candidate): int
    {
        $firstDate = $this->getFirstOccurrenceDate();
        $cursor = $firstDate->copy();
        $count = 0;
        $limit = $candidate->copy();

        while ($cursor->lte($limit)) {
            if ($this->matchesBaseRule($cursor, $firstDate)) {
                $count++;

                if ($cursor->isSameDay($candidate)) {
                    return $count;
                }
            }

            $cursor->addDay();
        }

        return $count;
    }

    protected function getFirstOccurrenceDate(): Carbon
    {
        $taskDate = Carbon::parse($this->task_date)->startOfDay();
        $startsOn = Carbon::parse($this->starts_on ?: $this->task_date)->startOfDay();

        return $taskDate->greaterThan($startsOn) ? $taskDate : $startsOn;
    }
}
