<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::ownedBy(Auth::id())->latest()->get();

        return view('to-do-list', compact('todos'));
    }

    public function list(): JsonResponse
    {
        $todos = Todo::ownedBy(Auth::id())->latest()->get();

        return response()->json([
            'data' => $todos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $data['user_id'] = Auth::id();

        $todo = Todo::create($data);

        return response()->json([
            'message' => 'Todo created successfully.',
            'data' => $todo,
        ], 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);

        $todo->update($this->validatedData($request));

        return response()->json([
            'message' => 'Todo updated successfully.',
            'data' => $todo->fresh(),
        ]);
    }

    public function destroy(Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);
        $todo->delete();

        return response()->json([
            'message' => 'Todo deleted successfully.',
        ]);
    }

    public function toggleStatus(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);

        $request->validate([
            'is_completed' => 'required|boolean',
        ]);

        $completed = (bool) $request->boolean('is_completed');

        $todo->update([
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
        ]);

        return response()->json([
            'message' => 'Todo status updated successfully.',
            'data' => $todo->fresh(),
        ]);
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_date' => 'required|date',
            'task_time' => 'nullable|date_format:H:i',
            'repeat_interval' => 'required|integer|min:1|max:365',
            'repeat_unit' => 'required|in:day,week,month,year',
            'repeat_days' => 'nullable|array',
            'repeat_days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'reminder_time' => 'nullable|date_format:H:i',
            'starts_on' => 'required|date',
            'ends_type' => 'required|in:never,on,after',
            'ends_on' => 'nullable|date|required_if:ends_type,on',
            'ends_after_occurrences' => 'nullable|integer|min:1|required_if:ends_type,after',
        ]);

        if ($data['repeat_unit'] !== 'week') {
            $data['repeat_days'] = null;
        }

        if ($data['ends_type'] !== 'on') {
            $data['ends_on'] = null;
        }

        if ($data['ends_type'] !== 'after') {
            $data['ends_after_occurrences'] = null;
        }

        return $data;
    }

    protected function authorizeTodo(Todo $todo): void
    {
        abort_unless($todo->user_id === Auth::id(), 403);
    }
}
