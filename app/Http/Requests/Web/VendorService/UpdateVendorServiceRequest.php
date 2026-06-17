<?php

namespace App\Http\Requests\Web\VendorService;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'remark_text' => 'nullable|string|max:100',
            'remark_color' => 'nullable|in:yellow,red,green,blue,gray',
            'plan_type' => 'required|in:monthly,yearly,quarterly,half_year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'billing_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,expired,pending',
        ];
    }
}
