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
            'full_name' => $this->full_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'address' => $this->whenLoaded('address'),
            'business_detail' => $this->whenLoaded('businessDetail'),
            'companies' => $this->whenLoaded('companies'),
            'tasks_count' => $this->when(isset($this->tasks_count), $this->tasks_count),
            'projects_count' => $this->when(isset($this->projects_count), $this->projects_count),
            'is_deleted' => method_exists($this, 'trashed') ? $this->trashed() : false,
            'deleted_at' => optional($this->deleted_at)?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values(), []),
            ],
            'links' => [
                'web' => [
                    'view' => route('client.view', $this->id),
                    'update' => route('client.update', $this->id),
                    'delete' => route('client.delete', $this->id),
                    'restore' => null,
                    'force_delete' => null,
                ],
                'api' => [
                    'show' => url('/api/v1/clients/' . $this->id),
                    'update' => url('/api/v1/clients/' . $this->id),
                    'delete' => url('/api/v1/clients/' . $this->id),
                ],
            ],
        ];
    }
}
