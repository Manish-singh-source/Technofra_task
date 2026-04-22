<?php

namespace App\Http\Controllers;

use App\Mail\ClientInviteMail;
use App\Models\ClientIssue;
use App\Models\ClientIssueTask;
use App\Models\ClientIssueTeamAssignment;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusLog;
use App\Models\Staff;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $customers = Customer::with('user')->latest()->get();

        return view('clients', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        $roles = Role::all();

        return view('add-clients', compact('roles'));
    }

    /**
     * Store a newly created customer.
     */
    public function storeclient(Request $request)
    {
        $validator = $this->validateCustomerData($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $payload = $validator->validated();
            $this->createCustomerFromPayload($payload);
            $sendWelcomeEmail = $this->normalizeSendWelcomeEmail($request->input('sendWelcomeEmail', true));

            if ($sendWelcomeEmail) {
                $this->sendClientInviteMail($payload);
            }

            DB::commit();

            $successMessage = $sendWelcomeEmail
                ? 'Customer added successfully. Invitation email sent to ' . $payload['email']
                : 'Customer added successfully. Welcome email was not sent.';

            return redirect()->route('clients')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $customer = Customer::withTrashed()->with([
            'projects.tasks',
            'user.roles',
            'clientIssues' => function ($query) {
                $query->latest();
            },
            'clientIssues.project',
            'clientIssues.teamAssignments.assignedStaff',
        ])->findOrFail($id);
        $roles = Role::all();

        return view('clients-details', compact('customer', 'roles'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->trashed()) {
            return redirect()->back()->with('error', 'Restore this client before updating the record.');
        }

        $validator = $this->validateCustomerData($request, $id, false);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $payload = $validator->validated();
            $oldEmail = $customer->email;
            $oldRole = $customer->role;

            $customer->update($this->buildCustomerData($payload, false));
            $this->syncUserForCustomerUpdate($customer, $payload, $oldRole, $oldEmail);
            $this->refreshPermissionCache();

            DB::commit();

            return redirect()->back()->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified customer.
     */
    public function delete($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->trashed()) {
            return redirect()->route('clients')->with('error', 'Customer is already deleted.');
        }

        DB::beginTransaction();
        try {
            $this->performCustomerDelete($customer, false);
            DB::commit();

            return redirect()->route('clients')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

    /**
     * Delete selected customers.
     */
    public function deleteSelected(Request $request)
    {
        $ids = array_filter(explode(',', (string) $request->ids));

        if (empty($ids)) {
            return redirect()->route('clients')->with('error', 'No customers selected for deletion.');
        }

        DB::beginTransaction();
        try {
            $customers = Customer::whereIn('id', $ids)->get();
            foreach ($customers as $customer) {
                if (! $customer->trashed()) {
                    $this->performCustomerDelete($customer, false);
                }
            }

            DB::commit();

            return redirect()->route('clients')->with('success', 'Selected customers deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('clients')->with('error', 'Failed to delete selected customers: ' . $e->getMessage());
        }
    }

    /**
     * API: Get all customers.
     */
    public function apiIndex(Request $request)
    {
        $clients = User::withTrashed()->with('address', 'businessDetail')->where('role', 'client')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * API: Show a single customer.
     */
    public function apiShow($id)
    {
        $clientDetail = User::with('services', 'address', 'businessDetail')->where('role', 'client')->find($id);

        if (! $clientDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $clientDetail,
        ]);
    }

    /**
     * API: Store a new customer.
     */
    public function apiStore(Request $request)
    {
        $validator = $this->validateCustomerData($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payload = $validator->validated();
            $customer = $this->createCustomerFromPayload($payload);
            $sendWelcomeEmail = $this->normalizeSendWelcomeEmail($request->input('sendWelcomeEmail', $request->input('send_welcome_email', true)));

            if ($sendWelcomeEmail) {
                $this->sendClientInviteMail($payload);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $sendWelcomeEmail ? 'Customer created successfully. Invitation email sent.' : 'Customer created successfully. Welcome email was not sent.',
                'data' => $this->formatCustomerResource($customer->fresh()->load('user.roles')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Update a customer.
     */
    public function apiUpdate(Request $request, $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Restore this client before updating the record.',
            ], 409);
        }

        $validator = $this->validateCustomerData($request, $id, false);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payload = $validator->validated();
            $oldEmail = $customer->email;
            $oldRole = $customer->role;

            $customer->update($this->buildCustomerData($payload, false));
            $this->syncUserForCustomerUpdate($customer, $payload, $oldRole, $oldEmail);
            $this->refreshPermissionCache();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully.',
                'data' => $this->formatCustomerResource($customer->fresh()->load('user.roles')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Soft delete a customer.
     */
    public function apiDestroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer is already deleted.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $this->performCustomerDelete($customer, false);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Restore a soft deleted customer.
     */
    public function apiRestore($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if (! $customer->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer is already active.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $customer->restore();
            $this->refreshPermissionCache();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer restored successfully.',
                'data' => $this->formatCustomerResource($customer->fresh()->load('user.roles')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore customer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get all projects for a client.
     */
    public function apiClientProjects($customerId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);
        $projects = Project::where('customer_id', $customerId)->with([
            'tasks',
            'milestones',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $projects->map(fn(Project $project) => $this->formatProjectResource($project)),
        ]);
    }

    /**
     * API: Get a single project for a client.
     */
    public function apiClientProjectDetail($customerId, $projectId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);
        $project = Project::where('customer_id', $customerId)->where('id', $projectId)->with([
            'tasks.attachments',
            'tasks.comments',
            'milestones',
            'statusLogs',
        ])->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->formatProjectResource($project, true),
        ]);
    }

    /**
     * API: Get all tasks for a client (from all projects).
     */
    public function apiClientTasks($customerId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);
        $projectIds = Project::where('customer_id', $customerId)->pluck('id');

        $tasks = Task::whereIn('project_id', $projectIds)->with([
            'project',
            'attachments',
            'comments',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $tasks->map(fn(Task $task) => $this->formatTaskResource($task)),
        ]);
    }

    /**
     * API: Get a single task for a client.
     */
    public function apiClientTaskDetail($customerId, $taskId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);
        $projectIds = Project::where('customer_id', $customerId)->pluck('id');

        $task = Task::whereIn('project_id', $projectIds)->where('id', $taskId)->with([
            'project',
            'attachments',
            'comments',
        ])->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->formatTaskResource($task, true),
        ]);
    }

    /**
     * API: Get all issues for a client.
     */
    public function apiClientIssues($customerId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);

        $issues = ClientIssue::where('customer_id', $customerId)->with([
            'project',
            'tasks',
            'teamAssignments.assignedStaff',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $issues->map(fn(ClientIssue $issue) => $this->formatClientIssueResource($issue)),
        ]);
    }

    /**
     * API: Get a single issue for a client.
     */
    public function apiClientIssueDetail($customerId, $issueId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);

        $issue = ClientIssue::where('customer_id', $customerId)->where('id', $issueId)->with([
            'project',
            'tasks',
            'teamAssignments.assignedStaff',
        ])->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->formatClientIssueResource($issue, true),
        ]);
    }

    private function formatProjectResource(Project $project, bool $detailed = false): array
    {
        $data = [
            'id' => $project->id,
            'customer_id' => $project->customer_id,
            'project_name' => $project->project_name,
            'status' => $project->status,
            'start_date' => optional($project->start_date)?->toISOString(),
            'deadline' => optional($project->deadline)?->toISOString(),
            'billing_type' => $project->billing_type,
            'total_rate' => $project->total_rate,
            'estimated_hours' => $project->estimated_hours,
            'tags' => $project->tags,
            'priority' => $project->priority,
            'technologies' => $project->technologies,
            'description' => $project->description,
            'created_at' => optional($project->created_at)?->toISOString(),
            'updated_at' => optional($project->updated_at)?->toISOString(),
        ];

        if ($detailed) {
            $data['tasks'] = $project->tasks->map(fn(Task $task) => $this->formatTaskResource($task));
            $data['staff_members'] = $project->membersList()->map(fn(Staff $staff) => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
            ]);
            $data['milestones'] = $project->milestones->map(fn(ProjectMilestone $milestone) => [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'status' => $milestone->status,
                'due_date' => optional($milestone->due_date)?->toISOString(),
            ]);
            $data['status_logs'] = $project->statusLogs->map(fn(ProjectStatusLog $log) => [
                'id' => $log->id,
                'old_status' => $log->old_status,
                'new_status' => $log->new_status,
                'created_at' => optional($log->created_at)?->toISOString(),
            ]);
        }

        return $data;
    }

    private function formatTaskResource(Task $task, bool $detailed = false): array
    {
        $data = [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'tags' => $task->tags,
            'start_date' => optional($task->start_date)?->toISOString(),
            'deadline' => optional($task->deadline)?->toISOString(),
            'created_at' => optional($task->created_at)?->toISOString(),
            'updated_at' => optional($task->updated_at)?->toISOString(),
        ];

        if ($detailed) {
            $data['project'] = $task->project ? [
                'id' => $task->project->id,
                'project_name' => $task->project->project_name,
            ] : null;
            $data['attachments'] = $task->attachments->map(fn(TaskAttachment $attachment) => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
            ]);
            $data['comments'] = $task->comments->map(fn(TaskComment $comment) => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user' => $comment->user ? [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ] : null,
                'created_at' => optional($comment->created_at)?->toISOString(),
            ]);
        }

        return $data;
    }

    private function formatClientIssueResource(ClientIssue $issue, bool $detailed = false): array
    {
        $data = [
            'id' => $issue->id,
            'project_id' => $issue->project_id,
            'customer_id' => $issue->customer_id,
            'issue_description' => $issue->issue_description,
            'priority' => $issue->priority,
            'status' => $issue->status,
            'created_at' => optional($issue->created_at)?->toISOString(),
            'updated_at' => optional($issue->updated_at)?->toISOString(),
        ];

        if ($detailed) {
            $data['project'] = $issue->project ? [
                'id' => $issue->project->id,
                'project_name' => $issue->project->project_name,
            ] : null;
            $data['tasks'] = $issue->tasks->map(fn(ClientIssueTask $task) => [
                'id' => $task->id,
                'task_description' => $task->task_description,
                'status' => $task->status,
            ]);
            $data['team_assignments'] = $issue->teamAssignments->map(fn(ClientIssueTeamAssignment $assignment) => [
                'id' => $assignment->id,
                'assigned_staff' => $assignment->assignedStaff ? [
                    'id' => $assignment->assignedStaff->id,
                    'name' => $assignment->assignedStaff->name,
                    'email' => $assignment->assignedStaff->email,
                ] : null,
                'assigned_at' => optional($assignment->created_at)?->toISOString(),
            ]);
        }

        return $data;
    }

    /**
     * API: Permanently delete a customer.
     */
    public function apiForceDelete($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            $this->performCustomerDelete($customer, true);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer permanently deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete customer: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateCustomerData(Request $request, int|string|null $id = null, bool $requirePassword = true)
    {
        $passwordRule = $requirePassword ? 'required|string|min:8' : 'nullable|string|min:8';
        $customerEmailRule = 'unique:customers,email' . ($id ? ',' . $id : '');
        $userEmailRule = 'unique:users,email';

        if ($id) {
            $existingCustomer = Customer::withTrashed()->find($id);
            $ignoreUserId = $existingCustomer?->user_id;

            if (! $ignoreUserId && $existingCustomer?->email) {
                $ignoreUserId = User::where('email', $existingCustomer->email)->value('id');
            }

            if ($ignoreUserId) {
                $userEmailRule .= ',' . $ignoreUserId;
            }
        }

        return Validator::make($request->all(), [
            'client_name' => 'required|string|min:3|max:255',
            'contact_person' => 'required|string|min:3|max:255',
            'email' => ['required', 'email', $customerEmailRule, $userEmailRule],
            'phone' => 'nullable|string|min:10|max:20',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:50',
            'country' => 'required|string|max:255',
            'client_type' => 'required|in:Individual,Company,Organization',
            'industry' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive,Suspended',
            'priority_level' => 'nullable|in:Low,Medium,High',
            'assigned_manager_id' => 'nullable|integer',
            'default_due_days' => 'nullable|integer',
            'billing_type' => 'nullable|in:Hourly,Fixed,Retainer',
            'role' => 'nullable|string|max:255',
            'password' => $passwordRule,
        ], [], [
            'client_name' => 'client name',
            'contact_person' => 'contact person',
            'address_line1' => 'address line 1',
            'address_line2' => 'address line 2',
            'postal_code' => 'postal code',
            'client_type' => 'client type',
            'priority_level' => 'priority level',
            'assigned_manager_id' => 'assigned manager',
            'default_due_days' => 'default due days',
            'billing_type' => 'billing type',
        ]);
    }

    private function createCustomerFromPayload(array $payload): Customer
    {
        $user = User::create([
            'name' => $payload['client_name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'role' => $payload['role'] ?? 'customer',
        ]);

        $roleName = $payload['role'] ?? 'customer';
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        $customer = Customer::create($this->buildCustomerData(array_merge($payload, [
            'user_id' => $user->id,
            'role' => $roleName,
            'password' => Hash::make($payload['password']),
        ]), true));

        $this->refreshPermissionCache();

        return $customer->load('user.roles');
    }

    private function buildCustomerData(array $payload, bool $includePassword = true): array
    {
        $data = [
            'user_id' => $payload['user_id'] ?? null,
            'client_name' => $payload['client_name'],
            'contact_person' => $payload['contact_person'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'website' => $payload['website'] ?? null,
            'address_line1' => $payload['address_line1'],
            'address_line2' => $payload['address_line2'] ?? null,
            'city' => $payload['city'],
            'state' => $payload['state'],
            'postal_code' => $payload['postal_code'],
            'country' => $payload['country'],
            'client_type' => $payload['client_type'],
            'industry' => $payload['industry'],
            'status' => $payload['status'],
            'priority_level' => $payload['priority_level'] ?? null,
            'assigned_manager_id' => $payload['assigned_manager_id'] ?? null,
            'default_due_days' => $payload['default_due_days'] ?? null,
            'billing_type' => $payload['billing_type'] ?? null,
            'role' => $payload['role'] ?? 'customer',
        ];

        if ($includePassword && array_key_exists('password', $payload)) {
            $data['password'] = $payload['password'];
        }

        return $data;
    }

    private function syncUserForCustomerUpdate(Customer $customer, array $payload, ?string $oldRole, string $oldEmail): void
    {
        $user = $customer->user ?? User::where('email', $oldEmail)->first();
        if (! $user) {
            return;
        }

        $user->update([
            'name' => $payload['client_name'],
            'email' => $payload['email'],
            'role' => $payload['role'] ?? $customer->role ?? 'customer',
        ]);

        $newRoleName = $payload['role'] ?? $customer->role ?? 'customer';
        if ($oldRole !== $newRoleName) {
            if ($oldRole && $user->hasRole($oldRole)) {
                $user->removeRole($oldRole);
            }

            $newRole = Role::firstOrCreate(['name' => $newRoleName]);
            $user->assignRole($newRole);
        }
    }

    private function performCustomerDelete(Customer $customer, bool $forceDelete = false): void
    {
        if ($forceDelete) {
            $user = $customer->user;
            if (! $user && $customer->email) {
                $user = User::where('email', $customer->email)->first();
            }

            $customer->forceDelete();

            if ($user) {
                $user->delete();
            }

            $this->refreshPermissionCache();

            return;
        }

        $customer->delete();
    }

    private function sendClientInviteMail(array $payload): void
    {
        $clientName = $payload['contact_person'] ?: $payload['client_name'];

        try {
            Mail::to($payload['email'])->send(new ClientInviteMail($clientName, $payload['email'], $payload['password']));
        } catch (\Exception $mailException) {
            Log::error('Failed to send client invitation email: ' . $mailException->getMessage());
        }
    }

    private function normalizeSendWelcomeEmail(mixed $value): bool
    {
        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? true;
    }

    private function refreshPermissionCache(): void
    {
        Cache::forget('spatie.permission.cache');
    }

    private function formatCustomerResource(Customer $customer): array
    {
        $customer->loadMissing('user.roles');

        return [
            'id' => $customer->id,
            'user_id' => $customer->user_id,
            'client_name' => $customer->client_name,
            'contact_person' => $customer->contact_person,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'website' => $customer->website,
            'address_line1' => $customer->address_line1,
            'address_line2' => $customer->address_line2,
            'city' => $customer->city,
            'state' => $customer->state,
            'postal_code' => $customer->postal_code,
            'country' => $customer->country,
            'client_type' => $customer->client_type,
            'industry' => $customer->industry,
            'status' => $customer->status,
            'priority_level' => $customer->priority_level,
            'assigned_manager_id' => $customer->assigned_manager_id,
            'default_due_days' => $customer->default_due_days,
            'billing_type' => $customer->billing_type,
            'role' => $customer->role,
            'is_deleted' => $customer->trashed(),
            'deleted_at' => optional($customer->deleted_at)?->toISOString(),
            'created_at' => optional($customer->created_at)?->toISOString(),
            'updated_at' => optional($customer->updated_at)?->toISOString(),
            'user' => $customer->user ? [
                'id' => $customer->user->id,
                'name' => $customer->user->name,
                'email' => $customer->user->email,
                'roles' => $customer->user->roles->pluck('name')->values(),
            ] : null,
            'links' => [
                'web' => [
                    'view' => route('clients-details', $customer->id),
                    'update' => route('clients.update', $customer->id),
                    'delete' => route('clients.delete', $customer->id),
                ],
                'api' => [
                    'show' => url('/api/clients/' . $customer->id),
                    'update' => url('/api/clients/' . $customer->id),
                    'delete' => url('/api/clients/' . $customer->id),
                    'restore' => url('/api/clients/' . $customer->id . '/restore'),
                    'force_delete' => url('/api/clients/' . $customer->id . '/force'),
                ],
            ],
        ];
    }
}
