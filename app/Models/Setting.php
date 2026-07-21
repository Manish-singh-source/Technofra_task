<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
        'type',
        'group',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a global setting value by key.
     */
    public static function get($key, $default = null)
    {
        $setting = self::query()
            ->whereNull('user_id')
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a global setting value.
     */
    public static function set($key, $value, $type = 'text')
    {
        return self::updateOrCreate(
            ['user_id' => null, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public static function getForUser(int $userId, string $key, $default = null)
    {
        $setting = self::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function setForUser(int $userId, string $key, $value, string $type = 'text', string $group = 'meta')
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
    }

    public static function getAllForUser(int $userId, ?string $group = null): array
    {
        $settings = self::query()
            ->where('user_id', $userId)
            ->when($group, fn ($query) => $query->where('group', $group))
            ->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Get all global settings as key-value pairs.
     */
    public static function getAllSettings()
    {
        $settings = self::query()->whereNull('user_id')->get();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }
        return $result;
    }

    public static function resolveGeneralAssetUrl(?string $path): ?string
    {
        $normalized = self::normalizeGeneralAssetPath((string) $path);

        if ($normalized === '') {
            return null;
        }

        $publicAsset = public_path($normalized);
        if (is_file($publicAsset)) {
            return asset($normalized);
        }

        return null;
    }

    public static function normalizeGeneralAssetPath(string $path): string
    {
        $normalized = trim(str_replace('\\', '/', $path));
        $normalized = ltrim($normalized, '/');

        if ($normalized === '') {
            return '';
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'uploads/settings/')) {
            return $normalized;
        }

        $fileName = basename($normalized);
        $targetDirectory = public_path('uploads/settings');
        $targetRelativePath = 'uploads/settings/' . $fileName;
        $targetAbsolutePath = public_path($targetRelativePath);

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $legacyCandidates = [];
        if ($fileName !== '') {
            $legacyCandidates[] = storage_path('app/public/settings/' . $fileName);
            $legacyCandidates[] = public_path('settings/' . $fileName);
        }

        if (str_starts_with($normalized, 'settings/')) {
            $legacyCandidates[] = storage_path('app/public/' . $normalized);
            $legacyCandidates[] = public_path($normalized);
        }

        foreach ($legacyCandidates as $candidate) {
            if (is_file($candidate) && !is_file($targetAbsolutePath)) {
                copy($candidate, $targetAbsolutePath);
            }
        }

        if (is_file($targetAbsolutePath)) {
            return $targetRelativePath;
        }

        if (is_file(public_path($normalized))) {
            return $normalized;
        }

        return '';
    }

    /**
     * Get settings by group.
     */
    public static function getByGroup($group)
    {
        return self::whereNull('user_id')->where('group', $group)->get();
    }
}