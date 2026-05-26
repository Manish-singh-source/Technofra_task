<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddLeadReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'remind_at' => ['required', 'date'],
            'reminder_type' => ['required', Rule::in(['email', 'whatsapp', 'dashboard'])],
        ];
    }
}
