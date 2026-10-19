<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogWebLeadFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'followup_date' => ['required', 'date', 'before_or_equal:now'],
            'followup_type' => ['required', Rule::in(['call', 'whatsapp', 'email', 'meeting', 'demo', 'video_call', 'site_visit', 'proposal_sent', 'quotation_sent'])],
            'outcome' => ['required', Rule::in(['interested', 'not_interested', 'callback_later', 'converted', 'no_response', 'meeting_scheduled', 'proposal_requested', 'negotiation', 'lost'])],
            'discussion_notes' => ['required', 'string', 'max:5000'],
            'next_followup_date' => ['nullable', 'date', 'after:now'],
            'lead_status_after_followup' => ['nullable', Rule::in(collect(config('lead_statuses', []))->pluck('slug')->all())],
            'lost_reason' => ['required_if:lead_status_after_followup,lost', 'nullable', 'string', 'max:1000'],
            'won_value' => ['nullable', 'numeric', 'min:0'],
            'complete_scheduled_followup' => ['nullable', 'boolean'],
        ];
    }
}
