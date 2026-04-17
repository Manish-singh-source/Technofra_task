<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClientIssue;
use App\Models\ClientIssueTask;
use App\Models\ClientIssueTeamAssignment;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientIssueController extends Controller
{
    private const ISSUE_PRIORITIES = ['low', 'medium', 'high', 'critical'];
    private const ISSUE_STATUSES = ['open', 'in_progress', 'resolved', 'closed'];
    private const TASK_PRIORITIES = ['low', 'medium', 'high', 'critical'];
    private const TASK_STATUSES = ['todo', 'in_progress', 'review', 'done'];

    public function formOptions(): JsonResponse
    {
        $user = Auth::user();
        $customer = $this->getLoggedInCustomer();
        if (! $customer && ! $this->userHasAnyPermission($user, ['view_raise_issue', 'create_raise_issue', 'edit_raise_issue'])) {
            return ApiResponse::error('You are not authorized to perform this action.', null, 403);
        }
        $projects = $customer ? Project::query()->where('customer_id', $customer->id)->orderBy('project_name')->get() : Project::query()->with('customer')->orderBy('project_name')->get();
        $customers = $customer ? collect([$customer]) : Customer::query()->orderBy('client_name')->get();
        return ApiResponse::success([
            'projects' => $projects->map(fn (Project $project) => $this->projectResource($project))->values(),
            'customers' => $customers->map(fn (Customer $item) => $this->customerResource($item))->values(),
            'teams' => Team::getTeamCards(),
            'team_options' => Team::getTeamOptions(),
            'issue_priorities' => self::ISSUE_PRIORITIES,
            'issue_statuses' => self::ISSUE_STATUSES,
            'task_priorities' => self::TASK_PRIORITIES,
            'task_statuses' => self::TASK_STATUSES,
        ], 'Client issue form options retrieved successfully.');
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            return ApiResponse::success([
                'issues' => [],
                'projects' => Project::query()->where('customer_id', $customer->id)->orderBy('project_name')->get()->map(fn (Project $project) => $this->projectResource($project))->values(),
                'customers' => [$this->customerResource($customer)],
            ], 'Client issues retrieved successfully.');
        }
        if (! $this->userHasPermission($user, 'view_raise_issue')) {
            return ApiResponse::error('You are not authorized to perform this action.', null, 403);
        }
        $issues = ClientIssue::query()->with(['project.customer', 'customer', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])->latest('created_at')->get();
        if ($user && $user->isStaff()) {
            $staff = $user->staff;
            $issues = $issues->filter(fn (ClientIssue $issue) => $this->staffCanAccessIssue($issue, optional($staff)->id, trim((string) optional($staff)->team)))->values();
        }
        return ApiResponse::success([
            'issues' => $issues->map(fn (ClientIssue $issue) => $this->issueResource($issue))->values(),
            'projects' => Project::query()->with('customer')->orderBy('project_name')->get()->map(fn (Project $project) => $this->projectResource($project))->values(),
            'customers' => Customer::query()->orderBy('client_name')->get()->map(fn (Customer $item) => $this->customerResource($item))->values(),
        ], 'Client issues retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        if (($response = $this->authorizeIssueWrite('create_raise_issue', true, $request->input('project_id'), $request->input('customer_id'))) !== null) {
            return $response;
        }
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'customer_id' => 'required|exists:customers,id',
            'issue_description' => 'required|string',
            'priority' => ['nullable', Rule::in(self::ISSUE_PRIORITIES)],
            'status' => ['nullable', Rule::in(self::ISSUE_STATUSES)],
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }
        try {
            $issue = ClientIssue::query()->create([
                'project_id' => $request->input('project_id'),
                'customer_id' => $request->input('customer_id'),
                'issue_description' => $request->input('issue_description'),
                'priority' => $request->input('priority', 'medium'),
                'status' => $request->input('status', 'open'),
            ]);
            return ApiResponse::success($this->issueDetailResource($issue->fresh(['project.customer', 'customer', 'tasks', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])), 'Client issue created successfully.', 201);
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to create client issue.', ['server' => [$exception->getMessage()]], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer', 'tasks', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])->find($id);
        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }
        if (($response = $this->authorizeIssueAccess($issue, 'view_raise_issue', true)) !== null) {
            return $response;
        }
        return ApiResponse::success(['issue' => $this->issueDetailResource($issue), 'teams' => Team::getTeamCards()], 'Client issue retrieved successfully.');
    }

    public function assignTeam(Request $request, int $clientIssue): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])->find($clientIssue);

        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }

        if (($response = $this->authorizeIssueAccess($issue, 'edit_raise_issue', true)) !== null) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'team_name' => ['required', Rule::in(Team::getTeamOptions())],
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $assignment = ClientIssueTeamAssignment::query()->create([
                'client_issue_id' => $issue->id,
                'team_name' => $request->input('team_name'),
                'assigned_to' => optional(Auth::user()->staff)->id ? (string) optional(Auth::user()->staff)->id : null,
                'note' => $request->input('note'),
                'assigned_by' => Auth::id(),
            ]);

            return ApiResponse::success([
                'assignment' => $this->assignmentResource($assignment->fresh(['assignedBy', 'assignedStaff'])),
                'issue' => $this->issueResource($issue->fresh(['project.customer', 'customer', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])),
            ], 'Issue assigned successfully.', 201);
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to assign issue.', ['server' => [$exception->getMessage()]], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer', 'tasks', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])->find($id);

        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }

        if (($response = $this->authorizeIssueAccess($issue, 'edit_raise_issue', true)) !== null) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(self::ISSUE_STATUSES)],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        $issue->update(['status' => $request->input('status')]);

        return ApiResponse::success($this->issueDetailResource($issue->fresh(['project.customer', 'customer', 'tasks', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy'])), 'Client issue status updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'tasks', 'teamAssignments'])->find($id);

        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }

        if (($response = $this->authorizeIssueAccess($issue, 'delete_raise_issue', true)) !== null) {
            return $response;
        }

        DB::beginTransaction();

        try {
            foreach ($issue->tasks as $task) {
                $this->deleteTaskAttachments($task);
                $task->delete();
            }

            $issue->teamAssignments()->delete();
            $issue->delete();
            DB::commit();

            return ApiResponse::success(['id' => $id], 'Client issue deleted successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete client issue.', ['server' => [$exception->getMessage()]], 500);
        }
    }

    public function deleteSelected(Request $request): JsonResponse
    {
        if (($response = $this->authorizePermission('delete_raise_issue')) !== null) {
            return $response;
        }

        $ids = $this->normalizeIds($request->input('ids'));
        if ($ids === []) {
            return ApiResponse::error('Validation failed.', ['ids' => ['The ids field is required and must contain at least one valid client issue id.']], 422);
        }

        $issues = ClientIssue::query()->with(['project.customer', 'tasks', 'teamAssignments'])->whereIn('id', $ids)->get();
        if ($issues->isEmpty()) {
            return ApiResponse::error('No matching client issues found.', null, 404);
        }

        if (Auth::user()->isStaff()) {
            $staff = Auth::user()->staff;
            $unauthorized = $issues->reject(fn (ClientIssue $issue) => $this->staffCanAccessIssue($issue, optional($staff)->id, trim((string) optional($staff)->team)))->pluck('id')->values();
            if ($unauthorized->isNotEmpty()) {
                return ApiResponse::error('You are not authorized to delete one or more selected issues.', ['unauthorized_ids' => $unauthorized], 403);
            }
        }
        DB::beginTransaction();
        try {
            foreach ($issues as $issue) {
                foreach ($issue->tasks as $task) {
                    $this->deleteTaskAttachments($task);
                    $task->delete();
                }
                $issue->teamAssignments()->delete();
                $issue->delete();
            }
            DB::commit();
            return ApiResponse::success(['deleted_count' => $issues->count(), 'deleted_ids' => $issues->pluck('id')->values()], 'Selected client issues deleted successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete selected client issues.', ['server' => [$exception->getMessage()]], 500);
        }
    }

    public function taskShow(int $clientIssue, int $task): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer'])->find($clientIssue);
        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }
        if (($response = $this->authorizeTaskAccess($issue, 'view_raise_issue')) !== null) {
            return $response;
        }
        $taskModel = ClientIssueTask::query()->where('client_issue_id', $clientIssue)->find($task);
        if (! $taskModel) {
            return ApiResponse::error('Task not found.', null, 404);
        }
        return ApiResponse::success(['issue' => $this->issueResource($issue), 'task' => $this->taskResource($taskModel)], 'Task retrieved successfully.');
    }

    public function taskStore(Request $request, int $clientIssue): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer'])->find($clientIssue);
        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }
        if (($response = $this->authorizeTaskAccess($issue, 'create_raise_issue')) !== null) {
            return $response;
        }
        $validator = Validator::make($request->all(), $this->taskRules());
        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $attachments = $this->storeUploadedAttachments($request);
            $task = ClientIssueTask::query()->create([
                'client_issue_id' => $issue->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => $request->input('status', 'todo'),
                'priority' => $request->input('priority', 'medium'),
                'assigned_to' => $request->input('assigned_to'),
                'start_date' => $request->input('start_date'),
                'due_date' => $request->input('due_date'),
                'due_time' => $request->input('due_time'),
                'reminder_date' => $request->input('reminder_date'),
                'reminder_time' => $request->input('reminder_time'),
                'checklist_data' => $this->normalizeJsonArray($request->input('checklist_data')),
                'labels_data' => $this->normalizeJsonArray($request->input('labels_data')),
                'attachment' => $attachments[0]['path'] ?? null,
                'attachments' => $attachments,
            ]);

            return ApiResponse::success(['issue' => $this->issueResource($issue), 'task' => $this->taskResource($task)], 'Task created successfully.', 201);
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to create task.', ['server' => [$exception->getMessage()]], 500);
        }
    }

    public function taskUpdate(Request $request, int $clientIssue, int $task): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer'])->find($clientIssue);
        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }
        if (($response = $this->authorizeTaskAccess($issue, 'edit_raise_issue')) !== null) {
            return $response;
        }
        $taskModel = ClientIssueTask::query()->where('client_issue_id', $clientIssue)->find($task);
        if (! $taskModel) {
            return ApiResponse::error('Task not found.', null, 404);
        }
        $validator = Validator::make($request->all(), $this->taskRules());
        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        $attachments = is_array($taskModel->attachments) ? $taskModel->attachments : [];
        if ($request->hasFile('attachments')) {
            $this->deleteTaskAttachments($taskModel);
            $attachments = $this->storeUploadedAttachments($request);
        }
        $taskModel->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status', $taskModel->status),
            'priority' => $request->input('priority', $taskModel->priority),
            'assigned_to' => $request->input('assigned_to'),
            'start_date' => $request->input('start_date'),
            'due_date' => $request->input('due_date'),
            'due_time' => $request->input('due_time'),
            'reminder_date' => $request->input('reminder_date'),
            'reminder_time' => $request->input('reminder_time'),
            'checklist_data' => $this->normalizeJsonArray($request->input('checklist_data')),
            'labels_data' => $this->normalizeJsonArray($request->input('labels_data')),
            'attachment' => $attachments[0]['path'] ?? $taskModel->attachment,
            'attachments' => $attachments,
        ]);

        return ApiResponse::success(['issue' => $this->issueResource($issue), 'task' => $this->taskResource($taskModel->fresh())], 'Task updated successfully.');
    }

    public function taskUpdateStatus(Request $request, int $clientIssue, int $task): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer'])->find($clientIssue);
        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }
        if (($response = $this->authorizeTaskAccess($issue, 'edit_raise_issue')) !== null) {
            return $response;
        }
        $taskModel = ClientIssueTask::query()->where('client_issue_id', $clientIssue)->find($task);
        if (! $taskModel) {
            return ApiResponse::error('Task not found.', null, 404);
        }
        $validator = Validator::make($request->all(), ['status' => ['required', Rule::in(self::TASK_STATUSES)]]);
        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }
        $taskModel->update(['status' => $request->input('status')]);
        return ApiResponse::success($this->taskResource($taskModel->fresh()), 'Task status updated successfully.');
    }

    public function taskDestroy(int $clientIssue, int $task): JsonResponse
    {
        $issue = ClientIssue::query()->with(['project.customer', 'customer'])->find($clientIssue);
        if (! $issue) {
            return ApiResponse::error('Client issue not found.', null, 404);
        }
        if (($response = $this->authorizeTaskAccess($issue, 'delete_raise_issue')) !== null) {
            return $response;
        }
        $taskModel = ClientIssueTask::query()->where('client_issue_id', $clientIssue)->find($task);
        if (! $taskModel) {
            return ApiResponse::error('Task not found.', null, 404);
        }
        $this->deleteTaskAttachments($taskModel);
        $taskModel->delete();
        return ApiResponse::success(['id' => $task, 'client_issue_id' => $clientIssue], 'Task deleted successfully.');
    }

    private function authorizeIssueWrite(string $permission, bool $allowCustomer, $projectId, $customerId): ?JsonResponse
    {
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            if (! $allowCustomer) {
                return ApiResponse::error('You are not authorized to perform this action.', null, 403);
            }
            $project = Project::query()->find($projectId);
            if (! $project || (int) $project->customer_id !== (int) $customer->id || (int) $customerId !== (int) $customer->id) {
                return ApiResponse::error('You are not authorized to perform this action.', null, 403);
            }
            return null;
        }
        return $this->authorizePermission($permission);
    }

    private function authorizeIssueAccess(ClientIssue $issue, string $permission, bool $blockCustomer): ?JsonResponse
    {
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            if ($blockCustomer || (int) $issue->project?->customer_id !== (int) $customer->id) {
                return ApiResponse::error('You are not authorized to perform this action.', null, 403);
            }
            return null;
        }
        if (($response = $this->authorizePermission($permission)) !== null) {
            return $response;
        }
        if (Auth::user()?->isStaff() && ! $this->staffCanAccessIssue($issue, optional(Auth::user()->staff)->id, trim((string) optional(Auth::user()->staff)->team))) {
            return ApiResponse::error('You are not authorized to access this issue.', null, 403);
        }
        return null;
    }

    private function authorizeTaskAccess(ClientIssue $issue, string $permission): ?JsonResponse
    {
        return $this->authorizeIssueAccess($issue, $permission, false);
    }

    private function authorizePermission(string $permission): ?JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated.', null, 401);
        }
        if ($this->userHasPermission($user, $permission)) {
            return null;
        }
        return ApiResponse::error('You are not authorized to perform this action.', null, 403);
    }
    private function getLoggedInCustomer(): ?Customer
    {
        $user = Auth::user();
        return ($user && $user->hasRole('customer')) ? (Customer::query()->where('email', $user->email)->first()) : null;
    }

    private function userHasPermission(?User $user, string $permission): bool
    {
        return (bool) ($user && ($user->hasAnyRole(['super-admin', 'super_admin', 'admin']) || $user->can($permission)));
    }

    private function userHasAnyPermission(?User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->userHasPermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    private function staffCanAccessIssue(ClientIssue $issue, $staffId, string $staffTeam): bool
    {
        if (! $staffId) {
            return false;
        }
        $latest = $issue->teamAssignments->sortByDesc('id')->first();
        if (! $latest) {
            return false;
        }
        if (! empty($latest->assigned_to) && (int) $latest->assigned_to === (int) $staffId) {
            return true;
        }
        return $staffTeam !== '' && strcasecmp((string) $latest->team_name, $staffTeam) === 0;
    }

    private function normalizeIds($ids): array
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        if (! is_array($ids)) {
            return [];
        }
        return collect($ids)->map(fn ($id) => (int) $id)->filter(fn (int $id) => $id > 0)->unique()->values()->all();
    }

    private function taskRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::in(self::TASK_STATUSES)],
            'priority' => ['nullable', Rule::in(self::TASK_PRIORITIES)],
            'assigned_to' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable',
            'reminder_date' => 'nullable|date',
            'reminder_time' => 'nullable',
            'checklist_data' => 'nullable',
            'labels_data' => 'nullable',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ];
    }

    private function normalizeJsonArray($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function storeUploadedAttachments(Request $request): array
    {
        $attachments = [];
        foreach ((array) $request->file('attachments', []) as $file) {
            if ($file && $file->isValid()) {
                $attachments[] = ['path' => $file->store('task-attachments', 'public'), 'name' => $file->getClientOriginalName()];
            }
        }
        return $attachments;
    }

    private function deleteTaskAttachments(ClientIssueTask $task): void
    {
        foreach ((array) ($task->attachments ?? []) as $item) {
            $path = is_array($item) ? ($item['path'] ?? null) : $item;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        if ($task->attachment && Storage::disk('public')->exists($task->attachment)) {
            Storage::disk('public')->delete($task->attachment);
        }
    }

    private function issueResource(ClientIssue $issue): array
    {
        $issue->loadMissing(['project.customer', 'customer', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy']);
        return [
            'id' => $issue->id,
            'project' => $issue->project ? $this->projectResource($issue->project) : null,
            'customer' => $issue->customer ? $this->customerResource($issue->customer) : null,
            'issue_description' => $issue->issue_description,
            'priority' => $issue->priority,
            'status' => $issue->status,
            'latest_assignment' => ($issue->teamAssignments->sortByDesc('id')->first()) ? $this->assignmentResource($issue->teamAssignments->sortByDesc('id')->first()) : null,
            'created_at' => optional($issue->created_at)->toISOString(),
            'updated_at' => optional($issue->updated_at)->toISOString(),
        ];
    }

    private function issueDetailResource(ClientIssue $issue): array
    {
        $issue->loadMissing(['project.customer', 'customer', 'tasks', 'teamAssignments.assignedStaff', 'teamAssignments.assignedBy']);
        $data = $this->issueResource($issue);
        $data['tasks'] = $issue->tasks->map(fn (ClientIssueTask $task) => $this->taskResource($task))->values();
        $data['team_assignments'] = $issue->teamAssignments->sortByDesc('id')->values()->map(fn (ClientIssueTeamAssignment $assignment) => $this->assignmentResource($assignment))->values();
        return $data;
    }

    private function taskResource(ClientIssueTask $task): array
    {
        return [
            'id' => $task->id,
            'client_issue_id' => $task->client_issue_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'assigned_to' => $task->assigned_to,
            'start_date' => optional($task->start_date)->toDateString(),
            'due_date' => optional($task->due_date)->toDateString(),
            'attachments' => collect((array) ($task->attachments ?? []))->values(),
            'created_at' => optional($task->created_at)->toISOString(),
            'updated_at' => optional($task->updated_at)->toISOString(),
        ];
    }

    private function assignmentResource(ClientIssueTeamAssignment $assignment): array
    {
        $assignment->loadMissing(['assignedBy', 'assignedStaff']);
        return [
            'id' => $assignment->id,
            'team_name' => $assignment->team_name,
            'note' => $assignment->note,
            'assigned_to' => $assignment->assigned_to,
            'assigned_staff' => $assignment->assignedStaff ? ['id' => $assignment->assignedStaff->id, 'name' => trim((string) $assignment->assignedStaff->full_name), 'team' => $assignment->assignedStaff->team] : null,
            'assigned_by_user' => $assignment->assignedBy ? ['id' => $assignment->assignedBy->id, 'name' => $assignment->assignedBy->name] : null,
            'created_at' => optional($assignment->created_at)->toISOString(),
        ];
    }

    private function projectResource(Project $project): array
    {
        $project->loadMissing('customer');
        return ['id' => $project->id, 'project_name' => $project->project_name, 'customer_id' => $project->customer_id, 'customer_name' => $project->customer?->client_name, 'status' => $project->status];
    }

    private function customerResource(Customer $customer): array
    {
        return ['id' => $customer->id, 'client_name' => $customer->client_name, 'contact_person' => $customer->contact_person, 'email' => $customer->email, 'phone' => $customer->phone, 'status' => $customer->status];
    }
}
