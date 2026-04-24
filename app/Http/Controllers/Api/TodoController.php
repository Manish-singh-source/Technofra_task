<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TodoController extends Controller
{
    //
    public function index(Request $request): JsonResponse
    {
        $todos = Todo::ownedBy(Auth::id())
            ->when($request->filled('is_completed'), function ($query) use ($request) {
                $query->where('is_completed', $request->boolean('is_completed'));
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $todos->map(fn(Todo $todo) => $this->formatTodoResource($todo))->values(),
        ]);
    }

    public function show(Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);

        return response()->json([
            'success' => true,
            'data' => $this->formatTodoResource($todo),
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $data['user_id'] = Auth::id();
        $data['attachments'] = $this->storeUploadedAttachments($request);

        $todo = Todo::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Todo created successfully.',
            'data' => $this->formatTodoResource($todo),
        ], 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);

        $data = $this->validatedData($request);
        $data['attachments'] = $this->mergeTodoAttachments($todo, $request);

        $todo->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Todo updated successfully.',
            'data' => $this->formatTodoResource($todo->fresh()),
        ]);
    }


    public function delete(Todo $todo): JsonResponse
    {
        $this->authorizeTodo($todo);
        $this->deleteTodoAttachments($todo);
        $todo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todo deleted successfully.',
        ]);
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'integer|min:0',
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

        if (empty($data['remove_attachments'])) {
            unset($data['remove_attachments']);
        }

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

    public function formatTodoResource(Todo $todo): array
    {
        $attachments = collect($todo->attachments ?? [])
            ->filter(fn($attachment) => is_array($attachment) && ! empty($attachment['path']))
            ->values()
            ->map(function (array $attachment) {
                $path = ltrim((string) ($attachment['path'] ?? ''), '/');

                return [
                    'name' => $attachment['name'] ?? basename($path),
                    'path' => $path,
                    'url' => url($path),
                    'size' => $attachment['size'] ?? null,
                    'mime_type' => $attachment['mime_type'] ?? null,
                ];
            })
            ->values();

        return [
            'id' => $todo->id,
            'user_id' => $todo->user_id,
            'title' => $todo->title,
            'description' => $todo->description,
            'attachments' => $attachments,
            'attachments_count' => $attachments->count(),
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
                    'update' => url('/api/v1/todos/update-todo/' . $todo->id),
                    'delete' => url('/api/v1/todos/delete-todo/' . $todo->id),
                    'toggle_status' => url('/api/v1/todos/toggle-todo-status/' . $todo->id),
                ],
            ],
        ];
    }

    protected function authorizeTodo(Todo $todo): void
    {
        abort_unless($todo->user_id === Auth::id(), 403);
    }

    protected function deleteTodoAttachments(Todo $todo): void
    {
        foreach ($todo->attachments ?? [] as $attachment) {
            if (! is_array($attachment) || empty($attachment['path'])) {
                continue;
            }

            $absolutePath = public_path($attachment['path']);
            if (File::exists($absolutePath)) {
                File::delete($absolutePath);
            }
        }
    }


    protected function mergeTodoAttachments(Todo $todo, Request $request): array
    {
        $existingAttachments = collect($todo->attachments ?? [])
            ->filter(fn($attachment) => is_array($attachment) && ! empty($attachment['path']))
            ->values()
            ->all();

        $removeIds = $request->input('remove_attachments', []);
        if (! empty($removeIds)) {
            $existingAttachments = collect($existingAttachments)
                ->filter(fn($attachment, $index) => ! in_array($index, $removeIds))
                ->values()
                ->all();
        }

        $newAttachments = $this->storeUploadedAttachments($request);

        return array_values(array_merge($existingAttachments, $newAttachments));
    }

    protected function storeUploadedAttachments(Request $request): array
    {
        $storedAttachments = [];

        if (! $request->hasFile('attachments')) {
            return $storedAttachments;
        }

        $directory = public_path('uploads/todo_attachments');
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $fileName = time() . '_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);

            $storedAttachments[] = [
                'name' => $file->getClientOriginalName(),
                'path' => 'uploads/todo_attachments/' . $fileName,
                // 'size' => $file->getSize(),
                // 'mime_type' => $file->getMimeType(),
            ];
        }

        return $storedAttachments;
    }
}
