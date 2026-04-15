<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ProjectCreatedMail;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectIssue;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusLog;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    private const DEFAULT_BUSINESS_TZ = 'UTC';

    public function apiFormOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'customers' => Customer::query()
                    ->orderBy('client_name')
                    ->get()
                    ->map(fn (Customer $customer) => [
                        'id' => $customer->id,
                        'name' => $this->formatCustomerName($customer),
                        'email' => $customer->email,
                    ])
                    ->values(),
                'staff' => Staff::query()
                    ->orderBy('first_name')
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
                'billing_types' => ['fixed_rate', 'hourly_rate'],
                'milestone_statuses' => ['pending', 'in_progress', 'completed'],
                'issue_statuses' => ['open', 'in_progress', 'resolved', 'closed'],
                'issue_priorities' => ['low', 'medium', 'high'],
            ],
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $projects = $this->visibleProjectsQuery()
            ->with(['customer', 'statusLogs'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->input('customer_id')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('project_name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $projects->map(fn (Project $project) => $this->formatProjectListResource($project))->values(),
            'meta' => [
                'counts' => [
                    'all' => $projects->count(),
                    'not_started' => $projects->where('status', 'not_started')->count(),
                    'in_progress' => $projects->where('status', 'in_progress')->count(),
                    'on_hold' => $projects->where('status', 'on_hold')->count(),
                    'completed' => $projects->where('status', 'completed')->count(),
                    'cancelled' => $projects->where('status', 'cancelled')->count(),
                ],
            ],
        ]);
    }

    public function apiShow($id): JsonResponse
    {
        $project = Project::with([
            'customer',
            'statusLogs' => fn ($query) => $query->orderBy('started_at'),
        ])->findOrFail($id);

        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->buildProjectDetailPayload($project),
        ]);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->projectValidationRules(true));

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $project = Project::create($this->buildProjectPayload($validator->validated()));

            if ($request->hasFile('project_files')) {
                foreach ($request->file('project_files') as $file) {
                    $this->storeProjectFile($project->id, $file);
                }
            }

            $this->createInitialStatusLog($project);
            $this->sendProjectCreationNotifications($project);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully.',
                'data' => $this->buildProjectDetailPayload($project->fresh(['customer', 'statusLogs'])),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $project = Project::with('statusLogs')->findOrFail($id);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validator = Validator::make($request->all(), $this->projectValidationRules(true));

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();
            $oldStatus = $project->status;
            $newStatus = $validated['status'] ?? 'not_started';

            $project->update($this->buildProjectPayload($validated));

            if ($request->hasFile('project_files')) {
                foreach ($request->file('project_files') as $file) {
                    $this->storeProjectFile($project->id, $file);
                }
            }

            $this->syncStatusTimeline($project->fresh('statusLogs'), $oldStatus, $newStatus);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully.',
                'data' => $this->buildProjectDetailPayload($project->fresh(['customer', 'statusLogs'])),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiDestroy($id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $this->authorizeProjectAccess($project, 'You are not authorized to delete this project.');

        try {
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiMilestoneIndex($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $milestones = ProjectMilestone::query()
            ->where('project_id', $projectId)
            ->orderBy('sort_order')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $milestones->map(fn (ProjectMilestone $milestone) => $this->formatMilestoneResource($milestone))->values(),
        ]);
    }

    public function apiStoreMilestone(Request $request, $projectId): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Milestone created successfully.',
            'data' => $this->formatMilestoneResource($milestone),
        ], 201);
    }

    public function apiUpdateMilestone(Request $request, $projectId, $milestoneId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $milestone = ProjectMilestone::where('project_id', $projectId)
            ->where('id', $milestoneId)
            ->firstOrFail();

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

        return response()->json([
            'success' => true,
            'message' => 'Milestone updated successfully.',
            'data' => $this->formatMilestoneResource($milestone->fresh()),
        ]);
    }

    public function apiDestroyMilestone($projectId, $milestoneId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $milestone = ProjectMilestone::where('project_id', $projectId)
            ->where('id', $milestoneId)
            ->firstOrFail();

        $milestone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Milestone deleted successfully.',
        ]);
    }

    public function apiIssueIndex($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $issues = ProjectIssue::query()
            ->where('project_id', $projectId)
            ->with('customer')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $issues->map(fn (ProjectIssue $issue) => $this->formatIssueResource($issue))->values(),
        ]);
    }

    public function apiStoreIssue(Request $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validated = $request->validate([
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $issue = ProjectIssue::create([
            'project_id' => $projectId,
            'customer_id' => $project->customer_id,
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Issue created successfully.',
            'data' => $this->formatIssueResource($issue->loadMissing('customer')),
        ], 201);
    }

    public function apiUpdateIssue(Request $request, $projectId, $issueId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $issue = ProjectIssue::where('project_id', $projectId)
            ->where('id', $issueId)
            ->firstOrFail();

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

        return response()->json([
            'success' => true,
            'message' => 'Issue updated successfully.',
            'data' => $this->formatIssueResource($issue->fresh()->loadMissing('customer')),
        ]);
    }

    public function apiDestroyIssue($projectId, $issueId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $issue = ProjectIssue::where('project_id', $projectId)
            ->where('id', $issueId)
            ->firstOrFail();

        $issue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Issue deleted successfully.',
        ]);
    }

    public function apiCommentIndex($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $comments = ProjectComment::query()
            ->where('project_id', $projectId)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments->map(fn (ProjectComment $comment) => $this->formatCommentResource($comment))->values(),
        ]);
    }

    public function apiStoreComment(Request $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to comment on this project.');

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = ProjectComment::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'data' => $this->formatCommentResource($comment->loadMissing('user')),
        ], 201);
    }

    public function apiFileIndex($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $files = ProjectFile::query()
            ->where('project_id', $projectId)
            ->with('uploader')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $files->map(fn (ProjectFile $file) => $this->formatFileResource($file))->values(),
        ]);
    }

    public function apiUploadFile(Request $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpeg,jpg,png,gif,svg,webp,bmp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
        ]);

        $projectFile = $this->storeProjectFile($projectId, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data' => $this->formatFileResource($projectFile->loadMissing('uploader')),
        ], 201);
    }

    public function apiDeleteFile($projectId, $fileId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to delete project files.');

        $file = ProjectFile::where('project_id', $projectId)
            ->where('id', $fileId)
            ->firstOrFail();

        $filePath = public_path($file->file_path);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
        ]);
    }

    public function apiUsage($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $tasks = Task::where('project_id', $projectId)->get();
        $totalTasks = $tasks->count();

        $statuses = ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        $usage = [];

        foreach ($statuses as $status) {
            $count = $tasks->where('status', $status)->count();
            $percentage = $totalTasks > 0 ? round(($count / $totalTasks) * 100, 1) : 0;
            $usage[$status] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'total_tasks' => $totalTasks,
                'usage' => [
                    'not_started' => [
                        'count' => $usage['not_started']['count'],
                        'percentage' => $usage['not_started']['percentage'],
                    ],
                    'in_progress' => [
                        'count' => $usage['in_progress']['count'],
                        'percentage' => $usage['in_progress']['percentage'],
                    ],
                    'on_hold' => [
                        'count' => $usage['on_hold']['count'],
                        'percentage' => $usage['on_hold']['percentage'],
                    ],
                    'completed' => [
                        'count' => $usage['completed']['count'],
                        'percentage' => $usage['completed']['percentage'],
                    ],
                    'cancelled' => [
                        'count' => $usage['cancelled']['count'],
                        'percentage' => $usage['cancelled']['percentage'],
                    ],
                ],
            ],
        ]);
    }

    private function getLoggedInCustomer(): ?Customer
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->relationLoaded('customer') && $user->customer) {
            return $user->customer;
        }

        return Customer::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
    }

    private function getLoggedInStaff(): ?Staff
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->relationLoaded('staff') && $user->staff) {
            return $user->staff;
        }

        return Staff::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
    }

    private function isPrivilegedProjectUser(): bool
    {
        $user = Auth::user();

        return (bool) ($user && $user->hasAnyRole(['super-admin', 'super_admin', 'admin']));
    }

    private function visibleProjectsQuery()
    {
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            return Project::query()->where('customer_id', $customer->id);
        }

        if ($this->isPrivilegedProjectUser()) {
            return Project::query();
        }

        $staff = $this->getLoggedInStaff();
        if (! $staff) {
            return Project::query();
        }

        return Project::query()->where(function ($query) use ($staff) {
            $query->whereJsonContains('members', $staff->id)
                ->orWhereJsonContains('members', (string) $staff->id);
        });
    }

    private function authorizeProjectAccess(Project $project, string $message = 'You are not authorized to view this project.'): void
    {
        if ($this->isPrivilegedProjectUser()) {
            return;
        }

        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            abort_if($project->customer_id !== $customer->id, 403, $message);

            return;
        }

        $staff = $this->getLoggedInStaff();
        if (! $staff) {
            return;
        }

        $memberIds = collect($project->members ?? [])
            ->filter(fn ($memberId) => $memberId !== null && $memberId !== '')
            ->map(fn ($memberId) => (int) $memberId);

        abort_if(! $memberIds->contains((int) $staff->id), 403, $message);
    }

    private function projectValidationRules(bool $includeFiles = true): array
    {
        $rules = [
            'project_name' => 'required|string|max:255',
            'customer' => 'nullable|exists:customers,id',
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
        ];

        if ($includeFiles) {
            $rules['project_files'] = 'nullable|array';
            $rules['project_files.*'] = 'file|max:10240|mimes:jpeg,jpg,png,gif,svg,webp,bmp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar';
        }

        return $rules;
    }

    private function buildProjectPayload(array $validated): array
    {
        return [
            'project_name' => $validated['project_name'],
            'customer_id' => $validated['customer'] ?? null,
            'status' => $validated['status'] ?? 'not_started',
            'start_date' => $validated['start_date'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'billing_type' => $validated['billing_type'] ?? null,
            'total_rate' => $validated['total_rate'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'members' => $validated['members'] ?? null,
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'technologies' => $validated['technologies'] ?? null,
        ];
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

            $logEnd = $log->ended_at ? $this->toBusinessTz($log->ended_at) : $elapsedBoundary->copy();
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

    private function buildProjectDetailPayload(Project $project): array
    {
        $project->loadMissing([
            'customer',
            'statusLogs' => fn ($query) => $query->orderBy('started_at'),
        ]);

        $elapsedBoundary = $this->getElapsedBoundary();
        $inProgressIntervals = $this->getProjectActiveIntervals($project, $elapsedBoundary);
        $projectElapsedMinutes = $this->calculateIntervalsElapsedMinutes($inProgressIntervals);

        $tasks = Task::where('project_id', $project->id)->latest()->get();
        $projectFiles = ProjectFile::where('project_id', $project->id)->with('uploader')->latest()->get();
        $milestones = ProjectMilestone::where('project_id', $project->id)->orderBy('sort_order')->orderBy('due_date')->orderBy('id')->get();
        $issues = ProjectIssue::where('project_id', $project->id)->with('customer')->latest()->get();
        $comments = ProjectComment::where('project_id', $project->id)->with('user')->latest()->get();

        $issueStats = [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];

        $overdueTasks = $tasks->filter(function ($task) {
            return $task->deadline
                && $task->deadline->isPast()
                && ! in_array($task->status, ['completed', 'cancelled'], true);
        })->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $notStartedTasks = $tasks->where('status', 'not_started')->count();

        $completedTasks = $tasks->where('status', 'completed')->count();
        $resolvedIssues = $issueStats['resolved'] + $issueStats['closed'];
        $milestoneStats = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('status', 'completed')->count(),
            'in_progress' => $milestones->where('status', 'in_progress')->count(),
            'pending' => $milestones->where('status', 'pending')->count(),
        ];
        $progressSignals = [
            $tasks->count() > 0 ? ($completedTasks / $tasks->count()) * 100 : null,
            $milestoneStats['total'] > 0 ? (($milestoneStats['completed'] / $milestoneStats['total']) * 100) : null,
            $issueStats['total'] > 0 ? (($resolvedIssues / $issueStats['total']) * 100) : null,
        ];

        if ($project->status === 'completed') {
            $overallProgress = 100;
        } elseif ($project->status === 'cancelled') {
            $overallProgress = 0;
        } else {
            $availableSignals = array_values(array_filter($progressSignals, fn ($value) => $value !== null));
            $overallProgress = ! empty($availableSignals)
                ? (int) round(array_sum($availableSignals) / count($availableSignals))
                : match ($project->status) {
                    'in_progress' => 45,
                    'on_hold' => 20,
                    default => 0,
                };
        }

        $overallProgress = max(0, min(100, $overallProgress));

        return [
            'project' => $this->formatProjectResource($project),
            'stats' => [
                'elapsed_hours' => round($projectElapsedMinutes / 60, 1),
                'tasks' => [
                    'total' => $tasks->count(),
                    'completed' => $completedTasks,
                    'in_progress' => $inProgressTasks,
                    'not_started' => $notStartedTasks,
                    'overdue' => $overdueTasks,
                    'remaining' => max($tasks->count() - $completedTasks, 0),
                ],
                'milestones' => $milestoneStats,
                'issues' => $issueStats,
                'progress' => [
                    'overall' => $overallProgress,
                    'resolved_issues' => $resolvedIssues,
                ],
            ],
            'tasks' => $tasks->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'start_date' => optional($task->start_date)?->toDateString(),
                'deadline' => optional($task->deadline)?->toDateString(),
                'assignees' => $task->assignees ?? [],
                'followers' => $task->followers ?? [],
                'tags' => $task->tags ?? [],
                'created_at' => optional($task->created_at)?->toISOString(),
                'updated_at' => optional($task->updated_at)?->toISOString(),
            ])->values(),
            'files' => $projectFiles->map(fn (ProjectFile $file) => $this->formatFileResource($file))->values(),
            'milestones' => $milestones->map(fn (ProjectMilestone $milestone) => $this->formatMilestoneResource($milestone))->values(),
            'issues' => $issues->map(fn (ProjectIssue $issue) => $this->formatIssueResource($issue))->values(),
            'comments' => $comments->map(fn (ProjectComment $comment) => $this->formatCommentResource($comment))->values(),
            'status_logs' => $project->statusLogs->map(fn (ProjectStatusLog $log) => [
                'id' => $log->id,
                'status' => $log->status,
                'started_at' => optional($log->started_at)?->toISOString(),
                'ended_at' => optional($log->ended_at)?->toISOString(),
            ])->values(),
        ];
    }

    private function formatProjectListResource(Project $project): array
    {
        $tasks = Task::where('project_id', $project->id)->latest()->get();
        $issues = ProjectIssue::where('project_id', $project->id)->with('customer')->latest()->get();
        $milestones = ProjectMilestone::where('project_id', $project->id)->orderBy('sort_order')->orderBy('due_date')->orderBy('id')->get();

        $issueStats = [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];
        $resolvedIssues = $issueStats['resolved'] + $issueStats['closed'];
        $completedTasks = $tasks->where('status', 'completed')->count();

        $milestoneStats = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('status', 'completed')->count(),
            'in_progress' => $milestones->where('status', 'in_progress')->count(),
            'pending' => $milestones->where('status', 'pending')->count(),
        ];

        $progressSignals = [
            $tasks->count() > 0 ? ($completedTasks / $tasks->count()) * 100 : null,
            $milestoneStats['total'] > 0 ? (($milestoneStats['completed'] / $milestoneStats['total']) * 100) : null,
            $issueStats['total'] > 0 ? (($resolvedIssues / $issueStats['total']) * 100) : null,
        ];

        if ($project->status === 'completed') {
            $overallProgress = 100;
        } elseif ($project->status === 'cancelled') {
            $overallProgress = 0;
        } else {
            $availableSignals = array_values(array_filter($progressSignals, fn ($value) => $value !== null));
            $overallProgress = ! empty($availableSignals)
                ? (int) round(array_sum($availableSignals) / count($availableSignals))
                : match ($project->status) {
                    'in_progress' => 45,
                    'on_hold' => 20,
                    default => 0,
                };
        }

        $overallProgress = max(0, min(100, $overallProgress));

        return [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'status' => $project->status,
            'priority' => $project->priority,
            'customer' => $project->customer ? [
                'id' => $project->customer->id,
                'name' => $this->formatCustomerName($project->customer),
                'email' => $project->customer->email,
            ] : null,
            'start_date' => optional($project->start_date)?->toDateString(),
            'deadline' => optional($project->deadline)?->toDateString(),
            'members' => $project->membersList(),
            'tags' => $project->tags ?? [],
            'technologies' => $project->technologies ?? [],
            'progress' => [
                'overall' => $overallProgress,
                'resolved_issues' => $resolvedIssues,
            ],
            'created_at' => optional($project->created_at)?->toISOString(),
            'updated_at' => optional($project->updated_at)?->toISOString(),
            'links' => [
                'web' => [
                    'details' => route('project-details', $project->id),
                ],
                'api' => [
                    'show' => url('/api/v1/projects/'.$project->id),
                    'update' => url('/api/v1/projects/'.$project->id),
                    'delete' => url('/api/v1/projects/'.$project->id),
                    'milestones' => url('/api/v1/projects/'.$project->id.'/milestones'),
                    'issues' => url('/api/v1/projects/'.$project->id.'/issues'),
                    'comments' => url('/api/v1/projects/'.$project->id.'/comments'),
                    'files' => url('/api/v1/projects/'.$project->id.'/files'),
                ],
            ],
        ];
    }

    private function formatProjectResource(Project $project): array
    {
        return [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'customer_id' => $project->customer_id,
            'customer' => $project->customer ? [
                'id' => $project->customer->id,
                'name' => $this->formatCustomerName($project->customer),
                'email' => $project->customer->email,
            ] : null,
            'status' => $project->status,
            'start_date' => optional($project->start_date)?->toDateString(),
            'deadline' => optional($project->deadline)?->toDateString(),
            'billing_type' => $project->billing_type,
            'total_rate' => $project->total_rate !== null ? (float) $project->total_rate : null,
            'estimated_hours' => $project->estimated_hours,
            'tags' => $project->tags ?? [],
            'members' => $project->membersList(),
            'description' => $project->description,
            'priority' => $project->priority,
            'technologies' => $project->technologies ?? [],
            'created_at' => optional($project->created_at)?->toISOString(),
            'updated_at' => optional($project->updated_at)?->toISOString(),
            'links' => $this->formatProjectListResource($project)['links'],
        ];
    }

    private function formatMilestoneResource(ProjectMilestone $milestone): array
    {
        return [
            'id' => $milestone->id,
            'project_id' => $milestone->project_id,
            'title' => $milestone->title,
            'description' => $milestone->description,
            'status' => $milestone->status,
            'due_date' => optional($milestone->due_date)?->toDateString(),
            'completed_at' => optional($milestone->completed_at)?->toISOString(),
            'sort_order' => $milestone->sort_order,
            'created_at' => optional($milestone->created_at)?->toISOString(),
            'updated_at' => optional($milestone->updated_at)?->toISOString(),
        ];
    }

    private function formatIssueResource(ProjectIssue $issue): array
    {
        return [
            'id' => $issue->id,
            'project_id' => $issue->project_id,
            'customer_id' => $issue->customer_id,
            'customer' => $issue->customer ? [
                'id' => $issue->customer->id,
                'name' => $this->formatCustomerName($issue->customer),
                'email' => $issue->customer->email,
            ] : null,
            'issue_description' => $issue->issue_description,
            'priority' => $issue->priority,
            'status' => $issue->status,
            'created_at' => optional($issue->created_at)?->toISOString(),
            'updated_at' => optional($issue->updated_at)?->toISOString(),
        ];
    }

    private function formatCommentResource(ProjectComment $comment): array
    {
        return [
            'id' => $comment->id,
            'project_id' => $comment->project_id,
            'user_id' => $comment->user_id,
            'user' => $comment->user ? [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'email' => $comment->user->email,
            ] : null,
            'comment' => $comment->comment,
            'created_at' => optional($comment->created_at)?->toISOString(),
            'updated_at' => optional($comment->updated_at)?->toISOString(),
        ];
    }

    private function formatCustomerName(Customer $customer): string
    {
        $name = trim((string) ($customer->client_name ?? ''));

        if ($name !== '') {
            return $name;
        }

        $contactPerson = trim((string) ($customer->contact_person ?? ''));

        if ($contactPerson !== '') {
            return $contactPerson;
        }

        return 'Customer #'.$customer->id;
    }

    private function formatFileResource(ProjectFile $file): array
    {
        return [
            'id' => $file->id,
            'project_id' => $file->project_id,
            'file_name' => $file->file_name,
            'original_name' => $file->original_name,
            'file_type' => $file->file_type,
            'file_size' => $file->file_size,
            'formatted_size' => $file->formatted_size,
            'file_path' => $file->file_path,
            'file_icon' => $file->file_icon,
            'uploaded_by' => $file->uploaded_by,
            'uploader' => $file->uploader ? [
                'id' => $file->uploader->id,
                'name' => $file->uploader->name,
                'email' => $file->uploader->email,
            ] : null,
            'created_at' => optional($file->created_at)?->toISOString(),
            'updated_at' => optional($file->updated_at)?->toISOString(),
            'links' => [
                'download' => route('project.file.download', $file->id),
                'delete' => url('/api/v1/projects/'.$file->project_id.'/files/'.$file->id),
            ],
        ];
    }

    private function storeProjectFile($projectId, $file): ProjectFile
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid().'_'.time().'.'.$extension;
        $fileSize = $file->getSize();
        $fileType = $file->getMimeType();

        $directory = public_path('uploads/project_files/'.$projectId);
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

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

    private function sendProjectCreationNotifications(Project $project): void
    {
        $project->loadMissing('customer');

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

        $memberEmails = Staff::query()
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
}
