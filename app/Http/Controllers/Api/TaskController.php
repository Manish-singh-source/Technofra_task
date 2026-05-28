<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\TaskTimeLog;
use App\Models\User;
use App\Services\ProjectManagement\MilestoneProgressService;
use App\Services\ProjectManagement\ProjectActivityService;
use App\Services\ProjectManagement\TaskCommentService;
use App\Services\ProjectManagement\TaskDependencyService;
use App\Services\ProjectManagement\TaskLifecycleService;
use App\Services\ProjectManagement\ProjectNotificationService;
use App\Services\ProjectManagement\TimeTrackingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct(
        private TaskLifecycleService $taskLifecycleService,
        private TaskDependencyService $taskDependencyService,
        private MilestoneProgressService $milestoneProgressService,
        private ProjectActivityService $projectActivityService,
        private TaskCommentService $taskCommentService,
        private TimeTrackingService $timeTrackingService,
        private ProjectNotificationService $projectNotificationService
    ) {}

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
                'staff' => User::query()
                    ->where('role', 'staff')
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get()
                    ->map(fn (User $member) => [
                        'id' => $member->id,
                        'name' => trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                        'email' => $member->email,
                        'role' => $member->role,
                    ])
                    ->values(),
                'statuses' => ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'],
                'workflow_statuses' => $this->taskLifecycleService->statuses(),
                'priorities' => ['low', 'medium', 'high'],
                'selected_project_id' => $selectedProjectId ? (int) $selectedProjectId : null,
            ],
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $statusFilter = $this->normalizeStatusFilter($request->input('status'));

        $filteredQuery = $this->accessibleTasksQuery()
            ->when($statusFilter !== null, fn (Builder $query) => $query->where('status', $statusFilter))
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
            });

        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        $tasks = (clone $filteredQuery)
            ->with(['project', 'attachments'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        $today = now()->startOfDay();
        $lateCount = (clone $filteredQuery)
            ->whereDate('deadline', '<', $today)
            ->whereNotIn('status', ['completed', 'cancelled', 'on_hold'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => collect($tasks->items())->map(fn (Task $task) => $this->formatTaskListResource($task))->values(),
            'meta' => [
                'counts' => [
                    'all' => (clone $filteredQuery)->count(),
                    'not_started' => (clone $filteredQuery)->where('status', 'not_started')->count(),
                    'in_progress' => (clone $filteredQuery)->where('status', 'in_progress')->count(),
                    'on_hold' => (clone $filteredQuery)->where('status', 'on_hold')->count(),
                    'completed' => (clone $filteredQuery)->where('status', 'completed')->count(),
                    'cancelled' => (clone $filteredQuery)->where('status', 'cancelled')->count(),
                    'late' => $lateCount,
                ],
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'from' => $tasks->firstItem(),
                    'to' => $tasks->lastItem(),
                    'has_more_pages' => $tasks->hasMorePages(),
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
            $validated = $validator->validated();
            $this->taskLifecycleService->ensureTransition(null, $validated['workflow_status'] ?? null);
            $task = Task::create($this->buildTaskPayload($validated));
            $this->projectNotificationService->notifyTaskAssigned(
                $task->fresh('project'),
                $task->assignees ?? [],
                auth()->id()
            );

            if ($request->hasFile('attach_files')) {
                foreach ($request->file('attach_files') as $file) {
                    $this->storeTaskAttachment($task->id, $file);
                }
            }

            if ($task->milestone_id) {
                $this->milestoneProgressService->syncForMilestone((int) $task->milestone_id);
            }
            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_created',
                    'Task Created',
                    'A new task was created: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    [
                        'status' => $task->status,
                        'workflow_status' => $task->workflow_status,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
            $validated = $validator->validated();
            $nextWorkflowStatus = $validated['workflow_status'] ?? $task->workflow_status;
            $this->taskLifecycleService->ensureTransition($task->workflow_status, $nextWorkflowStatus);
            $oldMilestoneId = $task->milestone_id;
            $oldProjectId = $task->project_id;
            $oldStatus = $task->status;
            $oldWorkflowStatus = $task->workflow_status;
            $oldAssigneeIds = collect($task->assignees ?? [])->map(fn ($id) => (int) $id)->unique();
            $task->update($this->buildTaskPayload($validated));
            $newAssigneeIds = collect($task->assignees ?? [])->map(fn ($id) => (int) $id)->unique();
            $addedAssigneeIds = $newAssigneeIds->diff($oldAssigneeIds)->values()->all();

            if ($request->hasFile('attach_files')) {
                foreach ($request->file('attach_files') as $file) {
                    $this->storeTaskAttachment($task->id, $file);
                }
            }

            if ($oldMilestoneId && (int) $oldMilestoneId !== (int) $task->milestone_id) {
                $this->milestoneProgressService->syncForMilestone((int) $oldMilestoneId);
            }
            if ($task->milestone_id) {
                $this->milestoneProgressService->syncForMilestone((int) $task->milestone_id);
            }

            $projectForActivity = $task->project_id ?: $oldProjectId;
            if ($projectForActivity) {
                $this->projectActivityService->log(
                    (int) $projectForActivity,
                    'task_updated',
                    'Task Updated',
                    'Task was updated: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    [
                        'old_status' => $oldStatus,
                        'new_status' => $task->status,
                        'old_workflow_status' => $oldWorkflowStatus,
                        'new_workflow_status' => $task->workflow_status,
                    ]
                );
            }

            if ($oldStatus !== $task->status || $oldWorkflowStatus !== $task->workflow_status) {
                if ($projectForActivity) {
                    $this->projectActivityService->log(
                        (int) $projectForActivity,
                        'task_status_changed',
                        'Task Status Changed',
                        'Task status changed for: '.$task->title,
                        (int) $task->id,
                        auth()->id(),
                        [
                            'old_status' => $oldStatus,
                            'new_status' => $task->status,
                            'old_workflow_status' => $oldWorkflowStatus,
                            'new_workflow_status' => $task->workflow_status,
                        ]
                    );
                }
            }

            if (! empty($addedAssigneeIds)) {
                $this->projectNotificationService->notifyTaskAssigned(
                    $task->fresh('project'),
                    $addedAssigneeIds,
                    auth()->id()
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
            if ($task->milestone_id) {
                $this->milestoneProgressService->syncForMilestone((int) $task->milestone_id);
            }
            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_deleted',
                    'Task Deleted',
                    'Task deleted: '.$task->title,
                    (int) $task->id,
                    auth()->id()
                );
            }
            $this->projectNotificationService->notifyQaReviewRequested($task->fresh('project'), auth()->id());
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
            ->whereNull('parent_id')
            ->with('user', 'replies.user', 'replies.attachments', 'attachments')
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
            'parent_id' => 'nullable|integer|exists:task_comments,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $task = $this->findAccessibleTaskOrFail((int) $taskId);

        $comment = $this->taskCommentService->create(
            $task,
            (int) auth()->id(),
            $validated['comment'],
            isset($validated['parent_id']) ? (int) $validated['parent_id'] : null
        );

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $this->taskCommentService->storeAttachment($comment, $attachment);
            }
        }

        if ($task->project_id) {
            $this->projectActivityService->log(
                (int) $task->project_id,
                'task_comment_added',
                'Task Comment Added',
                'A comment was added on task: '.$task->title,
                (int) $task->id,
                auth()->id()
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'data' => $this->formatCommentResource($comment->load('user', 'attachments')),
        ], 201);
    }

    public function apiUpdateComment(Request $request, $taskId, $commentId): JsonResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $comment = TaskComment::query()
            ->where('task_id', $task->id)
            ->where('id', (int) $commentId)
            ->firstOrFail();

        $comment = $this->taskCommentService->update($comment, $validated['comment']);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully.',
            'data' => $this->formatCommentResource($comment),
        ]);
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

    public function apiDependencyIndex($taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $dependencies = $this->taskDependencyService->dependencyTree((int) $task->id);

        return response()->json([
            'success' => true,
            'data' => $dependencies->map(function ($dependency) {
                return [
                    'id' => $dependency->id,
                    'task_id' => $dependency->task_id,
                    'depends_on_task_id' => $dependency->depends_on_task_id,
                    'dependency_type' => $dependency->dependency_type,
                    'depends_on_task' => $dependency->dependsOnTask ? [
                        'id' => $dependency->dependsOnTask->id,
                        'title' => $dependency->dependsOnTask->title,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function apiStoreDependency(Request $request, $taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'depends_on_task_id' => 'required|integer|exists:tasks,id',
            'dependency_type' => 'required|string',
        ]);

        try {
            $dependency = $this->taskDependencyService->createDependency(
                (int) $task->id,
                (int) $validated['depends_on_task_id'],
                (string) $validated['dependency_type'],
                auth()->id()
            );

            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_dependency_added',
                    'Task Dependency Added',
                    'Dependency added for task: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    [
                        'depends_on_task_id' => (int) $validated['depends_on_task_id'],
                        'dependency_type' => (string) $validated['dependency_type'],
                    ]
                );
            }
            $this->projectNotificationService->notifyDeploymentCompleted($task->fresh('project'), auth()->id());
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dependency created successfully.',
            'data' => [
                'id' => $dependency->id,
                'task_id' => $dependency->task_id,
                'depends_on_task_id' => $dependency->depends_on_task_id,
                'dependency_type' => $dependency->dependency_type,
            ],
        ], 201);
    }

    public function apiDeleteDependency(Request $request, $taskId, $dependsOnTaskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $dependencyType = $request->input('dependency_type');
        $deleted = $this->taskDependencyService->removeDependency((int) $task->id, (int) $dependsOnTaskId, $dependencyType);

        if ($deleted > 0 && $task->project_id) {
            $this->projectActivityService->log(
                (int) $task->project_id,
                'task_dependency_removed',
                'Task Dependency Removed',
                'Dependency removed for task: '.$task->title,
                (int) $task->id,
                auth()->id(),
                [
                    'depends_on_task_id' => (int) $dependsOnTaskId,
                    'dependency_type' => $dependencyType,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => $deleted > 0 ? 'Dependency removed successfully.' : 'No matching dependency found.',
        ]);
    }

    public function apiChecklistIndex($taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);

        $items = TaskChecklist::query()
            ->where('task_id', $task->id)
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items->map(fn (TaskChecklist $item) => [
                'id' => $item->id,
                'task_id' => $item->task_id,
                'parent_id' => $item->parent_id,
                'title' => $item->title,
                'is_completed' => (bool) $item->is_completed,
                'completed_by' => $item->completed_by,
                'completed_at' => optional($item->completed_at)?->toDateTimeString(),
                'sort_order' => $item->sort_order,
            ])->values(),
        ]);
    }

    public function apiStoreChecklist(Request $request, $taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:task_checklists,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $item = TaskChecklist::create([
            'task_id' => $task->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if ($task->project_id) {
            $this->projectActivityService->log(
                (int) $task->project_id,
                'task_checklist_added',
                'Task Checklist Added',
                'Checklist item added for task: '.$task->title,
                (int) $task->id,
                auth()->id(),
                ['checklist_id' => $item->id]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Checklist item created successfully.',
            'data' => $item,
        ], 201);
    }

    public function apiUpdateChecklist(Request $request, $taskId, $checklistId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'is_completed' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $item = TaskChecklist::query()
            ->where('task_id', $task->id)
            ->where('id', (int) $checklistId)
            ->firstOrFail();

        $payload = [];
        if (array_key_exists('title', $validated)) {
            $payload['title'] = $validated['title'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $payload['sort_order'] = $validated['sort_order'];
        }
        if (array_key_exists('is_completed', $validated)) {
            $payload['is_completed'] = (bool) $validated['is_completed'];
            $payload['completed_at'] = $validated['is_completed'] ? now() : null;
            $payload['completed_by'] = $validated['is_completed'] ? auth()->id() : null;
        }

        $item->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Checklist item updated successfully.',
            'data' => $item->fresh(),
        ]);
    }

    public function apiDeleteChecklist($taskId, $checklistId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $item = TaskChecklist::query()
            ->where('task_id', $task->id)
            ->where('id', (int) $checklistId)
            ->firstOrFail();
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Checklist item deleted successfully.',
        ]);
    }

    public function apiTimeLogStart(Request $request, $taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $log = $this->timeTrackingService->startTimer($task, (int) auth()->id(), $validated['note'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Timer started.',
            'data' => $log,
        ]);
    }

    public function apiTimeLogStop($taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $log = $this->timeTrackingService->stopTimer($task, (int) auth()->id());

        if (! $log) {
            return response()->json([
                'success' => false,
                'message' => 'No running timer found for this task.',
            ], 422);
        }

        if ($task->project_id) {
            $this->projectActivityService->log(
                (int) $task->project_id,
                'task_time_logged',
                'Task Time Logged',
                'Timer stopped for task: '.$task->title,
                (int) $task->id,
                auth()->id(),
                ['duration_minutes' => $log->duration_minutes]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Timer stopped.',
            'data' => $log,
        ]);
    }

    public function apiTimeLogManual(Request $request, $taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'duration_minutes' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
        ]);

        $log = $this->timeTrackingService->manualLog(
            $task,
            (int) auth()->id(),
            (float) $validated['duration_minutes'],
            $validated['note'] ?? null,
            $validated['started_at'] ?? null,
            $validated['ended_at'] ?? null
        );

        if ($task->project_id) {
            $this->projectActivityService->log(
                (int) $task->project_id,
                'task_time_logged',
                'Task Time Logged',
                'Manual time entry added for task: '.$task->title,
                (int) $task->id,
                auth()->id(),
                ['duration_minutes' => $log->duration_minutes]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Manual time log added.',
            'data' => $log,
        ], 201);
    }

    public function apiTimeLogReport($taskId): JsonResponse
    {
        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $summary = $this->timeTrackingService->summarizeForTask($task);
        $logs = TaskTimeLog::query()
            ->where('task_id', $task->id)
            ->orderByDesc('started_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'logs' => $logs,
            ],
        ]);
    }

    public function apiQaRequestReview(Request $request, $taskId): JsonResponse
    {
        if (($auth = $this->authorizeTaskWriteAction('edit_tasks')) !== null) {
            return $auth;
        }

        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $oldWorkflowStatus = (string) $task->workflow_status;
            $this->taskLifecycleService->ensureTransition($task->workflow_status, 'review');

            $task->update([
                'workflow_status' => 'review',
                'qa_status' => 'review_requested',
            ]);

            $this->recordTaskStatusHistory($task, $oldWorkflowStatus, 'review', $validated['note'] ?? 'Review requested.');

            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_review_requested',
                    'Task Review Requested',
                    'Review requested for task: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    ['qa_status' => $task->qa_status]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task moved to review.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to request review: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiQaReview(Request $request, $taskId): JsonResponse
    {
        if (($auth = $this->authorizeTaskWriteAction('edit_tasks')) !== null) {
            return $auth;
        }

        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'decision' => 'required|in:approved,changes_requested',
            'note' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $decision = (string) $validated['decision'];
            $oldWorkflowStatus = (string) $task->workflow_status;
            $nextWorkflowStatus = $decision === 'approved' ? 'testing' : 'in_progress';
            $this->taskLifecycleService->ensureTransition($task->workflow_status, $nextWorkflowStatus);

            $task->update([
                'workflow_status' => $nextWorkflowStatus,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'qa_status' => $decision === 'approved' ? 'in_qa' : 'changes_requested',
                'blocked_reason' => $decision === 'changes_requested' ? ($validated['note'] ?? 'Review changes requested.') : null,
            ]);

            $historyRemark = $validated['note'] ?? ($decision === 'approved' ? 'Review approved.' : 'Review changes requested.');
            $this->recordTaskStatusHistory($task, $oldWorkflowStatus, $nextWorkflowStatus, $historyRemark);

            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_review_completed',
                    'Task Review Completed',
                    'Review completed for task: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    [
                        'decision' => $decision,
                        'qa_status' => $task->qa_status,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task review updated successfully.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update review: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiQaApprove(Request $request, $taskId): JsonResponse
    {
        if (($auth = $this->authorizeTaskWriteAction('edit_tasks')) !== null) {
            return $auth;
        }

        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'decision' => 'required|in:passed,failed',
            'note' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $decision = (string) $validated['decision'];
            $qaStatus = $decision === 'passed' ? 'approved' : 'qa_failed';

            $task->update([
                'qa_status' => $qaStatus,
                'blocked_reason' => $decision === 'failed' ? ($validated['note'] ?? 'QA failed.') : null,
            ]);

            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_qa_reviewed',
                    'Task QA Reviewed',
                    'QA reviewed task: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    [
                        'decision' => $decision,
                        'qa_status' => $task->qa_status,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QA decision saved successfully.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save QA decision: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiMarkDeployed(Request $request, $taskId): JsonResponse
    {
        if (($auth = $this->authorizeTaskWriteAction('edit_tasks')) !== null) {
            return $auth;
        }

        $task = $this->findAccessibleTaskOrFail((int) $taskId);
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $oldWorkflowStatus = (string) $task->workflow_status;
            $this->taskLifecycleService->ensureTransition($task->workflow_status, 'deployed');

            $task->update([
                'workflow_status' => 'deployed',
                'deployed_at' => now(),
                'qa_status' => 'deployed',
            ]);

            $this->recordTaskStatusHistory($task, $oldWorkflowStatus, 'deployed', $validated['note'] ?? 'Task deployed.');

            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_deployed',
                    'Task Deployed',
                    'Task deployed: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    ['deployed_at' => optional($task->deployed_at)?->toISOString()]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task marked as deployed.',
                'data' => $this->buildTaskDetailPayload($task->fresh(['project', 'attachments', 'comments.user'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark deployment: '.$e->getMessage(),
            ], 500);
        }
    }

    private function recordTaskStatusHistory(Task $task, ?string $from, string $to, ?string $remarks = null): void
    {
        DB::table('task_status_histories')->insert([
            'task_id' => $task->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => auth()->id(),
            'remarks' => $remarks,
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function taskValidationRules(): array
    {
        return [
            'task_title' => 'required|string|max:255',
            'project_related' => 'nullable|exists:projects,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'priority' => 'nullable|in:High,Medium,Low,high,medium,low',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'workflow_status' => 'nullable|string',
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
            'milestone_id' => $validated['milestone_id'] ?? null,
            'followers' => $validated['followers'] ?? [],
            'assignees' => $validated['assignees'] ?? [],
            'tags' => $validated['tags'] ?? [],
            'status' => $validated['status'] ?? 'not_started',
            'workflow_status' => $validated['workflow_status'] ?? 'backlog',
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
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($staffId) {
            $builder->whereJsonContains('assignees', $staffId)
                ->orWhereJsonContains('assignees', (string) $staffId)
                ->orWhereJsonContains('followers', $staffId)
                ->orWhereJsonContains('followers', (string) $staffId);
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

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2'])) {
            return true;
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));

        return in_array($rawRole, ['admin', 'super-admin', 'super_admin', 'super_admin2'], true);
    }

    private function authenticatedStaffId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));
        if ($rawRole !== 'staff') {
            return null;
        }

        return (int) $user->id;
    }

    private function shouldRestrictToAssignedTasks(): bool
    {
        return ! $this->isPrivilegedTaskUser() && $this->authenticatedStaffId() !== null;
    }

    private function authorizeTaskWriteAction(string $permission): ?JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));
        if ($rawRole === 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Clients have read-only project portal access.',
            ], 403);
        }

        if ($this->isPrivilegedTaskUser()) {
            return null;
        }

        if (method_exists($user, 'can') && $user->can($permission)) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission for this action.',
        ], 403);
    }

    private function normalizeStatusFilter(mixed $status): ?string
    {
        if (! is_string($status)) {
            return null;
        }

        $normalized = strtolower(trim($status));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        $aliases = [
            'running' => 'in_progress',
            'hold' => 'on_hold',
            'delayed' => 'on_hold',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    private function buildTaskDetailPayload(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'workflow_status' => $task->workflow_status,
            'priority' => $task->priority,
            'qa_status' => $task->qa_status,
            'reviewed_by' => $task->reviewed_by,
            'reviewed_at' => optional($task->reviewed_at)?->toDateTimeString(),
            'deployed_at' => optional($task->deployed_at)?->toDateTimeString(),
            'blocked_reason' => $task->blocked_reason,
            'start_date' => optional($task->start_date)?->toDateString(),
            'deadline' => optional($task->deadline)?->toDateString(),
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->project_name,
            ] : null,
            'milestone_id' => $task->milestone_id,
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
            'workflow_status' => $task->workflow_status,
            'priority' => $task->priority,
            'qa_status' => $task->qa_status,
            'reviewed_at' => optional($task->reviewed_at)?->toDateTimeString(),
            'deployed_at' => optional($task->deployed_at)?->toDateTimeString(),
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->project_name,
            ] : null,
            'milestone_id' => $task->milestone_id,
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

        return User::query()
            ->where('role', 'staff')
            ->whereIn('id', $ids)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                'email' => $member->email,
                'profile_image' => asset($member->profile_image),
            ])
            ->values()
            ->all();
    }

    private function formatCommentResource(TaskComment $comment): array
    {
        return [
            'id' => $comment->id,
            'task_id' => $comment->task_id,
            'parent_id' => $comment->parent_id,
            'comment' => $comment->comment,
            'mentions' => $comment->mentions ?? [],
            'edited_at' => optional($comment->edited_at)?->toDateTimeString(),
            'edit_history' => $comment->edit_history ?? [],
            'user' => $comment->user ? [
                'id' => $comment->user->id,
                'name' => trim(($comment->user->name ?? '')),
                'email' => $comment->user->email,
            ] : null,
            'attachments' => $comment->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
                'file_url' => asset(ltrim((string) $attachment->file_path, '/')),
                'file_type' => $attachment->file_type,
                'file_size' => $attachment->file_size,
            ])->values(),
            'replies' => $comment->replies->map(fn (TaskComment $reply) => [
                'id' => $reply->id,
                'task_id' => $reply->task_id,
                'parent_id' => $reply->parent_id,
                'comment' => $reply->comment,
                'mentions' => $reply->mentions ?? [],
                'edited_at' => optional($reply->edited_at)?->toDateTimeString(),
                'edit_history' => $reply->edit_history ?? [],
                'user' => $reply->user ? [
                    'id' => $reply->user->id,
                    'name' => trim(($reply->user->name ?? '')),
                    'email' => $reply->user->email,
                ] : null,
                'attachments' => $reply->attachments->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_path' => $attachment->file_path,
                    'file_url' => asset(ltrim((string) $attachment->file_path, '/')),
                    'file_type' => $attachment->file_type,
                    'file_size' => $attachment->file_size,
                ])->values(),
                'created_at' => optional($reply->created_at)?->toDateTimeString(),
            ])->values(),
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
