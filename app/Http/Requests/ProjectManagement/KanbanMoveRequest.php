<?php

namespace App\Http\Requests\ProjectManagement;

use Illuminate\Foundation\Http\FormRequest;

class KanbanMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => 'required|integer|exists:tasks,id',
            'to_column' => 'required|string|in:backlog,todo,in_progress,review,done,not_started,pending,completed',
        ];
    }
}

