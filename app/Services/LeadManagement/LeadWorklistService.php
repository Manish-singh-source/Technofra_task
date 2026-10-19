<?php

namespace App\Services\LeadManagement;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeadWorklistService
{
    public const CLOSED = ['converted', 'won', 'lost', 'junk'];

    public function followupState(?Carbon $nextAt, string $status): string
    {
        if (in_array($status, self::CLOSED, true)) {
            return 'closed';
        }
        if (! $nextAt) {
            return 'unscheduled';
        }
        if ($nextAt->lessThan(now())) {
            return 'overdue';
        }

        return $nextAt->isToday() ? 'today' : 'upcoming';
    }

    public function summary(Collection $rows): array
    {
        return [
            'all' => $rows->count(),
            'overdue' => $rows->where('followup_state', 'overdue')->count(),
            'today' => $rows->whereIn('followup_state', ['today', 'overdue'])
                ->filter(fn (array $row) => ! empty($row['next_followup_at']) && $row['next_followup_at']->isToday())->count(),
            'unscheduled' => $rows->where('followup_state', 'unscheduled')->count(),
            'closed' => $rows->where('followup_state', 'closed')->count(),
        ];
    }

    public function matchesWorkFilter(array $row, string $filter): bool
    {
        if ($filter === 'today') {
            return $row['followup_state'] !== 'closed'
                && ! empty($row['next_followup_at']) && $row['next_followup_at']->isToday();
        }

        return $filter === '' || ($row['followup_state'] ?? '') === $filter;
    }
}
