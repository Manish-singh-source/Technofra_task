<?php

namespace App\Imports;

use App\Models\ClientBusinessDetail;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class ClientsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $user = User::create([
                'first_name' => trim((string) $row['first_name']),
                'last_name' => trim((string) $row['last_name']),
                'email' => trim((string) $row['email']),
                'phone' => trim((string) $row['phone']),
                'role' => 'client',
                'status' => in_array($row['status'] ?? 'active', [1, '1', true, 'true', 'active'], true) ? 'active' : 'inactive',
                'password' => Hash::make((string) ($row['phone'] ?? 'client@123')),
            ]);

            UserAddress::create([
                'user_id' => $user->id,
                'address_line_1' => trim((string) $row['address_line_1']),
                'address_line_2' => filled($row['address_line_2'] ?? null) ? trim((string) $row['address_line_2']) : null,
                'city' => trim((string) $row['city']),
                'state' => trim((string) $row['state']),
                'country' => trim((string) $row['country']),
                'pincode' => trim((string) $row['pincode']),
            ]);

            ClientBusinessDetail::create([
                'user_id' => $user->id,
                'client_type' => trim((string) $row['client_type']),
                'industry' => filled($row['industry'] ?? null) ? trim((string) $row['industry']) : null,
                'website' => filled($row['website'] ?? null) ? trim((string) $row['website']) : null,
            ]);

            return $user;
        });
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'client_type' => 'required|in:Individual,Company,Organization',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'nullable|in:active,inactive,1,0',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'address_line_1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'country.required' => 'Country is required.',
            'pincode.required' => 'Pincode is required.',
            'client_type.required' => 'Client type is required.',
        ];
    }
}
