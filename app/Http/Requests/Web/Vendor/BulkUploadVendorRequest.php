<?php

namespace App\Http\Requests\Web\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ];
    }
}

