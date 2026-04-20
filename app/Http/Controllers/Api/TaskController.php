<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function apiFormOptions(Request $request): JsonResponse
    {
        $selectedProjectId = $request->query('project_id');

        return response()->json([
            'success' => true,
            'data' => [
                'projects' => Project::query()
                    ->orderBy('project_name')
                    ->get(['id', 'project_name'])
                    ->map(fn (Project $project) => [
                        'id' => $project->id,
                        'name' => $project->project_name,
                    ])
                    ->values(),
                'staff' => Staff::query()
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get()
                    ->map(fn (Staff $member) => [
                        'id' => $member->id,
                        'name' => trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                        'email' => $member->email,
                        'role' => $member->role,
                    ])
                    ->values(),
                'statuses' => ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'],
                'priorities' => ['low', 'medium', 'high'],
                'selected_project_id' => $selectedProjectId ? (int) $selectedProjectId : null,
            ],
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $tasks = $this->accessibleTasksQuery()
            ->with(['project', 'attachments'])
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn (Builder $query) => $query->where('priority', strtolower((string) $request->input('priority'))))
            ->when($request->filled('project_id'), fn (Builder $query) => $query->where('project_id', $request->input('project_id')))
            ->when($request->filled('assignee_id'), function (Builder $query) use ($request) {
                $assigneeId = $request->input('assignee_id');

                $query->where(function (Builder $builder) use ($assigneeId) {
                    $builder->whereJsonContains('assignees', $assigneeId)
                        ->orWhereJsonContains('assignees', (string) $assigneeId);
                });
            })
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $today = now()->startOfDay();
        $lateCount = $tasks->filter(function (Task $task) use ($today) {
            return $task->deadline
                && $task->deadline->lt($today)
                && ! in_array($task->status, ['completed', 'cancelled', 'on_hold'], true);
        })->count();

        return response()->json([
            'success' => true,
            'data' => $tasks->map(fn (Task $task) => $this->formatTaskListResource($task))->values(),
            'meta' => [
                'counts' => [
                    'all' => $tasks->count(),
                    'not_started' => $tasks->where('status', 'not_started')->count(),
                    'in_progress' => $tasks->where('status', 'in_progress')->count(),
                    'on_hold' => $tasks->where('status', 'on_hold')->count(),
                    'completed' => $tasks->where('status', 'completed')->count(),
                    'cancelled' => $tasks->where('status', 'cancelled')->count(),
                    'late' => $lateCount,
                ],
                'restricted_to_assigned_tasks' => $this->shouldRestrictToAssignedTasks(),
            ],
        ]);
    }

    public function apiShow($id): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $id, ['project', 'attachments', 'comments.user']);
        $this->normalizeTaskAttachments($task);
        $task->load(['attachments', 'comments.user']);

        return response()->json([
            'success' => true,
            'data' => $this->buildTaskDetailPayload($task),
        ]);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->taskValidationRules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $task = Task::create($this->buildTaskPayload($validator->validated()));

            if ($request->hasFile('attach_files')) {
                foreach ($request->file('attach_files') as $file) {
                    $this->storeTaskAttachment($task->id, $file);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create task: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $id, ['attachments']);

        $validator = Validator::make($request->all(), $this->taskValidationRules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $task->update($this->buildTaskPayload($validator->validated()));

            if ($request->hasFile('attach_files')) {
                foreach ($request->file('attach_files') as $file) {
                    $this->storeTaskAttachment($task->id, $file);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiDestroy($id): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $id, ['attachments']);

        DB::beginTransaction();

        try {
            foreach ($task->attachments as $attachment) {
                $this->deleteTaskAttachmentFile($attachment);
                $attachment->delete();
            }

            $task->comments()->delete();
            $task->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiCommentIndex($taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);

        $comments = TaskComment::query()
            ->where('task_id', $task->id)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments->map(fn (TaskComment $comment) => $this->formatCommentResource($comment))->values(),
        ]);
    }

    public function apiStoreComment(Request $request, $taskId): JsonResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $task = $this->findAccessibleTaskOrFail((int) $taskId);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'data' => $this->formatCommentResource($comment->load('user')),
        ], 201);
    }

    public function apiAttachmentIndex($taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId, ['attachments']);
        $this->normalizeTaskAttachments($task);
        $task->load('attachments');

        return response()->json([
            'success' => true,
            'data' => $task->attachments->map(fn (TaskAttachment $attachment) => $this->formatAttachmentResource($attachment))->values(),
        ]);
    }

    public function apiUploadAttachment(Request $request, $taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);

        $validated = $request->validate([
            'attach_files' => 'required|array|min:1',
            'attach_files.*' => 'file|max:10240',
        ]);

        foreach ($validated['attach_files'] as $file) {
            $this->storeTaskAttachment($task->id, $file);
        }

        $task->load('attachments');

        return response()->json([
            'success' => true,
            'message' => 'Attachments uploaded successfully.',
            'data' => $task->attachments->map(fn (TaskAttachment $attachment) => $this->formatAttachmentResource($attachment))->values(),
        ], 201);
    }

    public function apiDeleteAttachment($taskId, $attachmentId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);

        $attachment = TaskAttachment::query()
            ->where('task_id', $task->id)
            ->where('id', $attachmentId)
            ->firstOrFail();

        try {
            $this->deleteTaskAttachmentFile($attachment);
            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attachment: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiDeleteAll(): JsonResponse
    {
        try {
            $count = Task::count();
            Task::query()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All tasks deleted successfully.',
                'data' => ['deleted_count' => $count],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete all tasks: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiForceDeleteAll(): JsonResponse
    {
        try {
            $trashedTasks = Task::onlyTrashed()->get();
            $count = $trashedTasks->count();

            foreach ($trashedTasks as $task) {
                foreach ($task->attachments as $attachment) {
                    $this->deleteTaskAttachmentFile($attachment);
                    $attachment->forceDelete();
                }
                $task->comments()->forceDelete();
            }

            Task::onlyTrashed()->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'All tasks permanently deleted successfully.',
                'data' => ['deleted_count' => $count],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete all tasks: '.$e->getMessage(),
            ], 500);
        }
    }

    private function taskValidationRules(): array
    {
        return [
            'task_title' => 'required|string|max:255',
            'project_related' => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:High,Medium,Low,high,medium,low',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:staff,id',
            'followers' => 'nullable|array',
            'followers.*' => 'exists:staff,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'task_description' => 'nullable|string',
            'attach_files' => 'nullable|array',
            'attach_files.*' => 'file|max:10240',
        ];
    }

    private function buildTaskPayload(array $validated): array
    {
        $priority = strtolower((string) ($validated['priority'] ?? 'medium'));

        return [
            'title' => $validated['task_title'],
            'description' => $validated['task_description'] ?? null,
            'project_id' => $validated['project_related'] ?? null,
            'followers' => $validated['followers'] ?? [],
            'assignees' => $validated['assignees'] ?? [],
            'tags' => $validated['tags'] ?? [],
            'status' => $validated['status'] ?? 'not_started',
            'priority' => $priority,
            'start_date' => $validated['start_date'] ?? null,
            'deadline' => $validated['due_date'] ?? null,
        ];
    }

    private function accessibleTasksQuery(): Builder
    {
        $query = Task::query();

        if ($this->isPrivilegedTaskUser()) {
            return $query;
        }

        $staffId = $this->authenticatedStaffId();

        if ($staffId === null) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($staffId) {
            $builder->whereJsonContains('assignees', $staffId)
                ->orWhereJsonContains('assignees', (string) $staffId);
        });
    }

    private function findAccessibleTaskOrFail(int $taskId, array $with = []): Task
    {
        return $this->accessibleTasksQuery()
            ->with($with)
            ->findOrFail($taskId);
    }

    private function isPrivilegedTaskUser(): bool
    {
        $user = auth()->user();

        return (bool) ($user && $user->hasAnyRole(['super-admin', 'super_admin', 'admin']));
    }

    private function authenticatedStaffId(): ?int
    {
        $user = auth()->user();

        if (! $user || ! $user->isStaff()) {
            return null;
        }

        return optional($user->staff)->id;
    }

    private function shouldRestrictToAssignedTasks(): bool
    {
        return ! $this->isPrivilegedTaskUser() && $this->authenticatedStaffId() !== null;
    }

    private function buildTaskDetailPayload(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'start_date' => optional($task->start_date)?->toDateString(),
            'deadline' => optional($task->deadline)?->toDateString(),
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->project_name,
            ] : null,
            'followers' => $this->formatStaffCollection($task->followers ?? []),
            'assignees' => $this->formatStaffCollection($task->assignees ?? []),
            'tags' => $task->tags ?? [],
            'attachments' => $task->attachments->map(fn (TaskAttachment $attachment) => $this->formatAttachmentResource($attachment))->values(),
            'comments' => $task->comments->map(fn (TaskComment $comment) => $this->formatCommentResource($comment))->values(),
            'created_at' => optional($task->created_at)?->toDateTimeString(),
            'updated_at' => optional($task->updated_at)?->toDateTimeString(),
        ];
    }

    private function formatTaskListResource(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->project_name,
            ] : null,
            'assignees' => $this->formatStaffCollection($task->assignees ?? []),
            'followers' => $this->formatStaffCollection($task->followers ?? []),
            'start_date' => optional($task->start_date)?->toDateString(),
            'deadline' => optional($task->deadline)?->toDateString(),
            'attachments_count' => $task->attachments->count(),
            'created_at' => optional($task->created_at)?->toDateTimeString(),
        ];
    }

    private function formatStaffCollection(array $staffIds): array
    {
        $ids = collect($staffIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Staff::query()
            ->whereIn('id', $ids)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Staff $member) => [
                'id' => $member->id,
                'name' => trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                'email' => $member->email,
                'profile_image' => asset('uploads/staff/'.$member->profile_image),
            ])
            ->values()
            ->all();
    }

    private function formatCommentResource(TaskComment $comment): array
    {
        return [
            'id' => $comment->id,
            'comment' => $comment->comment,
            'user' => $comment->user ? [
                'id' => $comment->user->id,
                'name' => trim(($comment->user->name ?? '')),
                'email' => $comment->user->email,
            ] : null,
            'created_at' => optional($comment->created_at)?->toDateTimeString(),
        ];
    }

    private function formatAttachmentResource(TaskAttachment $attachment): array
    {
        $relativePath = ltrim((string) $attachment->file_path, '/');

        return [
            'id' => $attachment->id,
            'file_name' => $attachment->file_name,
            'file_path' => $relativePath,
            'file_url' => $relativePath !== '' ? asset($relativePath) : null,
            'file_type' => $attachment->file_type,
            'file_size' => $attachment->file_size,
            'created_at' => optional($attachment->created_at)?->toDateTimeString(),
        ];
    }

    private function storeTaskAttachment(int $taskId, $file): void
    {
        if (! $file || ! $file->isValid()) {
            throw new \RuntimeException('Uploaded file is not valid or is no longer readable.');
        }

        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $baseName) ?: 'attachment';
        $fileName = time().'_'.$safeBaseName.($extension ? '.'.$extension : '');
        $directory = public_path('uploads/task_attachments/'.$taskId);

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $file->move($directory, $fileName);

        TaskAttachment::create([
            'task_id' => $taskId,
            'file_name' => $originalName,
            'file_path' => 'uploads/task_attachments/'.$taskId.'/'.$fileName,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
        ]);
    }

    private function normalizeTaskAttachments(Task $task): void
    {
        foreach ($task->attachments as $attachment) {
            $currentPath = ltrim((string) $attachment->file_path, '/');

            if ($currentPath === '' || str_starts_with($currentPath, 'uploads/task_attachments/')) {
                continue;
            }

            $sourceRelativePath = str_starts_with($currentPath, 'storage/')
                ? substr($currentPath, 8)
                : $currentPath;

            $storageSource = storage_path('app/public/'.$sourceRelativePath);
            $targetDirectory = public_path('uploads/task_attachments/'.$task->id);
            $targetRelativePath = 'uploads/task_attachments/'.$task->id.'/'.basename($sourceRelativePath);
            $targetAbsolutePath = public_path($targetRelativePath);

            if (! file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            if (file_exists($storageSource) && ! file_exists($targetAbsolutePath)) {
                copy($storageSource, $targetAbsolutePath);
            }

            if (file_exists($targetAbsolutePath) && $attachment->file_path !== $targetRelativePath) {
                $attachment->update(['file_path' => $targetRelativePath]);
            }
        }
    }

    private function deleteTaskAttachmentFile(TaskAttachment $attachment): void
    {
        $relativePath = ltrim((string) $attachment->file_path, '/');

        $publicFile = public_path($relativePath);
        if ($relativePath !== '' && file_exists($publicFile)) {
            unlink($publicFile);
        }

        $storageRelativePath = str_starts_with($relativePath, 'storage/')
            ? substr($relativePath, 8)
            : $relativePath;
        $storageFile = storage_path('app/public/'.$storageRelativePath);
        if ($storageRelativePath !== '' && file_exists($storageFile)) {
            unlink($storageFile);
        }
    }
}
