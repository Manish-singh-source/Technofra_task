<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getTeamOptions(): array
    {
        if (!Schema::hasTable('teams')) {
            return [];
        }

        $teams = self::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->map(function ($team) {
                return trim((string) $team);
            })
            ->filter(function ($team) {
                return $team !== '';
            })
            ->unique()
            ->values()
            ->all();

        return $teams;
    }

    public static function getTeamCards(): array
    {
        if (!Schema::hasTable('teams')) {
            return [];
        }

        return self::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'description', 'icon_path'])
            ->map(function ($team) {
                return [
                    'name' => trim((string) $team->name),
                    'description' => (string) ($team->description ?? ''),
                    'icon_path' => (string) ($team->icon_path ?? ''),
                ];
            })
            ->filter(function ($team) {
                return $team['name'] !== '';
            })
            ->values()
            ->all();
    }
}
