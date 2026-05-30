<?php

namespace App\DTOs\VendorService;

class VendorServiceFilterData
{
    /**
     * @param array<int, string> $availableTabs
     */
    public function __construct(
        public readonly ?string $fromDate,
        public readonly ?string $toDate,
        public readonly string $tab,
        public readonly int $perPage = 10,
        public readonly array $availableTabs = ['all', 'upcoming', 'active', 'inactive', 'pending', 'expired'],
    ) {}

    public static function fromArray(array $data): self
    {
        $tab = (string) ($data['tab'] ?? 'all');
        $availableTabs = ['all', 'upcoming', 'active', 'inactive', 'pending', 'expired'];
        if (! in_array($tab, $availableTabs, true)) {
            $tab = 'all';
        }

        return new self(
            fromDate: isset($data['from_date']) && $data['from_date'] !== '' ? (string) $data['from_date'] : null,
            toDate: isset($data['to_date']) && $data['to_date'] !== '' ? (string) $data['to_date'] : null,
            tab: $tab,
            perPage: isset($data['per_page']) ? max(1, min(100, (int) $data['per_page'])) : 10,
            availableTabs: $availableTabs,
        );
    }
}
