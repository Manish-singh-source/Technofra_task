<?php

namespace App\DTOs\Vendor;

use App\Enums\VendorStatus;

class VendorData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly VendorStatus $status,
    ) {}

    public static function fromArray(array $data, ?string $defaultStatus = null): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            address: isset($data['address']) ? (string) $data['address'] : null,
            status: VendorStatus::fromMixed($data['status'] ?? $defaultStatus ?? VendorStatus::ACTIVE->value),
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status->value,
        ];
    }
}

