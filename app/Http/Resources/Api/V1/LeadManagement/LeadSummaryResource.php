<?php

namespace App\Http\Resources\Api\V1\LeadManagement;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadSummaryResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray($request)
    {
        $row = (array) $this->resource;

        return [
            'source_type' => (string) ($row['source_type'] ?? ''),
            'source_id' => (int) ($row['source_id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'email' => $row['email'] ?? null,
            'number' => $row['number'] ?? null,
            'company' => $row['company'] ?? null,
            'source' => $row['source'] ?? null,
            'lead_value' => $row['lead_value'] ?? null,
            'status' => (string) ($row['status'] ?? 'new'),
            'assigned_to' => $row['assigned_to'] ?? null,
            'created_at' => $row['created_at'] ?? null,
        ];
    }
}
