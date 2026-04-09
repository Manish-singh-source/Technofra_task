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

    public function apiTodoCollection(Request $request): JsonResponse
    {
        $todos = Todo::ownedBy(Auth::id())
            ->when($request->filled('is_completed'), function ($query) use ($request) {
                $query->where('is_completed', $request->boolean('is_completed'));
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $todos->map(fn (Todo $todo) => $this->formatTodoResource($todo)),
        ]);
    }

    public function apiTodoOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'repeat_units' => ['day', 'week', 'month', 'year'],
                'repeat_days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'ends_types' => ['never', 'on', 'after'],
            ],
        ]);
    }

    public function apiTodoDetail(Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);

        return response()->json([
            'success' => true,
            'data' => $this->formatTodoResource($todo),
        ]);
    }

    public function apiCreateTodo(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $data['user_id'] = Auth::id();

        $todo = Todo::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Todo created successfully.',
            'data' => $this->formatTodoResource($todo),
        ], 201);
    }

    public function apiUpdateTodo(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);

        $todo->update($this->validatedData($request));

        return response()->json([
            'success' => true,
            'message' => 'Todo updated successfully.',
            'data' => $this->formatTodoResource($todo->fresh()),
        ]);
    }

    public function apiDeleteTodo(Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);
        $todo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todo deleted successfully.',
        ]);
    }

    public function apiToggleTodoStatus(Request $request, Todo $todo): JsonResponse
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
            'success' => true,
            'message' => 'Todo status updated successfully.',
            'data' => $this->formatTodoResource($todo->fresh()),
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

    protected function formatTodoResource(Todo $todo): array
    {
        return [
            'id' => $todo->id,
            'user_id' => $todo->user_id,
            'title' => $todo->title,
            'description' => $todo->description,
            'task_date' => optional($todo->task_date)?->toDateString(),
            'task_time' => $todo->task_time,
            'repeat_interval' => $todo->repeat_interval,
            'repeat_unit' => $todo->repeat_unit,
            'repeat_days' => $todo->repeat_days ?? [],
            'repeat_days_list' => $todo->repeat_days_list,
            'reminder_time' => $todo->reminder_time,
            'starts_on' => optional($todo->starts_on)?->toDateString(),
            'ends_type' => $todo->ends_type,
            'ends_on' => optional($todo->ends_on)?->toDateString(),
            'ends_after_occurrences' => $todo->ends_after_occurrences,
            'is_completed' => (bool) $todo->is_completed,
            'completed_at' => optional($todo->completed_at)?->toISOString(),
            'display_schedule' => $todo->display_schedule,
            'created_at' => optional($todo->created_at)?->toISOString(),
            'updated_at' => optional($todo->updated_at)?->toISOString(),
            'links' => [
                'web' => [
                    'list' => route('to-do-list'),
                ],
                'api' => [
                    'show' => url('/api/v1/todos/' . $todo->id),
                    'update' => url('/api/v1/todos/' . $todo->id),
                    'delete' => url('/api/v1/todos/' . $todo->id),
                    'toggle_status' => url('/api/v1/todos/' . $todo->id . '/status'),
                ],
            ],
        ];
    }
}
