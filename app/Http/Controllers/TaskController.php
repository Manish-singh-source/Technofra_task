<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\ProjectManagement\MilestoneProgressService;
use App\Services\ProjectManagement\ProjectActivityService;
use App\Services\ProjectManagement\TaskLifecycleService;
use App\Services\ProjectManagement\ProjectNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(
        private TaskLifecycleService $taskLifecycleService,
        private MilestoneProgressService $milestoneProgressService,
        private ProjectActivityService $projectActivityService,
        private ProjectNotificationService $projectNotificationService
    ) {}

    public function index()
    {
        $tasks = $this->accessibleTasksQuery()
            ->with('project', 'attachments')
            ->orderByDesc('created_at')
            ->get();

        $staff = User::staffMembers()->get()->keyBy('id');
        $today = now()->startOfDay();

        $runningTasks = $tasks->where('status', 'in_progress')->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $lateTasks = $tasks->filter(function ($task) use ($today) {
            return $task->deadline
                && $task->deadline->lt($today)
                && !in_array($task->status, ['completed', 'cancelled', 'on_hold'], true);
        })->count();
        $delayedTasks = $tasks->where('status', 'on_hold')->count();
        $isRestrictedToAssignedTasks = $this->shouldRestrictToAssignedTasks();

        return view('task', compact('tasks', 'staff', 'runningTasks', 'completedTasks', 'lateTasks', 'delayedTasks', 'isRestrictedToAssignedTasks'));
    }

    public function kanban(Request $request)
    {
        $this->ensureKanbanViewAccess();

        $projects = Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_name']);

        $selectedProjectId = $request->query('project_id');

        return view('task-kanban', compact('projects', 'selectedProjectId'));
    }

    public function kanbanData(Request $request): JsonResponse
    {
        $this->ensureKanbanViewAccess();

        $query = $this->accessibleTasksQuery()
            ->with('project')
            ->when($request->filled('project_id'), fn (Builder $builder) => $builder->where('project_id', (int) $request->input('project_id')))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $search = trim((string) $request->input('search'));
                $builder->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sequence_order')
            ->orderByDesc('created_at');

        $tasks = $query->get();

        $assigneeIds = $tasks->pluck('assignees')
            ->flatten()
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $staff = User::query()
            ->whereIn('id', $assigneeIds)
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        $columns = [
            'backlog' => ['key' => 'backlog', 'label' => 'Backlog', 'tasks' => []],
            'todo' => ['key' => 'todo', 'label' => 'Todo', 'tasks' => []],
            'in_progress' => ['key' => 'in_progress', 'label' => 'In Progress', 'tasks' => []],
            'review' => ['key' => 'review', 'label' => 'Review', 'tasks' => []],
            'testing' => ['key' => 'testing', 'label' => 'Testing', 'tasks' => []],
            'done' => ['key' => 'done', 'label' => 'Done', 'tasks' => []],
        ];

        foreach ($tasks as $task) {
            $columnKey = $this->kanbanColumnFromWorkflow((string) ($task->workflow_status ?? 'backlog'));
            $columns[$columnKey]['tasks'][] = [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->project?->project_name,
                'priority' => $task->priority,
                'status' => $task->status,
                'workflow_status' => $task->workflow_status,
                'deadline' => optional($task->deadline)?->toDateString(),
                'sequence_order' => $task->sequence_order,
                'assignees' => collect($task->assignees ?? [])->map(function ($assigneeId) use ($staff) {
                    $member = $staff->get((int) $assigneeId);
                    if (! $member) {
                        return null;
                    }

                    return [
                        'id' => $member->id,
                        'name' => trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                    ];
                })->filter()->values()->all(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'columns' => array_values($columns),
            ],
        ]);
    }

    public function kanbanMove(Request $request, $id): JsonResponse
    {
        $this->ensureKanbanMoveAccess();
        $task = $this->findAccessibleTaskOrFail((int) $id);

        $validated = $request->validate([
            'workflow_status' => 'required|in:backlog,todo,in_progress,blocked,review,testing,deployed,completed,archived',
            'sequence_order' => 'nullable|integer|min:0',
            'column_task_ids' => 'nullable|array',
            'column_task_ids.*' => 'integer',
        ]);

        DB::beginTransaction();

        try {
            $oldWorkflowStatus = (string) ($task->workflow_status ?? 'backlog');
            $newWorkflowStatus = (string) $validated['workflow_status'];
            $this->taskLifecycleService->ensureTransition($oldWorkflowStatus, $newWorkflowStatus);

            $task->update([
                'workflow_status' => $newWorkflowStatus,
                'status' => $this->mapLegacyStatusFromWorkflow($newWorkflowStatus),
                'sequence_order' => $validated['sequence_order'] ?? $task->sequence_order,
            ]);

            if (array_key_exists('column_task_ids', $validated) && is_array($validated['column_task_ids'])) {
                $orderedIds = collect($validated['column_task_ids'])
                    ->map(fn ($value) => (int) $value)
                    ->filter()
                    ->unique()
                    ->values();

                $accessibleInColumn = $this->accessibleTasksQuery()
                    ->whereIn('id', $orderedIds)
                    ->pluck('id')
                    ->map(fn ($value) => (int) $value)
                    ->all();
                $allowedLookup = array_flip($accessibleInColumn);

                foreach ($orderedIds as $index => $taskId) {
                    if (! isset($allowedLookup[$taskId])) {
                        continue;
                    }

                    Task::query()
                        ->where('id', $taskId)
                        ->update([
                            'workflow_status' => $newWorkflowStatus,
                            'status' => $this->mapLegacyStatusFromWorkflow($newWorkflowStatus),
                            'sequence_order' => $index + 1,
                        ]);
                }
            }

            if ($oldWorkflowStatus !== $newWorkflowStatus) {
                $this->recordTaskStatusHistory(
                    $task->id,
                    $oldWorkflowStatus,
                    $newWorkflowStatus,
                    'Status moved via Kanban board.'
                );
            }

            if ($task->project_id) {
                $this->projectActivityService->log(
                    (int) $task->project_id,
                    'task_kanban_moved',
                    'Task Moved',
                    'Task moved on Kanban board: '.$task->title,
                    (int) $task->id,
                    auth()->id(),
                    [
                        'old_workflow_status' => $oldWorkflowStatus,
                        'new_workflow_status' => $newWorkflowStatus,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task moved successfully.',
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
                'message' => 'Failed to move task: '.$e->getMessage(),
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $projects = Project::orderBy('project_name')->get();
        $staff = User::staffMembers()->orderBy('first_name')->orderBy('last_name')->get();
        $selectedProjectId = $request->query('project_id');

        return view('add-task', compact('projects', 'staff', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string|max:255',
            'project_related' => 'nullable|exists:projects,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'priority' => 'nullable|in:High,Medium,Low',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'workflow_status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'nullable|array',
            'assignees.*' => [
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
            'followers' => 'nullable|array',
            'followers.*' => [
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'task_description' => 'nullable|string',
            'attach_files' => 'nullable|array',
            'attach_files.*' => 'file|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $priority = strtolower($request->priority ?? 'medium');
        $tags = $request->tags ?? [];
        try {
            $this->taskLifecycleService->ensureTransition(null, $request->workflow_status);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'workflow_status' => $e->getMessage(),
            ]);
        }

        $task = Task::create([
            'title' => $request->task_title,
            'description' => $request->task_description,
            'project_id' => $request->project_related,
            'milestone_id' => $request->milestone_id,
            'followers' => $request->followers,
            'assignees' => $request->assignees,
            'tags' => $tags,
            'status' => $request->status ?? 'not_started',
            'workflow_status' => $request->workflow_status ?? 'backlog',
            'priority' => $priority,
            'start_date' => $request->start_date,
            'deadline' => $request->due_date,
        ]);
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

        return redirect()->route('task')->with('success', 'Task created successfully!');
    }

    public function show($id)
    {
        $task = $this->findAccessibleTaskOrFail((int) $id, ['project', 'attachments', 'comments']);
        $this->normalizeTaskAttachments($task);
        $task->load('attachments');
        $staff = User::staffMembers()->get()->keyBy('id');

        return view('task-details', compact('task', 'staff'));
    }

    public function edit($id)
    {
        $task = $this->findAccessibleTaskOrFail((int) $id, ['attachments']);
        $this->normalizeTaskAttachments($task);
        $task->load('attachments');
        $projects = Project::all();
        $staff = User::staffMembers()->orderBy('first_name')->orderBy('last_name')->get();

        return view('edit-task', compact('task', 'projects', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $task = $this->findAccessibleTaskOrFail((int) $id, ['attachments']);

        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string|max:255',
            'project_related' => 'nullable|exists:projects,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'priority' => 'nullable|in:High,Medium,Low',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'nullable|array',
            'assignees.*' => [
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
            'followers' => 'nullable|array',
            'followers.*' => [
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'task_description' => 'nullable|string',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'workflow_status' => 'nullable|string',
            'attach_files' => 'nullable|array',
            'attach_files.*' => 'file|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $priority = strtolower($request->priority ?? 'medium');
        $tags = $request->tags ?? [];
        try {
            $this->taskLifecycleService->ensureTransition($task->workflow_status, $request->workflow_status);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'workflow_status' => $e->getMessage(),
            ]);
        }

        $oldMilestoneId = $task->milestone_id;
        $oldProjectId = $task->project_id;
        $oldStatus = $task->status;
        $oldWorkflowStatus = $task->workflow_status;
        $oldAssigneeIds = collect($task->assignees ?? [])->map(fn ($id) => (int) $id)->unique();

        $task->update([
            'title' => $request->task_title,
            'description' => $request->task_description,
            'project_id' => $request->project_related,
            'milestone_id' => $request->milestone_id,
            'followers' => $request->followers,
            'assignees' => $request->assignees,
            'tags' => $tags,
            'status' => $request->status ?? 'not_started',
            'workflow_status' => $request->workflow_status ?? $task->workflow_status,
            'priority' => $priority,
            'start_date' => $request->start_date,
            'deadline' => $request->due_date,
        ]);
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

        return redirect()->route('task')->with('success', 'Task updated successfully!');
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $task = $this->findAccessibleTaskOrFail((int) $id);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

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

        return redirect()->back()->with('success', 'Comment added successfully!');
    }

    public function destroy($id)
    {
        try {
            $task = $this->findAccessibleTaskOrFail((int) $id, ['attachments']);
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
            $task->delete();

            return redirect()->route('task')->with('success', 'Task deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('task')->with('error', 'Failed to delete task: ' . $e->getMessage());
        }
    }

    public function deleteSelected(Request $request)
    {
        $ids = array_filter(explode(',', (string) $request->ids));

        if (empty($ids)) {
            return redirect()->route('task')->with('error', 'No tasks selected for deletion.');
        }

        try {
            foreach ($ids as $id) {
                $task = $this->accessibleTasksQuery()
                    ->with('attachments')
                    ->find($id);

                if ($task) {
                    foreach ($task->attachments as $attachment) {
                        $this->deleteTaskAttachmentFile($attachment);
                        $attachment->delete();
                    }
                    $task->comments()->delete();
                    $task->delete();
                }
            }

            return redirect()->route('task')->with('success', 'Selected tasks deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('task')->with('error', 'Failed to delete selected tasks: ' . $e->getMessage());
        }
    }

    private function accessibleTasksQuery(): Builder
    {
        $query = Task::query();

        if ($this->isPrivilegedTaskUser()) {
            return $query;
        }

        $staffId = $this->authenticatedUserId();

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

    private function authenticatedUserId(): ?int
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
        return !$this->isPrivilegedTaskUser() && $this->authenticatedUserId() !== null;
    }

    private function ensureKanbanViewAccess(): void
    {
        $user = auth()->user();
        abort_unless($user, 403, 'Unauthorized.');

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));
        if ($rawRole === 'client') {
            abort(403, 'Clients have read-only portal access and cannot use task board.');
        }

        if ($this->isPrivilegedTaskUser()) {
            return;
        }

        abort_unless(method_exists($user, 'can') && $user->can('view_tasks'), 403, 'You do not have permission to view tasks.');
    }

    private function ensureKanbanMoveAccess(): void
    {
        $user = auth()->user();
        abort_unless($user, 403, 'Unauthorized.');

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));
        if ($rawRole === 'client') {
            abort(403, 'Clients have read-only portal access.');
        }

        if ($this->isPrivilegedTaskUser()) {
            return;
        }

        abort_unless(method_exists($user, 'can') && $user->can('edit_tasks'), 403, 'You do not have permission to move tasks.');
    }

    private function kanbanColumnFromWorkflow(string $workflowStatus): string
    {
        return match ($workflowStatus) {
            'backlog' => 'backlog',
            'todo' => 'todo',
            'in_progress', 'blocked' => 'in_progress',
            'review' => 'review',
            'testing' => 'testing',
            'deployed', 'completed', 'archived' => 'done',
            default => 'backlog',
        };
    }

    private function mapLegacyStatusFromWorkflow(string $workflowStatus): string
    {
        return match ($workflowStatus) {
            'backlog', 'todo' => 'not_started',
            'in_progress', 'review', 'testing' => 'in_progress',
            'blocked' => 'on_hold',
            'deployed', 'completed', 'archived' => 'completed',
            default => 'not_started',
        };
    }

    private function recordTaskStatusHistory(int $taskId, ?string $from, string $to, ?string $remarks = null): void
    {
        DB::table('task_status_histories')->insert([
            'task_id' => $taskId,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => auth()->id(),
            'remarks' => $remarks,
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function storeTaskAttachment(int $taskId, $file): void
    {
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('Uploaded file is not valid or is no longer readable.');
        }

        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $baseName) ?: 'attachment';
        $fileName = time() . '_' . $safeBaseName . ($extension ? '.' . $extension : '');
        $directory = public_path('uploads/task_attachments/' . $taskId);
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->move($directory, $fileName);

        TaskAttachment::create([
            'task_id' => $taskId,
            'file_name' => $originalName,
            'file_path' => 'uploads/task_attachments/' . $taskId . '/' . $fileName,
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

            $storageSource = storage_path('app/public/' . $sourceRelativePath);
            $targetDirectory = public_path('uploads/task_attachments/' . $task->id);
            $targetRelativePath = 'uploads/task_attachments/' . $task->id . '/' . basename($sourceRelativePath);
            $targetAbsolutePath = public_path($targetRelativePath);

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            if (file_exists($storageSource) && !file_exists($targetAbsolutePath)) {
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
        $storageFile = storage_path('app/public/' . $storageRelativePath);
        if ($storageRelativePath !== '' && file_exists($storageFile)) {
            unlink($storageFile);
        }
    }
}
