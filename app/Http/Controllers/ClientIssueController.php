<?php

namespace App\Http\Controllers;

use App\Models\ClientIssue;
use App\Models\ClientIssueTeamAssignment;
use App\Models\ClientIssueTask;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientIssueController extends Controller
{
    private const TEAM_NAMES = ['Web Team', 'Graphic Team', 'Social Media Team', 'Accounts Team'];

    /**
     * Get the logged-in customer
     */
    private function getLoggedInCustomer()
    {
        $user = Auth::user();
        if ($user && $user->hasRole('customer')) {
            return Customer::where('email', $user->email)->first();
        }
        return null;
    }

    /**
     * Check if current user is a customer
     */
    private function isCustomer()
    {
        return Auth::user() && Auth::user()->hasRole('customer');
    }

    /**
     * Display a listing of client issues.
     */
    public function index()
    {
        $customer = $this->getLoggedInCustomer();

        if ($customer) {
            // Client users should not see client issue listing items.
            $clientIssues = collect();
            $projects = Project::where('customer_id', $customer->id)->get();
            $customers = collect([$customer]);
        } else {
            $user = Auth::user();
            if ($user && $user->isStaff()) {
                $staff = $user->staff;
                $staffId = optional($user->staff)->id;
                $staffTeam = trim((string) optional($staff)->team);

                $clientIssues = ClientIssue::with(['project', 'customer', 'teamAssignments'])
                    ->get();
                $clientIssues = $clientIssues
                    ->filter(fn($issue) => $this->staffCanAccessIssue($issue, $staffId, $staffTeam))
                    ->values();
            } else {
                // Admin can see all issues
                $clientIssues = ClientIssue::with(['project', 'customer'])->get();
            }
            // Admin/Staff can see all projects and customers
            $projects = Project::all();
            $customers = Customer::all();
        }
        
        return view('client-issue', compact('clientIssues', 'projects', 'customers'));
    }

    /**
     * Store a newly created client issue in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'customer_id' => 'required|exists:customers,id',
            'issue_description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // If user is a customer, verify they own this project
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            $project = Project::findOrFail($request->project_id);
            if ($project->customer_id !== $customer->id) {
                return redirect()->back()->with('error', 'You are not authorized to create issues for this project.');
            }
        }

        ClientIssue::create([
            'project_id' => $request->project_id,
            'customer_id' => $request->customer_id,
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
        $customer = $this->getLoggedInCustomer();
        
        if ($customer) {
            // Customer can only see their own projects
            $projects = Project::where('customer_id', $customer->id)->get();
            $customers = collect([$customer]);
        } else {
            // Admin/Staff can see all projects and customers
            $projects = Project::all();
            $customers = Customer::all();
        }
        
        return view('client-issue', compact('projects', 'customers'));
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
        
        // Client users should not be able to access issue detail pages.
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            abort(403, 'You are not authorized to view this issue.');
        }

        $user = Auth::user();
        if ($user && $user->isStaff()) {
            $staff = $user->staff;
            $staffId = optional($user->staff)->id;
            $staffTeam = trim((string) optional($staff)->team);
            if (!$this->staffCanAccessIssue($clientIssue, $staffId, $staffTeam)) {
                abort(403, 'You are not authorized to view this issue.');
            }
        }

        $teams = self::TEAM_NAMES;
        
        return view('client-issue-details', compact('clientIssue', 'teams'));
    }

    /**
     * Assign a team/staff to a client issue.
     */
    public function assignTeam(Request $request, $clientIssueId)
    {
        $validator = Validator::make($request->all(), [
            'team_name' => 'required|in:Web Team,Graphic Team,Social Media Team,Accounts Team',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $clientIssue = ClientIssue::findOrFail($clientIssueId);

        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            abort(403, 'You are not authorized to assign this issue.');
        }

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
        if (!$staffId) {
            return false;
        }

        $latestAssignment = $clientIssue->teamAssignments
            ->sortByDesc('id')
            ->first();

        if (!$latestAssignment) {
            return false;
        }

        if (!empty($latestAssignment->assigned_to) && (int) $latestAssignment->assigned_to === (int) $staffId) {
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

        // If user is a customer, verify they own this issue's project
        $customer = $this->getLoggedInCustomer();
        if ($customer && $clientIssue->project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to view this task.');
        }

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
        
        // If user is a customer, verify they own this issue's project
        $customer = $this->getLoggedInCustomer();
        if ($customer && $clientIssue->project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to create tasks for this issue.');
        }

        // Handle multiple file uploads
        $attachmentsPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $attachmentsPaths[] = [
                        'path' => $file->store('task-attachments', 'public'),
                        'name' => $file->getClientOriginalName(),
                    ];
                }
            }
        }

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
        
        // If user is a customer, verify they own this issue's project
        $customer = $this->getLoggedInCustomer();
        if ($customer && $clientIssue->project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to update tasks for this issue.');
        }

        // Handle multiple file uploads (replace existing if new files provided)
        $attachmentsPaths = is_array($task->attachments) ? $task->attachments : (json_decode($task->attachments, true) ?? []);
        if (count($attachmentsPaths) === 0 && $task->attachment) {
            $attachmentsPaths = [$task->attachment];
        }
        if ($request->hasFile('attachments')) {
            // Delete old files
            foreach ($attachmentsPaths as $oldItem) {
                $oldPath = is_array($oldItem) ? ($oldItem['path'] ?? null) : $oldItem;
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            if ($task->attachment && Storage::disk('public')->exists($task->attachment)) {
                Storage::disk('public')->delete($task->attachment);
            }

            $attachmentsPaths = [];
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $attachmentsPaths[] = [
                        'path' => $file->store('task-attachments', 'public'),
                        'name' => $file->getClientOriginalName(),
                    ];
                }
            }
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
        
        // If user is a customer, verify they own this issue's project
        $customer = $this->getLoggedInCustomer();
        if ($customer && $clientIssue->project->customer_id !== $customer->id) {
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
        
        // If user is a customer, verify they own this issue's project
        $customer = $this->getLoggedInCustomer();
        if ($customer && $clientIssue->project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to delete tasks for this issue.');
        }

        // Delete attachments if exists
        $attachmentsPaths = is_array($task->attachments) ? $task->attachments : (json_decode($task->attachments, true) ?? []);
        foreach ($attachmentsPaths as $oldItem) {
            $oldPath = is_array($oldItem) ? ($oldItem['path'] ?? null) : $oldItem;
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        if ($task->attachment && Storage::disk('public')->exists($task->attachment)) {
            Storage::disk('public')->delete($task->attachment);
        }

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
            return redirect()->route('client-issue')->with('error', 'Failed to delete selected client issues: ' . $e->getMessage());
        }
    }
}
