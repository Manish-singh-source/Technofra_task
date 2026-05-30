<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\VendorStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'full_name' => (string) $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => VendorStatus::fromMixed($this->status)->label(),
            'status_value' => (string) $this->status,
            'is_deleted' => false,
            'deleted_at' => null,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
            'links' => [
                'web' => [
                    'view' => route('vendors.show', $this->id),
                    'update' => route('vendors.edit', $this->id),
                    'delete' => route('vendors.destroy', $this->id),
                    'restore' => null,
                    'force_delete' => null,
                ],
                'api' => [
                    'show' => url('/api/v1/vendors/' . $this->id),
                    'update' => url('/api/v1/vendors/' . $this->id),
                    'delete' => url('/api/v1/vendors/' . $this->id),
                ],
            ],
        ];
    }
}
