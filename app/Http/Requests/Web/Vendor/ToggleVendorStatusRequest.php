<?php

namespace App\Http\Requests\Web\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class ToggleVendorStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:vendors,id',
            'status' => 'required|string|in:0,1,active,inactive',
        ];
    }
}

