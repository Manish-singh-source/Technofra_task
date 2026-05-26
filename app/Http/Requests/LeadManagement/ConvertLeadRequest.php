<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer'],
            'conversion_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
