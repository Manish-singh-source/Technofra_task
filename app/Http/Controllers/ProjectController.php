<?php

namespace App\Http\Controllers;

use App\Mail\ProjectCreatedMail;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectIssue;
use App\Models\ProjectMilestone;
use App\Models\ProjectActivity;
use App\Models\ProjectStatusLog;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use App\Services\ProjectManagement\ProjectDashboardService;
use App\Services\ProjectManagement\MilestoneProgressService;
use App\Services\ProjectManagement\ProjectActivityService;
use App\Services\ProjectManagement\ProjectLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Log;

class ProjectController extends Controller
{
    private const DEFAULT_BUSINESS_TZ = 'UTC';

    public function __construct(
        private ProjectLifecycleService $projectLifecycleService,
        private MilestoneProgressService $milestoneProgressService,
        private ProjectActivityService $projectActivityService,
        private ProjectDashboardService $projectDashboardService
    ) {}

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

    private function clientProjectOwnerIds(User $clientUser): array
    {
        return collect([$clientUser->id])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function projectBelongsToClient(Project $project, User $clientUser): bool
    {
        return in_array((int) $project->customer_id, $this->clientProjectOwnerIds($clientUser), true);
    }

    /**
     * Check if current user is a client
     */
    private function isClient()
    {
        return $this->getLoggedInClient() !== null;
    }

    /**
     * Get the logged-in staff record.
     */
    private function getLoggedInStaff()
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));
        if ($rawRole !== 'staff') {
            return null;
        }

        return User::where('id', $user->id)->first();
    }

    /**
     * Admin users can access every project; regular staff cannot.
     */
    private function isPrivilegedProjectUser(): bool
    {
        $user = Auth::user();
        return (bool) ($user && $user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2']));
    }

    /**
     * Restrict project visibility by current user type.
     */
    private function visibleProjectsQuery()
    {
        if ($this->isPrivilegedProjectUser()) {
            return Project::query();
        }

        $clientUser = $this->getLoggedInClient();
        if ($clientUser) {
            return Project::query()->where('customer_id', $clientUser->id);
        }

        $staff = $this->getLoggedInStaff();
        if (! $staff) {
            return Project::query();
        }

        return Project::query()
            ->where(function ($query) use ($staff) {
                $query->whereJsonContains('members', $staff->id)
                    ->orWhereJsonContains('members', (string) $staff->id);
            });
    }

    /**
     * Ensure the logged-in user can access the given project.
     */
    private function authorizeProjectAccess(Project $project, string $message = 'You are not authorized to view this project.'): void
    {
        if ($this->isPrivilegedProjectUser()) {
            return;
        }

        $clientUser = $this->getLoggedInClient();
        if ($clientUser) {
            abort_if(! $this->projectBelongsToClient($project, $clientUser), 403, $message);

            return;
        }

        $staff = $this->getLoggedInStaff();
        abort_if(! $staff, 403, $message);

        $memberIds = collect($project->members ?? [])
            ->filter(fn ($memberId) => $memberId !== null && $memberId !== '')
            ->map(fn ($memberId) => (int) $memberId);

        abort_if(! $memberIds->contains((int) $staff->id), 403, $message);
    }

    public function index()
    {
        $projects = $this->visibleProjectsQuery()
            ->with(['customer'])
            ->get();

        $staffIds = $projects
            ->pluck('members')
            ->flatten()
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $staff = User::staffMembers()
            ->whereIn('id', $staffIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');

        $projects->each(function ($project) use ($staff) {
            $memberIds = collect($project->members ?? [])
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $project->setAttribute('staff_members', $memberIds
                ->map(fn ($id) => $staff->get($id))
                ->filter()
                ->values());
        });

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
        $customers = User::query()
            ->with('customer')
            ->where('role', 'client')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $staff = User::staffMembers()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('add-project', compact('customers', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'customer' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'client')),
            ],
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'lifecycle_stage' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'nullable|in:fixed_rate,hourly_rate',
            'total_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'members' => 'nullable|array',
            'members.*' => [
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
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

        try {
            $this->projectLifecycleService->ensureValidStage($request->lifecycle_stage);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'lifecycle_stage' => $e->getMessage(),
            ]);
        }

        $project = Project::create([
            'project_name' => $request->project_name,
            'customer_id' => $request->customer,
            'status' => $request->status ?? 'not_started',
            'lifecycle_stage' => $request->lifecycle_stage ?? 'project_created',
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
                    \Log::error('File upload failed: '.$e->getMessage());

                    return redirect()->back()->with('error', 'Failed to upload file: '.$e->getMessage())->withInput();
                }
            }
        }

        $this->createInitialStatusLog($project);
        $this->sendProjectCreationNotifications($project);
        $this->projectActivityService->log(
            (int) $project->id,
            'project_created',
            'Project Created',
            'Project created: '.$project->project_name,
            null,
            Auth::id(),
            [
                'status' => $project->status,
                'lifecycle_stage' => $project->lifecycle_stage,
            ]
        );

        return redirect()->route('project')->with('success', 'Project created successfully!');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $this->authorizeProjectAccess($project, 'You are not authorized to edit this project.');
        $customers = User::query()
            ->with('customer')
            ->where('role', 'client')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $staff = User::staffMembers()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $projectFiles = ProjectFile::where('project_id', $id)->orderBy('created_at', 'desc')->get();

        return view('edit-project', compact('project', 'customers', 'staff', 'projectFiles'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'customer' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'client')),
            ],
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'lifecycle_stage' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'nullable|in:fixed_rate,hourly_rate',
            'total_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'members' => 'nullable|array',
            'members.*' => [
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->projectLifecycleService->ensureValidStage($request->lifecycle_stage);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'lifecycle_stage' => $e->getMessage(),
            ]);
        }

        $oldStatus = $project->status;
        $newStatus = $request->status ?? 'not_started';

        $project->update([
            'project_name' => $request->project_name,
            'customer_id' => $request->customer,
            'status' => $newStatus,
            'lifecycle_stage' => $request->lifecycle_stage ?? $project->lifecycle_stage,
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
                    \Log::error('File upload failed: '.$e->getMessage());

                    return redirect()->back()->with('error', 'Failed to upload file: '.$e->getMessage())->withInput();
                }
            }
        }

        $this->syncStatusTimeline($project, $oldStatus, $newStatus);
        $this->projectActivityService->log(
            (int) $project->id,
            'project_updated',
            'Project Updated',
            'Project updated: '.$project->project_name,
            null,
            Auth::id(),
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'lifecycle_stage' => $project->lifecycle_stage,
            ]
        );

        return redirect()->route('project')->with('success', 'Project updated successfully!');
    }

    public function show($id)
    {
        $project = Project::query()
            ->select([
                'id',
                'project_name',
                'customer_id',
                'status',
                'lifecycle_stage',
                'start_date',
                'deadline',
                'tags',
                'members',
                'description',
                'priority',
                'technologies',
                'deployment_date',
                'maintenance_expiry',
            ])
            ->with([
            'customer',
            'customerUser.address',
            'customerUser',
            'statusLogs' => function ($query) {
                $query->orderBy('started_at');
            },
            ])
            ->findOrFail($id);

        $this->authorizeProjectAccess($project);

        $elapsedBoundary = $this->getElapsedBoundary();
        $inProgressIntervals = $this->getProjectActiveIntervals($project, $elapsedBoundary);
        $projectElapsedMinutes = $this->calculateIntervalsElapsedMinutes($inProgressIntervals);
        $projectElapsedHours = round($projectElapsedMinutes / 60, 1);

        // Fetch tasks for this project before member-level metrics calculation.
        $tasks = Task::query()
            ->select([
                'id',
                'project_id',
                'title',
                'assignees',
                'status',
                'priority',
                'deadline',
                'created_at',
            ])
            ->where('project_id', $id)
            ->orderByDesc('created_at')
            ->get();
        $memberIds = collect($project->members ?? [])->filter()->map(fn ($id) => (int) $id)->values();
        $taskAssigneeIds = $tasks->pluck('assignees')
            ->flatten()
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->values();
        $relevantStaffIds = $memberIds->merge($taskAssigneeIds)->unique()->values();

        $taskStatusCounts = $tasks->countBy(fn ($task) => (string) $task->status);
        $taskAssignments = [];
        $taskAssignmentStartMap = [];
        foreach ($tasks as $task) {
            $taskCreatedAt = $this->toBusinessTz($task->created_at);
            foreach (collect($task->assignees ?? [])->map(fn ($assigneeId) => (int) $assigneeId)->unique() as $assigneeId) {
                $taskAssignments[$assigneeId] = ($taskAssignments[$assigneeId] ?? 0) + 1;

                if ($taskCreatedAt === null) {
                    continue;
                }

                $existingStart = $taskAssignmentStartMap[$assigneeId] ?? null;
                if ($existingStart === null || $taskCreatedAt->lt($existingStart)) {
                    $taskAssignmentStartMap[$assigneeId] = $taskCreatedAt->copy();
                }
            }
        }

        $staff = User::staffMembers()
            ->select(['id', 'first_name', 'last_name', 'profile_image', 'email'])
            ->whereIn('id', $relevantStaffIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');

        $taskStatusMeta = [
            'not_started' => ['label' => 'Not Started', 'badge_class' => 'bg-secondary'],
            'in_progress' => ['label' => 'In Progress', 'badge_class' => 'bg-primary'],
            'on_hold' => ['label' => 'On Hold', 'badge_class' => 'bg-warning'],
            'completed' => ['label' => 'Completed', 'badge_class' => 'bg-success'],
            'cancelled' => ['label' => 'Cancelled', 'badge_class' => 'bg-danger'],
        ];

        $totalTasks = $tasks->count();
        $usageDistribution = [];
        foreach ($taskStatusMeta as $status => $meta) {
            $count = (int) ($taskStatusCounts[$status] ?? 0);
            $usageDistribution[] = [
                'status' => $status,
                'label' => $meta['label'],
                'badge_class' => $meta['badge_class'],
                'count' => $count,
                'percentage' => $totalTasks > 0 ? round(($count / $totalTasks) * 100, 1) : 0,
            ];
        }

        $usageChartLabels = array_column($usageDistribution, 'label');
        $usageChartData = array_column($usageDistribution, 'percentage');

        $taskCreatedCountsByDate = $tasks
            ->groupBy(function ($task) {
                return $this->toBusinessTz($task->created_at)?->format('Y-m-d');
            })
            ->map(fn ($group) => $group->count());

        $weeklyActivityLabels = [];
        $weeklyActivityData = [];
        $activityEndDate = now($this->businessTimezone())->startOfDay();
        for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
            $date = $activityEndDate->copy()->subDays($dayOffset);
            $dateKey = $date->format('Y-m-d');

            $weeklyActivityLabels[] = $date->format('D');
            $weeklyActivityData[] = (int) ($taskCreatedCountsByDate[$dateKey] ?? 0);
        }

        $memberMetrics = [];
        foreach ($memberIds as $memberId) {
            $assignmentStartAt = $taskAssignmentStartMap[(int) $memberId] ?? null;
            $memberElapsedMinutes = $assignmentStartAt
                ? $this->calculateIntervalsElapsedMinutesWithinRange($inProgressIntervals, $assignmentStartAt, $elapsedBoundary)
                : 0;

            $memberMetrics[$memberId] = [
                'total_hours' => round($memberElapsedMinutes / 60, 1),
                'assignment_started_at' => $assignmentStartAt?->toDateTimeString(),
            ];
        }

        // Get project files
        $projectFiles = ProjectFile::query()
            ->select(['id', 'project_id', 'original_name', 'file_size', 'file_path', 'created_at'])
            ->where('project_id', $id)
            ->orderByDesc('created_at')
            ->get();
        $milestones = ProjectMilestone::query()
            ->select(['id', 'project_id', 'title', 'description', 'status', 'due_date', 'completed_at', 'sort_order'])
            ->where('project_id', $id)
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
        $issues = ProjectIssue::query()
            ->select(['id', 'project_id', 'issue_description', 'priority', 'status', 'created_at', 'updated_at'])
            ->where('project_id', $id)
            ->orderByDesc('created_at')
            ->get();

        $issueStats = [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];

        $completedTasks = (int) ($taskStatusCounts['completed'] ?? 0);
        $inProgressTasks = (int) ($taskStatusCounts['in_progress'] ?? 0);
        $overdueTasks = $tasks->filter(function ($task) {
            return $task->deadline
                && $task->deadline->isPast()
                && ! in_array($task->status, ['completed', 'cancelled'], true);
        })->count();
        $notStartedTasks = (int) ($taskStatusCounts['not_started'] ?? 0);
        $doneMilestones = $milestoneStats['completed'] ?? 0;
        $resolvedIssues = ($issueStats['resolved'] ?? 0) + ($issueStats['closed'] ?? 0);

        $progressSignals = [
            $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : null,
            ($milestoneStats['total'] ?? 0) > 0 ? (($doneMilestones / $milestoneStats['total']) * 100) : null,
            ($issueStats['total'] ?? 0) > 0 ? (($resolvedIssues / $issueStats['total']) * 100) : null,
        ];

        if ($project->status === 'completed') {
            $overallProgress = 100;
        } elseif ($project->status === 'cancelled') {
            $overallProgress = 0;
        } else {
            $availableProgressSignals = array_values(array_filter($progressSignals, function ($value) {
                return $value !== null;
            }));

            if (! empty($availableProgressSignals)) {
                $overallProgress = (int) round(array_sum($availableProgressSignals) / count($availableProgressSignals));
            } else {
                $overallProgress = match ($project->status) {
                    'in_progress' => 45,
                    'on_hold' => 20,
                    default => 0,
                };
            }
        }

        $overallProgress = max(0, min(100, $overallProgress));
        $remainingTasks = max($totalTasks - $completedTasks, 0);
        $projectProgress = [
            'overall' => $overallProgress,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'overdue_tasks' => $overdueTasks,
            'remaining_tasks' => $remainingTasks,
            'not_started_tasks' => $notStartedTasks,
            'total_tasks' => $totalTasks,
            'completed_milestones' => $doneMilestones,
            'total_milestones' => $milestoneStats['total'] ?? 0,
            'resolved_issues' => $resolvedIssues,
            'total_issues' => $issueStats['total'] ?? 0,
        ];

        // Get project comments (paginated with dedicated page parameter)
        $projectComments = ProjectComment::where('project_id', $id)
            ->with(['user:id,name,profile_image,email'])
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'comments_page')
            ->withQueryString();

        $recentActivities = collect();
        if (Schema::hasTable('project_activities')) {
            $recentActivities = ProjectActivity::query()
                ->select([
                    'id',
                    'project_id',
                    'task_id',
                    'user_id',
                    'activity_type',
                    'title',
                    'description',
                    'activity_at',
                    'created_at',
                ])
                ->where('project_id', $project->id)
                ->with(['user:id,first_name,last_name,email', 'task:id,title'])
                ->orderByDesc(DB::raw('COALESCE(activity_at, created_at)'))
                ->paginate(10, ['*'], 'timeline_page')
                ->withQueryString();
        }

        $pendingIssues = $issues->whereIn('status', ['open', 'in_progress'])->values();

        $timeTrackingStats = [
            'total_hours' => 0,
            'manual_hours' => 0,
            'timer_hours' => 0,
            'entries_count' => 0,
        ];

        if (Schema::hasTable('task_time_logs')) {
            $timeSummary = TaskTimeLog::query()
                ->join('tasks', 'tasks.id', '=', 'task_time_logs.task_id')
                ->where('tasks.project_id', $project->id)
                ->selectRaw('COALESCE(SUM(task_time_logs.duration_minutes), 0) as total_minutes')
                ->selectRaw("COALESCE(SUM(CASE WHEN task_time_logs.log_type = 'manual' THEN task_time_logs.duration_minutes ELSE 0 END), 0) as manual_minutes")
                ->selectRaw("COALESCE(SUM(CASE WHEN task_time_logs.log_type = 'timer' THEN task_time_logs.duration_minutes ELSE 0 END), 0) as timer_minutes")
                ->selectRaw('COUNT(*) as entries_count')
                ->first();

            $totalMinutes = (float) ($timeSummary->total_minutes ?? 0);
            $manualMinutes = (float) ($timeSummary->manual_minutes ?? 0);
            $timerMinutes = (float) ($timeSummary->timer_minutes ?? 0);

            $timeTrackingStats = [
                'total_hours' => round($totalMinutes / 60, 2),
                'manual_hours' => round($manualMinutes / 60, 2),
                'timer_hours' => round($timerMinutes / 60, 2),
                'entries_count' => (int) ($timeSummary->entries_count ?? 0),
            ];
        }

        $latestDeployment = null;
        if (Schema::hasTable('project_deployments')) {
            $latestDeployment = DB::table('project_deployments')
                ->where('project_id', $project->id)
                ->orderByDesc('deployed_at')
                ->orderByDesc('id')
                ->first();
        }

        $deploymentSummary = [
            'status' => $latestDeployment->status ?? ($project->status === 'completed' ? 'deployed' : 'pending'),
            'environment' => $latestDeployment->environment ?? null,
            'version' => $latestDeployment->version ?? null,
            'deployed_at' => $latestDeployment->deployed_at ?? ($project->deployment_date?->toDateString()),
            'maintenance_expiry' => $project->maintenance_expiry?->toDateString(),
        ];

        $workloadDistribution = collect($project->members ?? [])
            ->filter()
            ->map(function ($memberId) use ($staff, $taskAssignments) {
                $memberId = (int) $memberId;
                if (! isset($staff[$memberId])) {
                    return null;
                }

                $assignedTaskCount = (int) ($taskAssignments[$memberId] ?? 0);

                return [
                    'member_id' => $memberId,
                    'member_name' => trim(($staff[$memberId]->first_name ?? '').' '.($staff[$memberId]->last_name ?? '')),
                    'tasks_count' => $assignedTaskCount,
                ];
            })
            ->filter()
            ->sortByDesc('tasks_count')
            ->values();

        $taskStatusAnalytics = [
            'labels' => collect($usageDistribution)->pluck('label')->values()->all(),
            'counts' => collect($usageDistribution)->pluck('count')->values()->all(),
        ];

        $workloadAnalytics = [
            'labels' => $workloadDistribution->pluck('member_name')->values()->all(),
            'counts' => $workloadDistribution->pluck('tasks_count')->values()->all(),
        ];

        $overdueTrendLabels = [];
        $overdueTrendCounts = [];
        $overdueEndDate = now($this->businessTimezone())->startOfDay();
        $overdueCountsByDate = $tasks
            ->filter(function ($task) {
                return $task->deadline
                    && ! in_array($task->status, ['completed', 'cancelled'], true);
            })
            ->groupBy(function ($task) {
                return $task->deadline->copy()->startOfDay()->format('Y-m-d');
            })
            ->map(fn ($group) => $group->count());

        for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
            $date = $overdueEndDate->copy()->subDays($dayOffset);
            $label = $date->format('D');
            $count = (int) ($overdueCountsByDate[$date->format('Y-m-d')] ?? 0);

            $overdueTrendLabels[] = $label;
            $overdueTrendCounts[] = $count;
        }

        $milestoneCompletionRate = ($milestoneStats['total'] ?? 0) > 0
            ? round((($milestoneStats['completed'] ?? 0) / $milestoneStats['total']) * 100, 2)
            : 0;
        $milestoneCompletionAnalytics = [
            'completed' => (int) ($milestoneStats['completed'] ?? 0),
            'remaining' => max((int) ($milestoneStats['total'] ?? 0) - (int) ($milestoneStats['completed'] ?? 0), 0),
            'rate' => $milestoneCompletionRate,
        ];

        $sprintVelocityAnalytics = [
            'labels' => [],
            'velocities' => [],
        ];
        if (Schema::hasTable('sprints')) {
            $sprints = DB::table('sprints')
                ->where('project_id', $project->id)
                ->orderBy('start_date')
                ->orderBy('id')
                ->limit(8)
                ->get(['name', 'velocity']);

            $sprintVelocityAnalytics = [
                'labels' => $sprints->map(fn ($sprint) => $sprint->name ?: 'Sprint')->values()->all(),
                'velocities' => $sprints->map(fn ($sprint) => (int) ($sprint->velocity ?? 0))->values()->all(),
            ];
        }

        return view('project-details', compact(
            'project',
            'staff',
            'memberMetrics',
            'projectElapsedHours',
            'tasks',
            'projectFiles',
            'milestones',
            'milestoneStats',
            'issues',
            'issueStats',
            'projectComments',
            'usageDistribution',
            'usageChartLabels',
            'usageChartData',
            'weeklyActivityLabels',
            'weeklyActivityData',
            'totalTasks',
            'projectProgress',
            'recentActivities',
            'pendingIssues',
            'timeTrackingStats',
            'deploymentSummary',
            'workloadDistribution',
            'taskStatusAnalytics',
            'workloadAnalytics',
            'overdueTrendLabels',
            'overdueTrendCounts',
            'milestoneCompletionAnalytics',
            'sprintVelocityAnalytics'
        ));
    }

    public function ajaxCharts(int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->projectDashboardService->charts($projectId),
        ]);
    }

    public function ajaxActivityFeed(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $perPage = max(5, min(50, (int) $request->input('per_page', 15)));
        $feed = $this->projectDashboardService->activityFeed($projectId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $feed->items(),
            'meta' => [
                'current_page' => $feed->currentPage(),
                'last_page' => $feed->lastPage(),
                'total' => $feed->total(),
            ],
        ]);
    }

    public function ajaxMilestoneProgress(int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->projectDashboardService->milestoneProgress($projectId),
        ]);
    }

    public function ajaxTaskFilter(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $validated = $request->validate([
            'status' => 'nullable|string|in:not_started,pending,in_progress,completed,on_hold,cancelled',
            'priority' => 'nullable|string|in:low,medium,high',
            'q' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $tasks = $this->projectDashboardService->filteredTasks($projectId, $validated);

        return response()->json([
            'success' => true,
            'data' => $tasks->map(fn (Task $task) => [
                'id' => (int) $task->id,
                'title' => (string) $task->title,
                'status' => (string) $task->status,
                'workflow_status' => (string) ($task->workflow_status ?? 'backlog'),
                'priority' => (string) ($task->priority ?? 'medium'),
                'deadline' => optional($task->deadline)?->toDateString(),
            ])->values(),
            'meta' => ['count' => $tasks->count()],
        ]);
    }

    public function ajaxKanbanSnapshot(int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->projectDashboardService->kanbanBoard($projectId),
        ]);
    }

    public function storeMilestone(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $nextSortOrder = (int) ProjectMilestone::where('project_id', $projectId)->max('sort_order') + 1;
        $completedAt = $validated['status'] === 'completed' ? now($this->businessTimezone()) : null;

        $milestone = ProjectMilestone::create([
            'project_id' => $projectId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'completed_at' => $completedAt,
            'sort_order' => $nextSortOrder,
        ]);
        $this->milestoneProgressService->syncForMilestone((int) $milestone->id);
        $this->projectActivityService->log(
            (int) $project->id,
            'milestone_created',
            'Milestone Created',
            'Milestone created: '.$milestone->title,
            null,
            Auth::id(),
            ['milestone_id' => $milestone->id, 'status' => $milestone->status]
        );

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Milestone created successfully.');
    }

    public function updateMilestone(Request $request, $projectId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $milestone = ProjectMilestone::where('id', $milestoneId)->where('project_id', $projectId)->firstOrFail();
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $completedAt = $validated['status'] === 'completed'
            ? ($milestone->completed_at ?? now($this->businessTimezone()))
            : null;

        $milestone->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'completed_at' => $completedAt,
        ]);
        $this->milestoneProgressService->syncForMilestone((int) $milestone->id);
        $this->projectActivityService->log(
            (int) $project->id,
            'milestone_updated',
            'Milestone Updated',
            'Milestone updated: '.$milestone->title,
            null,
            Auth::id(),
            ['milestone_id' => $milestone->id, 'status' => $milestone->status]
        );

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Milestone updated successfully.');
    }

    public function destroyMilestone($projectId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $milestone = ProjectMilestone::where('id', $milestoneId)->where('project_id', $projectId)->firstOrFail();
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $milestoneTitle = $milestone->title;
        $milestone->delete();
        $this->projectActivityService->log(
            (int) $project->id,
            'milestone_deleted',
            'Milestone Deleted',
            'Milestone deleted: '.$milestoneTitle,
            null,
            Auth::id(),
            ['milestone_id' => (int) $milestoneId]
        );

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Milestone deleted successfully.');
    }

    public function storeIssue(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validated = $request->validate([
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        ProjectIssue::create([
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
        $issue = ProjectIssue::where('id', $issueId)->where('project_id', $projectId)->firstOrFail();
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

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
        $issue = ProjectIssue::where('id', $issueId)->where('project_id', $projectId)->firstOrFail();
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $issue->delete();

        return redirect()
            ->route('project-details', $projectId)
            ->with('success', 'Issue deleted successfully.');
    }

    public function storeComment(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to comment on this project.');

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        ProjectComment::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);
        $this->projectActivityService->log(
            (int) $project->id,
            'project_comment_added',
            'Project Comment Added',
            'A project comment was added.',
            null,
            Auth::id()
        );

        return redirect()
            ->route('project-details', [$projectId, 'tab' => 'comments'])
            ->with('success', 'Comment added successfully.');
    }

    private function createInitialStatusLog(Project $project): void
    {
        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status' => $project->status,
            'started_at' => now($this->businessTimezone()),
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
            if (! $openLog) {
                ProjectStatusLog::create([
                    'project_id' => $project->id,
                    'status' => $newStatus,
                    'started_at' => now($this->businessTimezone()),
                    'ended_at' => null,
                ]);
            }

            return;
        }

        if ($openLog) {
            $openLog->update([
                'ended_at' => now($this->businessTimezone()),
            ]);
        }

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status' => $newStatus,
            'started_at' => now($this->businessTimezone()),
            'ended_at' => null,
        ]);

        $this->projectActivityService->log(
            (int) $project->id,
            'project_status_changed',
            'Project Status Changed',
            'Project status changed.',
            null,
            Auth::id(),
            ['from' => $oldStatus, 'to' => $newStatus]
        );
    }

    private function getElapsedBoundary(): Carbon
    {
        return now($this->businessTimezone());
    }

    private function getProjectActiveIntervals(Project $project, Carbon $elapsedBoundary): array
    {
        $intervals = [];

        $inProgressLogs = $project->statusLogs->where('status', 'in_progress')->values();
        foreach ($inProgressLogs as $log) {
            $logStart = $this->toBusinessTz($log->started_at);
            if (! $logStart) {
                continue;
            }

            $logEnd = $log->ended_at
                ? $this->toBusinessTz($log->ended_at)
                : $elapsedBoundary->copy();

            $intervalEnd = $logEnd->lt($elapsedBoundary) ? $logEnd : $elapsedBoundary->copy();
            if ($intervalEnd->lte($logStart)) {
                continue;
            }

            $intervals[] = [$logStart, $intervalEnd];
        }

        if ($inProgressLogs->isEmpty() && $project->status === 'in_progress') {
            $fallbackStart = $this->getFallbackActiveStart($project);
            if ($fallbackStart && $elapsedBoundary->gt($fallbackStart)) {
                $intervals[] = [$fallbackStart, $elapsedBoundary->copy()];
            }
        }

        return $intervals;
    }

    private function calculateIntervalsElapsedMinutes(array $intervals): int
    {
        $minutes = 0;
        foreach ($intervals as [$start, $end]) {
            if ($end->gt($start)) {
                $minutes += $start->diffInMinutes($end);
            }
        }

        return $minutes;
    }

    private function calculateIntervalsElapsedMinutesWithinRange(array $intervals, Carbon $rangeStart, Carbon $rangeEnd): int
    {
        if ($rangeEnd->lte($rangeStart)) {
            return 0;
        }

        $minutes = 0;
        foreach ($intervals as [$start, $end]) {
            $overlapStart = $start->gt($rangeStart) ? $start : $rangeStart;
            $overlapEnd = $end->lt($rangeEnd) ? $end : $rangeEnd;

            if ($overlapEnd->lte($overlapStart)) {
                continue;
            }

            $minutes += $overlapStart->diffInMinutes($overlapEnd);
        }

        return $minutes;
    }

    private function getFallbackActiveStart(Project $project): ?Carbon
    {
        $createdAt = $this->toBusinessTz($project->created_at);
        if (! $project->start_date) {
            return $createdAt;
        }

        $startDateAtDayStart = Carbon::parse(
            $project->start_date->format('Y-m-d').' 00:00:00',
            $this->businessTimezone()
        );

        if (! $createdAt) {
            return $startDateAtDayStart;
        }

        return $createdAt->gt($startDateAtDayStart) ? $createdAt : $startDateAtDayStart;
    }

    private function toBusinessTz($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->setTimezone($this->businessTimezone());
    }

    private function businessTimezone(): string
    {
        return (string) config('app.timezone', self::DEFAULT_BUSINESS_TZ);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('project')->with('success', 'Project deleted successfully!');
    }

    public function deleteSelected(Request $request)
    {
        $ids = array_filter(explode(',', (string) $request->ids));

        if (empty($ids)) {
            return redirect()->route('project')->with('error', 'No projects selected for deletion.');
        }

        try {
            foreach ($ids as $id) {
                $project = Project::find($id);
                if ($project) {
                    $project->delete();
                }
            }

            return redirect()->route('project')->with('success', 'Selected projects deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('project')->with('error', 'Failed to delete selected projects: '.$e->getMessage());
        }
    }

    /**
     * Store a project file
     */
    private function storeProjectFile($projectId, $file): ProjectFile
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid().'_'.time().'.'.$extension;
        $fileSize = $file->getSize();
        $fileType = $file->getMimeType();

        // Create directory if it doesn't exist
        $directory = public_path('uploads/project_files/'.$projectId);
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Store the file
        $file->move($directory, $fileName);

        return ProjectFile::create([
            'project_id' => $projectId,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'file_path' => 'uploads/project_files/'.$projectId.'/'.$fileName,
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
            $uploadedFile = $this->storeProjectFile($projectId, $request->file('file'));
            $this->projectActivityService->log(
                (int) $projectId,
                'project_file_uploaded',
                'File Uploaded',
                'A file was uploaded to the project.',
                null,
                Auth::id(),
                ['file_id' => $uploadedFile->id ?? null]
            );

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

        if (! file_exists($filePath)) {
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

    private function sendProjectCreationNotifications(Project $project): void
    {
        $project->loadMissing(['customer', 'customerUser']);

        $adminEmail = trim((string) Setting::get('company_email', ''));
        if ($adminEmail !== '') {
            try {
                Mail::to($adminEmail)->send(new ProjectCreatedMail($project, 'admin'));
            } catch (\Throwable $e) {
                Log::error('Failed to send project creation email to admin: '.$e->getMessage(), [
                    'project_id' => $project->id,
                    'admin_email' => $adminEmail,
                ]);
            }
        }

        $memberEmails = User::staffMembers()
            ->whereIn('id', collect($project->members ?? [])->filter()->map(fn ($id) => (int) $id)->all())
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($email) => trim((string) $email))
            ->filter()
            ->unique()
            ->values();

        foreach ($memberEmails as $memberEmail) {
            try {
                Mail::to($memberEmail)->send(new ProjectCreatedMail($project, 'member'));
            } catch (\Throwable $e) {
                Log::error('Failed to send project creation email to member: '.$e->getMessage(), [
                    'project_id' => $project->id,
                    'member_email' => $memberEmail,
                ]);
            }
        }
    }

    /**
     * Customer's own projects - simplified view for customers
     */
    public function myProjects()
    {
        $clientUser = $this->getLoggedInClient();

        if (! $clientUser) {
            // Redirect non-customers to the main project page
            return redirect()->route('project');
        }

        // Customer can only see their own projects
        $projects = Project::with(['customer', 'customerUser'])
            ->whereIn('customer_id', $this->clientProjectOwnerIds($clientUser))
            ->get();
        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');
        $allProjects = $projects->count();
        $planningProjects = $projects->where('status', 'not_started')->count();
        $inProgressProjects = $projects->where('status', 'in_progress')->count();
        $onHoldProjects = $projects->where('status', 'on_hold')->count();
        $completedProjects = $projects->where('status', 'completed')->count();
        $cancelledProjects = $projects->where('status', 'cancelled')->count();

        $customer = $clientUser;

        return view('customer-projects', compact('projects', 'staff', 'allProjects', 'planningProjects', 'inProgressProjects', 'onHoldProjects', 'completedProjects', 'cancelledProjects', 'customer'));
    }
}
