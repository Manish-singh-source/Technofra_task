<?php

namespace App\Http\Controllers;

use App\Models\ClientIssue;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusLog;
use App\Models\Staff;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    private const BUSINESS_TZ = 'UTC';

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

    public function index()
    {
        $customer = $this->getLoggedInCustomer();
        
        if ($customer) {
            // Customer can only see their own projects
            $projects = Project::with('customer')->where('customer_id', $customer->id)->get();
        } else {
            // Admin/Staff can see all projects
            $projects = Project::with('customer')->get();
        }
        
        $staff = Staff::all()->keyBy('id');
        $allProjects = $projects->count();
        $planningProjects = $projects->where('status', 'not_started')->count();
        $inProgressProjects = $projects->where('status', 'in_progress')->count();
        $onHoldProjects = $projects->where('status', 'on_hold')->count();
        $completedProjects = $projects->where('status', 'completed')->count();
        $cancelledProjects = $projects->where('status', 'cancelled')->count();
        return view('project', compact('projects', 'staff', 'allProjects', 'planningProjects', 'inProgressProjects', 'onHoldProjects', 'completedProjects', 'cancelledProjects'));
    }

    public function create()
    {
        $customers = Customer::all();
        $staff = Staff::all();
        return view('add-project', compact('customers', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'customer' => 'required|exists:customers,id',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'nullable|in:fixed_rate,hourly_rate',
            'total_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'members' => 'nullable|array',
            'members.*' => 'exists:staff,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
            'project_files' => 'nullable|array',
            'project_files.*' => 'file|max:10240|mimes:jpeg,jpg,png,gif,svg,webp,bmp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $project = Project::create([
            'project_name' => $request->project_name,
            'customer_id' => $request->customer,
            'status' => $request->status ?? 'not_started',
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'billing_type' => $request->billing_type,
            'total_rate' => $request->total_rate,
            'estimated_hours' => $request->estimated_hours,
            'tags' => $request->tags,
            'members' => $request->members,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'technologies' => $request->technologies,
        ]);

        // Handle file uploads
        if ($request->hasFile('project_files')) {
            foreach ($request->file('project_files') as $file) {
                try {
                    $this->storeProjectFile($project->id, $file);
                } catch (\Exception $e) {
                    \Log::error('File upload failed: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to upload file: ' . $e->getMessage())->withInput();
                }
            }
        }

        $this->createInitialStatusLog($project);

        return redirect()->route('project')->with('success', 'Project created successfully!');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $customers = Customer::all();
        $staff = Staff::all();
        $projectFiles = ProjectFile::where('project_id', $id)->orderBy('created_at', 'desc')->get();
        return view('edit-project', compact('project', 'customers', 'staff', 'projectFiles'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'customer' => 'required|exists:customers,id',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'nullable|in:fixed_rate,hourly_rate',
            'total_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'members' => 'nullable|array',
            'members.*' => 'exists:staff,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldStatus = $project->status;
        $newStatus = $request->status ?? 'not_started';

        $project->update([
            'project_name' => $request->project_name,
            'customer_id' => $request->customer,
            'status' => $newStatus,
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'billing_type' => $request->billing_type,
            'total_rate' => $request->total_rate,
            'estimated_hours' => $request->estimated_hours,
            'tags' => $request->tags,
            'members' => $request->members,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'technologies' => $request->technologies,
        ]);

        // Handle file uploads
        if ($request->hasFile('project_files')) {
            foreach ($request->file('project_files') as $file) {
                try {
                    $this->storeProjectFile($project->id, $file);
                } catch (\Exception $e) {
                    \Log::error('File upload failed: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to upload file: ' . $e->getMessage())->withInput();
                }
            }
        }

        $this->syncStatusTimeline($project, $oldStatus, $newStatus);

        return redirect()->route('project')->with('success', 'Project updated successfully!');
    }

    public function show($id)
    {
        $customer = $this->getLoggedInCustomer();
        $project = Project::with([
            'customer',
            'statusLogs' => function ($query) {
                $query->orderBy('started_at');
            },
        ])->findOrFail($id);
        
        // If user is a customer, verify they own this project
        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to view this project.');
        }
        
        $staff = Staff::all()->keyBy('id');
        $memberIds = collect($project->members ?? [])->filter()->values();

        $elapsedBoundary = $this->getElapsedBoundary($project);
        $elapsedMinutes = 0;
        $firstActiveStartPoint = null;

        $inProgressLogs = $project->statusLogs->where('status', 'in_progress')->values();
        foreach ($inProgressLogs as $log) {
            $logStart = $this->toBusinessTz($log->started_at);
            if (!$logStart) {
                continue;
            }

            $logEnd = $log->ended_at
                ? $this->toBusinessTz($log->ended_at)
                : $elapsedBoundary->copy();

            $intervalEnd = $logEnd->lt($elapsedBoundary) ? $logEnd : $elapsedBoundary->copy();
            if ($intervalEnd->lte($logStart)) {
                continue;
            }

            if (!$firstActiveStartPoint || $logStart->lt($firstActiveStartPoint)) {
                $firstActiveStartPoint = $logStart->copy();
            }

            $elapsedMinutes += $this->calculateWorkingMinutes($logStart, $intervalEnd);
        }

        if ($inProgressLogs->isEmpty() && $project->status === 'in_progress') {
            $fallbackStart = $this->getFallbackActiveStart($project);
            if ($fallbackStart && $elapsedBoundary->gt($fallbackStart)) {
                $firstActiveStartPoint = $fallbackStart->copy();
                $elapsedMinutes += $this->calculateWorkingMinutes($fallbackStart, $elapsedBoundary);
            }
        }

        if (!$firstActiveStartPoint) {
            $firstActiveStartPoint = $this->getFallbackActiveStart($project);
        }

        $deadlineWindowMinutes = 0;
        if ($project->deadline && $firstActiveStartPoint) {
            $deadlineAtEndOfWork = Carbon::parse(
                $project->deadline->format('Y-m-d') . ' 18:00:00',
                self::BUSINESS_TZ
            );

            if ($deadlineAtEndOfWork->gt($firstActiveStartPoint)) {
                $deadlineWindowMinutes = $this->calculateWorkingMinutes($firstActiveStartPoint, $deadlineAtEndOfWork);
            }
        }

        $projectElapsedHours = round($elapsedMinutes / 60, 1);
        $projectUtilization = $deadlineWindowMinutes > 0
            ? min(100.0, round(($elapsedMinutes / $deadlineWindowMinutes) * 100, 1))
            : 0.0;

        // Fetch tasks for this project before member-level metrics calculation.
        $tasks = Task::where('project_id', $id)->with('project')->orderBy('created_at', 'desc')->get();

        // Build member weights from task assignees so total project time can be split per member.
        $memberWeights = [];
        foreach ($memberIds as $memberId) {
            $memberWeights[$memberId] = 0.0;
        }

        foreach ($tasks as $task) {
            $assignees = collect($task->assignees ?? [])
                ->filter(fn ($assigneeId) => isset($memberWeights[$assigneeId]))
                ->values();

            if ($assignees->isEmpty()) {
                continue;
            }

            $share = 1 / $assignees->count();
            foreach ($assignees as $assigneeId) {
                $memberWeights[$assigneeId] += $share;
            }
        }

        $totalWeight = array_sum($memberWeights);
        if ($totalWeight <= 0 && count($memberWeights) > 0) {
            // Fallback: when tasks have no assignees, split equally across project members.
            foreach ($memberWeights as $memberId => $weight) {
                $memberWeights[$memberId] = 1.0;
            }
            $totalWeight = array_sum($memberWeights);
        }

        $memberMetrics = [];
        foreach ($memberIds as $memberId) {
            $ratio = $totalWeight > 0 ? ($memberWeights[$memberId] / $totalWeight) : 0;
            $memberElapsedMinutes = $elapsedMinutes * $ratio;
            $memberMetrics[$memberId] = [
                'total_hours' => round($memberElapsedMinutes / 60, 1),
                'utilization' => $deadlineWindowMinutes > 0
                    ? min(100.0, round(($memberElapsedMinutes / $deadlineWindowMinutes) * 100, 1))
                    : 0.0,
            ];
        }
        
        if ($customer) {
            // Customer can only see their own projects
            $allProjects = Project::where('customer_id', $customer->id)->get();
        } else {
            $allProjects = Project::all();
        }
        
        // Get project files
        $projectFiles = ProjectFile::where('project_id', $id)->orderBy('created_at', 'desc')->get();
        $milestones = ProjectMilestone::where('project_id', $id)
            ->orderBy('sort_order')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $milestoneStats = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('status', 'completed')->count(),
            'in_progress' => $milestones->where('status', 'in_progress')->count(),
            'pending' => $milestones->where('status', 'pending')->count(),
        ];

        // Get client issues for this project
        $issues = ClientIssue::where('project_id', $id)
            ->with(['customer', 'tasks'])
            ->orderBy('created_at', 'desc')
            ->get();

        $issueStats = [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];

        // Get project comments
        $projectComments = ProjectComment::where('project_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-details', compact(
            'project',
            'staff',
            'allProjects',
            'memberMetrics',
            'projectElapsedHours',
            'projectUtilization',
            'tasks',
            'projectFiles',
            'milestones',
            'milestoneStats',
            'issues',
            'issueStats',
            'projectComments'
        ));
    }

    public function storeMilestone(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $customer = $this->getLoggedInCustomer();

        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to update this project.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $nextSortOrder = (int) ProjectMilestone::where('project_id', $projectId)->max('sort_order') + 1;
        $completedAt = $validated['status'] === 'completed' ? now(self::BUSINESS_TZ) : null;

        ProjectMilestone::create([
            'project_id' => $projectId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'completed_at' => $completedAt,
            'sort_order' => $nextSortOrder,
        ]);

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Milestone created successfully.');
    }

    public function storeIssue(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $customer = $this->getLoggedInCustomer();

        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to update this project.');
        }

        $validated = $request->validate([
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        ClientIssue::create([
            'project_id' => $projectId,
            'customer_id' => $project->customer_id,
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Issue created successfully.');
    }

    public function updateIssue(Request $request, $projectId, $issueId)
    {
        $project = Project::findOrFail($projectId);
        $issue = ClientIssue::where('id', $issueId)->where('project_id', $projectId)->firstOrFail();
        $customer = $this->getLoggedInCustomer();

        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to update this project.');
        }

        $validated = $request->validate([
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $issue->update([
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Issue updated successfully.');
    }

    public function destroyIssue($projectId, $issueId)
    {
        $project = Project::findOrFail($projectId);
        $issue = ClientIssue::where('id', $issueId)->where('project_id', $projectId)->firstOrFail();
        $customer = $this->getLoggedInCustomer();

        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to update this project.');
        }

        $issue->delete();

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Issue deleted successfully.');
    }

    public function storeComment(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $customer = $this->getLoggedInCustomer();

        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to comment on this project.');
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        ProjectComment::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Comment added successfully.');
    }

    private function createInitialStatusLog(Project $project): void
    {
        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status' => $project->status,
            'started_at' => now(self::BUSINESS_TZ),
            'ended_at' => null,
        ]);
    }

    private function syncStatusTimeline(Project $project, string $oldStatus, string $newStatus): void
    {
        $openLog = ProjectStatusLog::where('project_id', $project->id)
            ->whereNull('ended_at')
            ->orderByDesc('started_at')
            ->first();

        if ($oldStatus === $newStatus) {
            if (!$openLog) {
                ProjectStatusLog::create([
                    'project_id' => $project->id,
                    'status' => $newStatus,
                    'started_at' => now(self::BUSINESS_TZ),
                    'ended_at' => null,
                ]);
            }
            return;
        }

        if ($openLog) {
            $openLog->update([
                'ended_at' => now(self::BUSINESS_TZ),
            ]);
        }

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status' => $newStatus,
            'started_at' => now(self::BUSINESS_TZ),
            'ended_at' => null,
        ]);
    }

    private function getElapsedBoundary(Project $project): Carbon
    {
        $now = now(self::BUSINESS_TZ);
        if (!$project->deadline) {
            return $now;
        }

        $deadlineAtEndOfWork = Carbon::parse(
            $project->deadline->format('Y-m-d') . ' 18:00:00',
            self::BUSINESS_TZ
        );

        return $deadlineAtEndOfWork->lt($now) ? $deadlineAtEndOfWork : $now;
    }

    private function getFallbackActiveStart(Project $project): ?Carbon
    {
        $createdAt = $this->toBusinessTz($project->created_at);
        if (!$project->start_date) {
            return $createdAt;
        }

        $startDateAtWorkStart = Carbon::parse(
            $project->start_date->format('Y-m-d') . ' 09:00:00',
            self::BUSINESS_TZ
        );

        if (!$createdAt) {
            return $startDateAtWorkStart;
        }

        return $createdAt->gt($startDateAtWorkStart) ? $createdAt : $startDateAtWorkStart;
    }

    private function toBusinessTz($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->setTimezone(self::BUSINESS_TZ);
    }

    private function calculateWorkingMinutes(Carbon $from, Carbon $to): int
    {
        if ($to->lte($from)) {
            return 0;
        }

        $start = $from->copy()->setTimezone(self::BUSINESS_TZ);
        $end = $to->copy()->setTimezone(self::BUSINESS_TZ);
        $cursor = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();
        $minutes = 0;

        while ($cursor->lte($lastDay)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $dayDate = $cursor->format('Y-m-d');
                $workMorningStart = Carbon::parse($dayDate . ' 09:00:00', self::BUSINESS_TZ);
                $workMorningEnd = Carbon::parse($dayDate . ' 13:00:00', self::BUSINESS_TZ);
                $workEveningStart = Carbon::parse($dayDate . ' 14:00:00', self::BUSINESS_TZ);
                $workEveningEnd = Carbon::parse($dayDate . ' 18:00:00', self::BUSINESS_TZ);

                $minutes += $this->calculateOverlapMinutes($start, $end, $workMorningStart, $workMorningEnd);
                $minutes += $this->calculateOverlapMinutes($start, $end, $workEveningStart, $workEveningEnd);
            }

            $cursor->addDay();
        }

        return $minutes;
    }

    private function calculateOverlapMinutes(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): int
    {
        $start = $aStart->gt($bStart) ? $aStart : $bStart;
        $end = $aEnd->lt($bEnd) ? $aEnd : $bEnd;

        if ($end->lte($start)) {
            return 0;
        }

        return $start->diffInMinutes($end);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('project')->with('success', 'Project deleted successfully!');
    }

    /**
     * Store a project file
     */
    private function storeProjectFile($projectId, $file)
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $fileSize = $file->getSize();
        $fileType = $file->getMimeType();
        
        // Create directory if it doesn't exist
        $directory = public_path('uploads/project_files/' . $projectId);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Store the file
        $file->move($directory, $fileName);
        
        ProjectFile::create([
            'project_id' => $projectId,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'file_path' => 'uploads/project_files/' . $projectId . '/' . $fileName,
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Upload file for a project (AJAX)
     */
    public function uploadFile(Request $request, $projectId)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpeg,jpg,png,gif,svg,webp,bmp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
            'description' => 'nullable|string|max:255',
        ]);
        
        if ($request->hasFile('file')) {
            $this->storeProjectFile($projectId, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'File uploaded successfully!']);
        }
        
        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    /**
     * Download project file
     */
    public function downloadFile($fileId)
    {
        $file = ProjectFile::findOrFail($fileId);
        $filePath = public_path($file->file_path);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found!');
        }
        
        return response()->download($filePath, $file->original_name);
    }

    /**
     * Delete project file
     */
    public function deleteFile($fileId)
    {
        $file = ProjectFile::findOrFail($fileId);
        $filePath = public_path($file->file_path);
        
        // Delete physical file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete database record
        $file->delete();
        
        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'File deleted successfully!']);
        }
        
        return redirect()->back()->with('success', 'File deleted successfully!');
    }

    /**
     * Customer's own projects - simplified view for customers
     */
    public function myProjects()
    {
        $customer = $this->getLoggedInCustomer();
        
        if (!$customer) {
            // Redirect non-customers to the main project page
            return redirect()->route('project');
        }
        
        // Customer can only see their own projects
        $projects = Project::with('customer')->where('customer_id', $customer->id)->get();
        $staff = Staff::all()->keyBy('id');
        $allProjects = $projects->count();
        $planningProjects = $projects->where('status', 'not_started')->count();
        $inProgressProjects = $projects->where('status', 'in_progress')->count();
        $onHoldProjects = $projects->where('status', 'on_hold')->count();
        $completedProjects = $projects->where('status', 'completed')->count();
        $cancelledProjects = $projects->where('status', 'cancelled')->count();
        
        return view('customer-projects', compact('projects', 'staff', 'allProjects', 'planningProjects', 'inProgressProjects', 'onHoldProjects', 'completedProjects', 'cancelledProjects', 'customer'));
    }

}
