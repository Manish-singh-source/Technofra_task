<?php

namespace App\DTOs\LeadManagement;

class StatusUpdateData
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $remarks = null,
        public readonly ?string $lostReason = null,
        public readonly ?float $wonValue = null,
        public readonly ?int $actorId = null,
    ) {
    }
}

