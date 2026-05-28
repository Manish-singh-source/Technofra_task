<?php

namespace App\Http\Requests\LeadManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadStatusRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $status = (string) $this->input('status', '');
        $conversionValue = $this->input('conversion_value');

        if ($status === 'won') {
            $status = 'converted';
        }

        $payload = ['status' => $status];
        if ($conversionValue !== null && $conversionValue !== '') {
            $payload['won_value'] = $conversionValue;
        }

        $this->merge($payload);
    }

    public function authorize(): bool
    {
        return (bool) auth()->user()?->can('edit_leads');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(collect(config('lead_statuses', []))->pluck('slug')->all())],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'lost_reason' => ['nullable', 'string', 'max:1000'],
            'won_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
