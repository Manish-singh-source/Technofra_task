<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;

class EscalateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'escalated_to' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
