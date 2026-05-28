<?php

namespace App\Services\LeadManagement;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LeadClientConversionService
{
    public function ensureClientFromLead(Lead $lead): User
    {
        $email = $this->resolveEmail($lead);
        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            if (strtolower((string) $existing->getRawOriginal('role')) !== 'client') {
                $existing->role = 'client';
            }
            if (empty($existing->phone) && ! empty($lead->phone)) {
                $existing->phone = (string) $lead->phone;
            }
            if (empty($existing->status)) {
                $existing->status = 'active';
            }
            $existing->save();

            return $existing;
        }

        [$firstName, $lastName] = $this->splitName((string) ($lead->name ?? 'Client'));

        return User::query()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $lead->phone ? (string) $lead->phone : null,
            'password' => Hash::make(Str::random(24)),
            'status' => 'active',
            'role' => 'client',
        ]);
    }

    private function resolveEmail(Lead $lead): string
    {
        $email = strtolower(trim((string) ($lead->email ?? '')));
        if ($email !== '') {
            return $email;
        }

        return sprintf('client+lead-%d@local.crm', (int) $lead->id);
    }

    private function splitName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['Client', 'User'];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [
            $parts[0] ?? 'Client',
            $parts[1] ?? 'User',
        ];
    }
}

