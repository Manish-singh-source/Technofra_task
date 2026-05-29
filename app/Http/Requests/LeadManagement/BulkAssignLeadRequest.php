<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAssignLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'assigned_user_ids' => ['required', 'array', 'min:1'],
            'assigned_user_ids.*' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'staff')),
            ],
            'selected_leads' => ['required', 'array', 'min:1'],
            'selected_leads.*.source' => ['required', 'string'],
            'selected_leads.*.id' => ['required', 'integer'],
        ];
    }
}

