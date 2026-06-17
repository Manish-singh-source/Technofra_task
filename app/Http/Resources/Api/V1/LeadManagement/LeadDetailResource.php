<?php

namespace App\Http\Resources\Api\V1\LeadManagement;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadDetailResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) ($this->name ?? ''),
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'source' => $this->source,
            'lead_value' => $this->lead_value,
            'status' => (string) ($this->status ?? 'new'),
            'previous_status' => $this->previous_status,
            'converted_at' => optional($this->converted_at)?->toISOString(),
            'next_followup_at' => optional($this->next_followup_at)?->toISOString(),
            'assigned' => $this->assigned ?? [],
            'won_value' => $this->won_value,
            'lost_reason' => $this->lost_reason,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
