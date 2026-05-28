<?php

namespace App\Services\ProjectManagement;

use App\Models\Task;
use App\Models\TaskTimeLog;
use Carbon\Carbon;

class TimeTrackingService
{
    public function startTimer(Task $task, int $userId, ?string $note = null): TaskTimeLog
    {
        $open = TaskTimeLog::query()
            ->where('task_id', $task->id)
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->first();

        if ($open) {
            return $open;
        }

        return TaskTimeLog::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'started_at' => now(),
            'ended_at' => null,
            'duration_minutes' => null,
            'note' => $note,
            'log_type' => 'timer',
        ]);
    }

    public function stopTimer(Task $task, int $userId): ?TaskTimeLog
    {
        $open = TaskTimeLog::query()
            ->where('task_id', $task->id)
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $open) {
            return null;
        }

        $endedAt = now();
        $durationMinutes = max(0, (float) $open->started_at?->diffInMinutes($endedAt));

        $open->update([
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
        ]);

        return $open->fresh();
    }

    public function manualLog(Task $task, int $userId, float $durationMinutes, ?string $note = null, ?string $startedAt = null, ?string $endedAt = null): TaskTimeLog
    {
        return TaskTimeLog::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'started_at' => $startedAt ? Carbon::parse($startedAt) : null,
            'ended_at' => $endedAt ? Carbon::parse($endedAt) : null,
            'duration_minutes' => $durationMinutes,
            'note' => $note,
            'log_type' => 'manual',
        ]);
    }

    public function summarizeForTask(Task $task): array
    {
        $logs = TaskTimeLog::query()->where('task_id', $task->id)->get();

        $total = (float) $logs->sum(fn (TaskTimeLog $log) => (float) ($log->duration_minutes ?? 0));
        $timer = (float) $logs->where('log_type', 'timer')->sum('duration_minutes');
        $manual = (float) $logs->where('log_type', 'manual')->sum('duration_minutes');

        return [
            'task_id' => $task->id,
            'total_minutes' => round($total, 2),
            'total_hours' => round($total / 60, 2),
            'timer_minutes' => round($timer, 2),
            'manual_minutes' => round($manual, 2),
        ];
    }
}
