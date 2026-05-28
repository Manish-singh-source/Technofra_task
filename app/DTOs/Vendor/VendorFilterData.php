<?php

namespace App\DTOs\Vendor;

class VendorFilterData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly int $perPage = 10,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = isset($data['search']) ? trim((string) $data['search']) : null;
        $perPage = (int) ($data['per_page'] ?? 10);
        $perPage = max(1, min(100, $perPage));

        return new self(
            search: $search !== '' ? $search : null,
            perPage: $perPage,
        );
    }
}

