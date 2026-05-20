<?php

namespace App\Http\Controllers;

use App\Models\ClientIssue;
use App\Models\ClientIssueTask;
use App\Models\ClientIssueTeamAssignment;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientIssueController extends Controller
{
    /**
     * Get the logged-in client user
     */
    private function getLoggedInClient()
    {
        $user = Auth::user();
        $rawRole = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));

        if ($user && $rawRole === 'client') {
            return $user;
        }

        return null;
    }

    /**
     * Check if current user is a client
     */
    private function isClient()
    {
        $user = Auth::user();
        $rawRole = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));

        return (bool) ($user && $rawRole === 'client');
    }

    private function isPrivilegedIssueUser(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2'])) {
            return true;
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));

        return in_array($rawRole, ['admin', 'super-admin', 'super_admin', 'super_admin2'], true);
    }

    private function clientCanAccessIssue(ClientIssue $clientIssue, User $clientUser): bool
    {
        return (int) $clientIssue->customer_id === (int) $clientUser->id
            || (int) optional($clientIssue->project)->customer_id === (int) $clientUser->id;
    }

    private function authorizeIssueAccess(ClientIssue $clientIssue, string $message = 'You are not authorized to view this issue.'): void
    {
        if ($this->isPrivilegedIssueUser()) {
            return;
        }

        $clientUser = $this->getLoggedInClient();
        if ($clientUser) {
            abort_if(! $this->clientCanAccessIssue($clientIssue, $clientUser), 403, $message);

            return;
        }

        $user = Auth::user();
        $rawRole = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));
        if ($user && $rawRole === 'staff') {
            $staff = $user;
            $staffId = optional($staff)->id;
            $staffTeam = trim((string) optional($staff)->team);
            abort_if(! $this->staffCanAccessIssue($clientIssue, $staffId, $staffTeam), 403, $message);
        }
    }

    /**
     * Display a listing of client issues.
     */
    public function index()
    {
        $clientUser = $this->getLoggedInClient();
        $isClient = $this->isClient();

        if ($clientUser) {
            // Client users should see their own issues only
            $clientIssues = ClientIssue::with(['project', 'customer', 'teamAssignments.assignedStaff'])
                ->where('customer_id', $clientUser->id)
                ->get();
            $projects = Project::where('customer_id', $clientUser->id)->get();
            $clientUsers = collect([$clientUser]);
            // Auto-select project if only one exists
            $selectedProjectId = $projects->count() === 1 ? $projects->first()->id : null;
        } else {
            $user = Auth::user();
            if ($user && $user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2'])) {
                // Super-admin/Admin can see all issues
                $clientIssues = ClientIssue::with(['project', 'customer', 'teamAssignments.assignedStaff'])->get();
            } elseif ($user && strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? '')) === 'staff') {
                // Staff can see assigned issues only
                $staff = $user;
                $staffId = optional($staff)->id;
                $staffTeam = trim((string) optional($staff)->team);

                $clientIssues = ClientIssue::with(['project', 'customer', 'teamAssignments.assignedStaff'])
                    ->get();
                $clientIssues = $clientIssues
                    ->filter(fn ($issue) => $this->staffCanAccessIssue($issue, $staffId, $staffTeam))
                    ->values();
            } else {
                $clientIssues = collect();
            }
            // Admin/Staff can see all projects and client users
            $projects = Project::all();
            $clientUsers = User::where('role', 'client')->get();
            $selectedProjectId = null;
        }

        return view('client-issue', compact('clientIssues', 'projects', 'clientUsers', 'selectedProjectId', 'isClient'));
    }

    /**
     * Store a newly created client issue in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'customer_id' => 'required|exists:users,id',
            'issue_description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $project = Project::findOrFail($request->project_id);

        if ((int) $project->customer_id !== (int) $request->customer_id) {
            return redirect()->back()->with('error', 'Selected client does not belong to this project.')->withInput();
        }

        // If user is a client, verify they own this project
        $clientUser = $this->getLoggedInClient();
        if ($clientUser) {
            if ((int) $project->customer_id !== (int) $clientUser->id) {
                return redirect()->back()->with('error', 'You are not authorized to create issues for this project.');
            }
        }

        ClientIssue::create([
            'project_id' => $request->project_id,
            'customer_id' => $project->customer_id,
            'issue_description' => $request->issue_description,
            'priority' => $request->priority ?? 'medium',
            'status' => $request->status ?? 'open',
        ]);

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo) {
            return redirect($redirectTo)->with('success', 'Client issue created successfully!');
        }

        return redirect()->route('client-issue')->with('success', 'Client issue created successfully!');
    }

    /**
     * Show the form for creating a new client issue.
     */
    public function create()
    {
        $clientUser = $this->getLoggedInClient();
        $isClient = $this->isClient();

        if ($clientUser) {
            // Client can only see their own projects
            $projects = Project::where('customer_id', $clientUser->id)->get();
            $clientUsers = collect([$clientUser]);
            // Auto-select project if only one exists
            $selectedProjectId = $projects->count() === 1 ? $projects->first()->id : null;
        } else {
            // Admin/Staff can see all projects and clients
            $projects = Project::all();
            $clientUsers = User::where('role', 'client')->get();
            $selectedProjectId = null;
        }

        return view('client-issue', compact('projects', 'clientUsers', 'selectedProjectId', 'isClient'));
    }

    /**
     * Display the specified client issue with tasks.
     */
    public function show($id)
    {
        $clientIssue = ClientIssue::with([
            'project',
            'customer',
            'tasks',
            'teamAssignments.assignedBy',
            'teamAssignments.assignedStaff',
        ])->findOrFail($id);

        $this->authorizeIssueAccess($clientIssue);

        $teams = Team::getTeamCards();

        return view('client-issue-details', compact('clientIssue', 'teams'));
    }

    /**
     * Assign a team/staff to a client issue.
     */
    public function assignTeam(Request $request, $clientIssueId)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'team_name' => ['required', Rule::in($teams)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $clientIssue = ClientIssue::findOrFail($clientIssueId);

        if ($this->getLoggedInClient()) {
            abort(403, 'You are not authorized to assign this issue.');
        }

        $this->authorizeIssueAccess($clientIssue, 'You are not authorized to assign this issue.');

        $staffId = optional(Auth::user()->staff)->id;

        ClientIssueTeamAssignment::create([
            'client_issue_id' => $clientIssueId,
            'team_name' => $request->team_name,
            'assigned_to' => $staffId ? (string) $staffId : null,
            'note' => null,
            'assigned_by' => Auth::id(),
        ]);

        return redirect()->route('client-issue.show', $clientIssueId)
            ->with('success', 'Issue assigned successfully!');
    }

    private function staffCanAccessIssue(ClientIssue $clientIssue, $staffId, string $staffTeam): bool
    {
        if (! $staffId) {
            return false;
        }

        $latestAssignment = $clientIssue->teamAssignments
            ->sortByDesc('id')
            ->first();

        if (! $latestAssignment) {
            return false;
        }

        if (! empty($latestAssignment->assigned_to) && (int) $latestAssignment->assigned_to === (int) $staffId) {
            return true;
        }

        if ($staffTeam === '') {
            return false;
        }

        return strcasecmp((string) $latestAssignment->team_name, $staffTeam) === 0;
    }

    /**
     * Display a single task for a client issue.
     */
    public function taskShow($clientIssueId, $taskId)
    {
        $clientIssue = ClientIssue::with(['project', 'customer'])->findOrFail($clientIssueId);
        $task = ClientIssueTask::where('client_issue_id', $clientIssueId)->findOrFail($taskId);

        $this->authorizeIssueAccess($clientIssue, 'You are not authorized to view this task.');

        return view('client-issue-task-view', compact('clientIssue', 'task'));
    }

    /**
     * Store a newly created task for a client issue.
     */
    public function taskStore(Request $request, $clientIssueId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'priority' => 'nullable|in:low,medium,high,critical',
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $clientIssue = ClientIssue::findOrFail($clientIssueId);

        $this->authorizeIssueAccess($clientIssue, 'You are not authorized to create tasks for this issue.');

        $attachmentsPaths = $this->storeUploadedAttachments($request);

        ClientIssueTask::create([
            'client_issue_id' => $clientIssueId,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'todo',
            'priority' => $request->priority ?? 'medium',
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'reminder_date' => $request->reminder_date,
            'reminder_time' => $request->reminder_time,
            'checklist_data' => $request->checklist_data,
            'labels_data' => $request->labels_data,
            'attachment' => $attachmentsPaths[0]['path'] ?? null,
            'attachments' => $attachmentsPaths,
        ]);

        return redirect()->route('client-issue.show', $clientIssueId)->with('success', 'Task created successfully!');
    }

    /**
     * Update an existing task.
     */
    public function taskUpdate(Request $request, $clientIssueId, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'priority' => 'nullable|in:low,medium,high,critical',
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $clientIssue = ClientIssue::findOrFail($clientIssueId);
        $task = ClientIssueTask::where('client_issue_id', $clientIssueId)->findOrFail($taskId);

        $this->authorizeIssueAccess($clientIssue, 'You are not authorized to update tasks for this issue.');

        // Handle multiple file uploads (replace existing if new files provided)
        $attachmentsPaths = $this->normalizeAttachmentEntries($task->attachments, $task->attachment);
        $uploadedAttachments = $this->storeUploadedAttachments($request);
        if (! empty($uploadedAttachments)) {
            $this->deleteAttachmentEntries($attachmentsPaths);
            $attachmentsPaths = $uploadedAttachments;
        }

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? $task->status,
            'priority' => $request->priority ?? $task->priority,
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'reminder_date' => $request->reminder_date,
            'reminder_time' => $request->reminder_time,
            'checklist_data' => $request->checklist_data,
            'labels_data' => $request->labels_data,
            'attachment' => $attachmentsPaths[0]['path'] ?? $task->attachment,
            'attachments' => $attachmentsPaths,
        ]);

        return redirect()->route('client-issue.show', $clientIssueId)->with('success', 'Task updated successfully!');
    }

    /**
     * Update task status (for drag and drop).
     */
    public function taskUpdateStatus(Request $request, $clientIssueId, $taskId)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $clientIssue = ClientIssue::findOrFail($clientIssueId);
        $task = ClientIssueTask::where('client_issue_id', $clientIssueId)->findOrFail($taskId);

        try {
            $this->authorizeIssueAccess($clientIssue, 'You are not authorized to update tasks for this issue.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'task' => $task]);
    }

    /**
     * Delete a task.
     */
    public function taskDestroy($clientIssueId, $taskId)
    {
        $clientIssue = ClientIssue::findOrFail($clientIssueId);
        $task = ClientIssueTask::where('client_issue_id', $clientIssueId)->findOrFail($taskId);

        $this->authorizeIssueAccess($clientIssue, 'You are not authorized to delete tasks for this issue.');

        // Delete attachments if exists
        $attachmentsPaths = $this->normalizeAttachmentEntries($task->attachments, $task->attachment);
        $this->deleteAttachmentEntries($attachmentsPaths);

        $task->delete();

        return redirect()->route('client-issue.show', $clientIssueId)->with('success', 'Task deleted successfully!');
    }

    /**
     * Remove the specified client issue from storage.
     */
    public function destroy($id)
    {
        $clientIssue = ClientIssue::findOrFail($id);
        $clientIssue->delete();

        return redirect()->route('client-issue')->with('success', 'Client issue deleted successfully!');
    }

    /**
     * Delete selected client issues.
     */
    public function deleteSelected(Request $request)
    {
        $ids = explode(',', $request->ids);

        if (empty($ids)) {
            return redirect()->route('client-issue')->with('error', 'No client issues selected for deletion.');
        }

        try {
            foreach ($ids as $id) {
                $clientIssue = ClientIssue::find($id);
                if ($clientIssue) {
                    // Delete related tasks
                    $clientIssue->tasks()->delete();
                    // Delete team assignments
                    $clientIssue->teamAssignments()->delete();
                    // Delete the issue
                    $clientIssue->delete();
                }
            }

            return redirect()->route('client-issue')->with('success', 'Selected client issues deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('client-issue')->with('error', 'Failed to delete selected client issues: '.$e->getMessage());
        }
    }

    /**
     * Update the status of a client issue
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:open,in_progress,resolved,closed',
            ]);

            $clientIssue = ClientIssue::findOrFail($id);

            // Check authorization - allow if user can edit issues or is admin
            if (! Auth::user()->can('edit_raise_issue') &&
                ! Auth::user()->hasAnyRole(['super-admin', 'super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You do not have permission to close issues',
                ], 403);
            }

            $clientIssue->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Issue status updated to '.ucfirst(str_replace('_', ' ', $validated['status'])),
                    'status' => $validated['status'],
                ]);
            }

            return redirect()->back()->with('success', 'Issue status updated successfully');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating issue status: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Error updating issue status: '.$e->getMessage());
        }
    }

    private function storeUploadedAttachments(Request $request): array
    {
        $attachments = [];

        $files = $request->file('attachments');
        if ($files instanceof UploadedFile) {
            $files = [$files];
        } elseif (! is_array($files)) {
            $files = [];
        }

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $originalName = $file->getClientOriginalName();
                $extension = strtolower((string) $file->getClientOriginalExtension());
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                $safeBaseName = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $baseName) ?: 'attachment';
                $fileName = time().'_'.$safeBaseName.($extension !== '' ? '.'.$extension : '');
                $relativeDir = 'uploads/task-attachments';
                $absoluteDir = public_path($relativeDir);

                if (! file_exists($absoluteDir)) {
                    mkdir($absoluteDir, 0755, true);
                }

                $file->move($absoluteDir, $fileName);

                $attachments[] = [
                    'path' => $relativeDir.'/'.$fileName,
                    'name' => $originalName,
                ];
            }
        }

        return $attachments;
    }

    private function normalizeAttachmentEntries(mixed $attachments, mixed $fallbackAttachment = null): array
    {
        $items = is_array($attachments) ? $attachments : (json_decode((string) $attachments, true) ?? []);

        if (! is_array($items)) {
            $items = [];
        }

        if (count($items) === 0 && $fallbackAttachment) {
            $items = [is_array($fallbackAttachment) ? ($fallbackAttachment['path'] ?? null) : $fallbackAttachment];
        }

        return collect($items)
            ->map(function ($item) {
                if (is_array($item)) {
                    $path = $item['path'] ?? null;

                    return $path ? ['path' => $path, 'name' => $item['name'] ?? basename((string) $path)] : null;
                }

                if (is_string($item) && trim($item) !== '') {
                    return ['path' => $item, 'name' => basename($item)];
                }

                return null;
            })
            ->filter(fn($item) => is_array($item) && ! empty($item['path']))
            ->values()
            ->all();
    }

    private function deleteAttachmentEntries(array $attachments): void
    {
        foreach ($attachments as $item) {
            $path = is_array($item) ? ($item['path'] ?? null) : null;
            if (! $path) {
                continue;
            }

            $absolutePath = public_path(ltrim((string) $path, '/'));
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }
}
