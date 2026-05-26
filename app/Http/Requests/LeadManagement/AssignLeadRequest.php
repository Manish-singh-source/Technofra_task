<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'assigned_user_ids' => ['required', 'array', 'min:1'],
            'assigned_user_ids.*' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'staff'))],
            'assignment_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
