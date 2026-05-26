<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'followup_date' => ['required', 'date'],
            'followup_type' => ['required', Rule::in(['call','whatsapp','email','meeting','demo','video_call','site_visit','proposal_sent','quotation_sent'])],
            'outcome' => ['nullable', Rule::in(['interested','not_interested','callback_later','converted','no_response','meeting_scheduled','proposal_requested','negotiation','lost'])],
            'discussion_notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
            'lead_status_after_followup' => ['nullable', 'string', 'max:50'],
            'create_reminder' => ['nullable', 'boolean'],
            'reminder_type' => ['nullable', Rule::in(['email', 'whatsapp', 'dashboard'])],
        ];
    }
}
