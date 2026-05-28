<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorRenewalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'vendor_id' => (int) $this->vendor_id,
            'vendor_name' => $this->vendor?->name,
            'vendor_email' => $this->vendor?->email,
            'vendor_phone' => $this->vendor?->phone,
            'service_name' => (string) $this->service_name,
            'service_details' => $this->service_details,
            'remark_text' => $this->remark_text,
            'remark_color' => $this->remark_color,
            'plan_type' => $this->plan_type,
            'start_date' => optional($this->start_date)->toDateString(),
            'end_date' => optional($this->end_date)->toDateString(),
            'billing_date' => optional($this->billing_date)->toDateString(),
            'status' => $this->effective_status ?? $this->status,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}

