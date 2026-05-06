<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoogleLeadListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'lead_stage' => $this->lead_stage,
            'is_test' => (bool) $this->is_test,
            'submitted_at' => optional($this->lead_submit_time)?->format('d M Y, h:i A'),
        ];
    }
}
