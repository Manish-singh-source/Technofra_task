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
                    'icon_path' => self::normalizeIconPath((string) ($team->icon_path ?? '')),
                ];
            })
            ->filter(function ($team) {
                return $team['name'] !== '';
            })
            ->values()
            ->all();
    }

    private static function normalizeIconPath(string $iconPath): string
    {
        $normalized = trim(str_replace('\\', '/', $iconPath));
        $normalized = ltrim($normalized, '/');

        if ($normalized === '') {
            return '';
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'uploads/team-icons/')) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'team-icons/')) {
            $legacyStoragePath = storage_path('app/public/' . $normalized);
            $destinationPath = public_path('uploads/team-icons');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $targetRelativePath = 'uploads/team-icons/' . basename($normalized);
            $targetAbsolutePath = public_path($targetRelativePath);
            if (is_file($legacyStoragePath) && !is_file($targetAbsolutePath)) {
                copy($legacyStoragePath, $targetAbsolutePath);
            }

            if (is_file($targetAbsolutePath)) {
                return $targetRelativePath;
            }
        }

        return $normalized;
    }
}

