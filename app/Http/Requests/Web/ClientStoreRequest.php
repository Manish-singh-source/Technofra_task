<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ClientStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'nullable|string|min:3|max:255',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')],
            'phone' => 'nullable|string|min:10|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'companies' => 'nullable|array',
            'companies.*.client_type' => 'nullable|in:Individual,Company,Organization',
            'companies.*.company_name' => 'nullable|string|max:255',
            'companies.*.industry' => 'nullable|string|max:255',
            'companies.*.website' => 'nullable|url|max:255',
            'status' => 'nullable|in:active,inactive',
            'send_invite_mail' => 'nullable|boolean',
            'password' => 'required|string|min:8',
            'client_type' => 'nullable|in:Individual,Company,Organization',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Password is required.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('send_invite_mail')) {
                return;
            }

            if (! $this->filled('email')) {
                $validator->errors()->add('email', 'Email is required to send the invitation.');
            }
        });
    }
}
