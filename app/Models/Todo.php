<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'task_date',
        'task_time',
        'repeat_interval',
        'repeat_unit',
        'repeat_days',
        'reminder_time',
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
        'repeat_days' => 'array',
        'is_completed' => 'boolean',
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
        $time = $this->task_time ? Carbon::createFromFormat('H:i:s', $this->task_time)->format('h:i A') : 'Any time';
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

    public function getOccurrenceInReminderWindow(Carbon $now, int $graceMinutes = 5): ?Carbon
    {
        foreach ([$now->copy()->subDay()->startOfDay(), $now->copy()->startOfDay()] as $date) {
            if (!$this->isOccurrenceDate($date)) {
                continue;
            }

            $reminderAt = $this->getReminderDateTimeForOccurrence($date);

            if ($now->greaterThanOrEqualTo($reminderAt) && $now->lessThanOrEqualTo($reminderAt->copy()->addMinutes($graceMinutes))) {
                return $date->copy();
            }
        }

        return null;
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

