<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set($key, $value, $type = 'text')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    /**
     * Get all settings as key-value pairs.
     */
    public static function getAllSettings()
    {
        $settings = self::all();
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
        return self::where('group', $group)->get();
    }

}

