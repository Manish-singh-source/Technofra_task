<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VCardContactRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100', 'middle_name' => 'nullable|string|max:100', 'last_name' => 'required|string|max:100', 'display_name' => 'nullable|string|max:200',
            'primary_email' => 'required|email:rfc,dns|max:255', 'secondary_email' => 'nullable|email:rfc,dns|max:255', 'additional_email' => 'nullable|email:rfc,dns|max:255',
            'mobile_number' => ['required','string','max:30','regex:/^[+0-9() .-]+$/'], 'alternate_mobile' => ['nullable','string','max:30','regex:/^[+0-9() .-]+$/'],
            'office_telephone' => ['nullable','string','max:30','regex:/^[+0-9() .-]+$/'], 'home_telephone' => ['nullable','string','max:30','regex:/^[+0-9() .-]+$/'], 'fax_number' => ['nullable','string','max:30','regex:/^[+0-9() .-]+$/'], 'whatsapp_number' => ['nullable','string','max:30','regex:/^[+0-9() .-]+$/'],
            'company_website' => 'nullable|url|max:255', 'linkedin_url' => 'nullable|url|max:255', 'facebook_url' => 'nullable|url|max:255', 'instagram_url' => 'nullable|url|max:255',
            'designation' => 'nullable|string|max:150', 'department' => 'nullable|string|max:150', 'company_name' => 'nullable|string|max:150', 'address_line_1' => 'nullable|string|max:255', 'address_line_2' => 'nullable|string|max:255', 'landmark' => 'nullable|string|max:150', 'city' => 'nullable|string|max:100', 'state' => 'nullable|string|max:100', 'postal_code' => 'nullable|string|max:30', 'country' => 'nullable|string|max:100', 'notes' => 'nullable|string|max:1000', 'status' => 'nullable|boolean',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
