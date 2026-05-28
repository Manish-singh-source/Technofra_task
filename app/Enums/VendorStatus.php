<?php

namespace App\Enums;

enum VendorStatus: string
{
    case ACTIVE = '1';
    case INACTIVE = '0';

    public static function fromMixed(string|int|null $value): self
    {
        $normalized = strtolower((string) $value);

        return match ($normalized) {
            '1', 'active', 'true' => self::ACTIVE,
            default => self::INACTIVE,
        };
    }

    public function label(): string
    {
        return $this === self::ACTIVE ? 'active' : 'inactive';
    }
}

