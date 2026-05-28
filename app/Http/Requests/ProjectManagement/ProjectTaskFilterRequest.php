<?php

namespace App\Http\Requests\ProjectManagement;

use Illuminate\Foundation\Http\FormRequest;

class ProjectTaskFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:not_started,pending,in_progress,completed,on_hold,cancelled',
            'priority' => 'nullable|string|in:low,medium,high',
            'q' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:200',
        ];
    }
}

