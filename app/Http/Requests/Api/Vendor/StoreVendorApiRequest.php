<?php

namespace App\Http\Requests\Api\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:vendors,name',
            'email' => 'nullable|email|unique:vendors,email',
            'phone' => 'nullable|numeric|digits_between:10,15',
            'address' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,inactive,0,1',
        ];
    }
}

