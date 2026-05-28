<?php

namespace App\Http\Requests\Api\VendorRenewal;

use Illuminate\Foundation\Http\FormRequest;

class IndexVendorRenewalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tab' => 'nullable|in:all,upcoming,active,inactive,pending,expired',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ];
    }
}

