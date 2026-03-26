<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getDepartmentOptions(): array
    {
        if (!Schema::hasTable('departments')) {
            return [];
        }

        return self::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->map(function ($department) {
                return trim((string) $department);
            })
            ->filter(function ($department) {
                return $department !== '';
            })
            ->unique()
            ->values()
            ->all();
    }
}
