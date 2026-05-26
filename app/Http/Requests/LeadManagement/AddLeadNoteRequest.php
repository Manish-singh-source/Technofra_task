<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;

class AddLeadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string'],
            'is_private' => ['nullable', 'boolean'],
        ];
    }
}
