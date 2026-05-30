<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'profile_image' => $this->profile_image,
            'profile_image_url' => $this->profile_image ? asset($this->profile_image) : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'address' => $this->whenLoaded('address'),
            'business_detail' => $this->whenLoaded('businessDetail'),
            'companies' => $this->whenLoaded('companies'),
            'tasks_count' => $this->when(isset($this->tasks_count), $this->tasks_count),
            'projects_count' => $this->when(isset($this->projects_count), $this->projects_count),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
