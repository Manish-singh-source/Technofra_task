<?php

namespace App\Http\Requests\Web\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendorId = (int) $this->route('vendor');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('vendors', 'name')->ignore($vendorId)],
            'email' => ['nullable', 'email', Rule::unique('vendors', 'email')->ignore($vendorId)],
            'phone' => 'nullable|numeric|digits_between:10,15',
            'address' => 'nullable|string|max:1000',
        ];
    }
}

