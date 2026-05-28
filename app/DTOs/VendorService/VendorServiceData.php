<?php

namespace App\DTOs\VendorService;

class VendorServiceData
{
    public function __construct(
        public readonly int $vendorId,
        public readonly string $serviceName,
        public readonly ?string $serviceDetails,
        public readonly ?string $remarkText,
        public readonly ?string $remarkColor,
        public readonly string $planType,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?string $billingDate,
        public readonly string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vendorId: (int) ($data['vendor_id'] ?? 0),
            serviceName: (string) ($data['service_name'] ?? ''),
            serviceDetails: isset($data['service_details']) ? (string) $data['service_details'] : null,
            remarkText: isset($data['remark_text']) ? (string) $data['remark_text'] : null,
            remarkColor: isset($data['remark_color']) ? (string) $data['remark_color'] : null,
            planType: (string) ($data['plan_type'] ?? ''),
            startDate: (string) ($data['start_date'] ?? ''),
            endDate: (string) ($data['end_date'] ?? ''),
            billingDate: isset($data['billing_date']) && $data['billing_date'] !== '' ? (string) $data['billing_date'] : null,
            status: (string) ($data['status'] ?? 'pending'),
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'vendor_id' => $this->vendorId,
            'service_name' => $this->serviceName,
            'service_details' => $this->serviceDetails,
            'remark_text' => $this->remarkText,
            'remark_color' => $this->remarkColor,
            'plan_type' => $this->planType,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'billing_date' => $this->billingDate,
            'status' => $this->status,
        ];
    }
}

