<?php

namespace App\Http\Requests\Web\VendorService;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => 'required|exists:vendors,id',
            'services' => 'required|array|min:1',
            'services.*.service_name' => 'required|string|max:255',
            'services.*.service_details' => 'nullable|string',
            'services.*.remark_text' => 'nullable|string|max:100',
            'services.*.remark_color' => 'nullable|in:yellow,red,green,blue,gray',
            'services.*.plan_type' => 'required|in:monthly,yearly,quarterly',
            'services.*.start_date' => 'required|date',
            'services.*.end_date' => 'required|date|after_or_equal:services.*.start_date',
            'services.*.billing_date' => 'nullable|date',
            'services.*.status' => 'required|in:active,inactive,expired,pending',
        ];
    }
}

