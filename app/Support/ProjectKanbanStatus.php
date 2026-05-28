<?php

namespace App\Support;

class ProjectKanbanStatus
{
    public const BACKLOG = 'backlog';
    public const TODO = 'todo';
    public const IN_PROGRESS = 'in_progress';
    public const REVIEW = 'review';
    public const DONE = 'done';

    /**
     * @return array<int, array<string, string>>
     */
    public static function columns(): array
    {
        return [
            ['key' => self::BACKLOG, 'label' => 'Backlog'],
            ['key' => self::TODO, 'label' => 'To Do'],
            ['key' => self::IN_PROGRESS, 'label' => 'In Progress'],
            ['key' => self::REVIEW, 'label' => 'Review'],
            ['key' => self::DONE, 'label' => 'Done'],
        ];
    }

    public static function normalize(string $value): string
    {
        $value = trim(strtolower($value));

        return match ($value) {
            'not_started' => self::BACKLOG,
            'pending' => self::TODO,
            'completed' => self::DONE,
            default => in_array($value, [self::BACKLOG, self::TODO, self::IN_PROGRESS, self::REVIEW, self::DONE], true)
                ? $value
                : self::BACKLOG,
        };
    }

    public static function toTaskStatus(string $column): string
    {
        $column = self::normalize($column);

        return match ($column) {
            self::DONE => 'completed',
            self::IN_PROGRESS, self::REVIEW => 'in_progress',
            default => 'pending',
        };
    }
}

