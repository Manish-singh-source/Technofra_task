<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
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
            'priority' => 'nullable|in:High,Medium,Low',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
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

        $task = Task::create([
            'title' => $request->task_title,
            'description' => $request->task_description,
            'project_id' => $request->project_related,
            'followers' => $request->followers,
            'assignees' => $request->assignees,
            'tags' => $tags,
            'status' => $request->status ?? 'not_started',
            'priority' => $priority,
            'start_date' => $request->start_date,
            'deadline' => $request->due_date,
        ]);

        if ($request->hasFile('attach_files')) {
            foreach ($request->file('attach_files') as $file) {
                $this->storeTaskAttachment($task->id, $file);
            }
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
            'attach_files' => 'nullable|array',
            'attach_files.*' => 'file|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $priority = strtolower($request->priority ?? 'medium');
        $tags = $request->tags ?? [];

        $task->update([
            'title' => $request->task_title,
            'description' => $request->task_description,
            'project_id' => $request->project_related,
            'followers' => $request->followers,
            'assignees' => $request->assignees,
            'tags' => $tags,
            'status' => $request->status ?? 'not_started',
            'priority' => $priority,
            'start_date' => $request->start_date,
            'deadline' => $request->due_date,
        ]);

        if ($request->hasFile('attach_files')) {
            foreach ($request->file('attach_files') as $file) {
                $this->storeTaskAttachment($task->id, $file);
            }
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

    private function authenticatedUserId(): ?int
    {
        $user = auth()->user();
        if (!$user || !$user->isStaff()) {
            return null;
        }

        return $user->id;
    }

    private function shouldRestrictToAssignedTasks(): bool
    {
        return !$this->isPrivilegedTaskUser() && $this->authenticatedUserId() !== null;
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
