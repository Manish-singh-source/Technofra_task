<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectManagement\KanbanMoveRequest;
use App\Http\Requests\ProjectManagement\ProjectTaskFilterRequest;
use App\Mail\ProjectCreatedMail;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectComment;
use App\Models\ProjectChangeRequest;
use App\Models\ProjectFile;
use App\Models\ProjectIssue;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusLog;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use App\Services\ProjectManagement\MilestoneProgressService;
use App\Services\ProjectManagement\ProjectActivityService;
use App\Services\ProjectManagement\ProjectDashboardService;
use App\Services\ProjectManagement\ProjectLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    private const DEFAULT_BUSINESS_TZ = 'UTC';

    public function __construct(
        private ProjectLifecycleService $projectLifecycleService,
        private MilestoneProgressService $milestoneProgressService,
        private ProjectActivityService $projectActivityService,
        private ProjectDashboardService $projectDashboardService
    ) {}

    public function apiFormOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'clients' => User::query()
                    ->where('role', 'client')
                    ->orderBy('first_name')
                    ->get()
                    ->map(fn(User $user) => [
                        'id' => $user->id,
                        'name' => $this->formatUserName($user),
                        'email' => $user->email,
                    ])
                    ->values(),
                'staff' => User::query()
                    ->where('role', 'staff')
                    ->where('status', 'active')
                    ->orderBy('first_name')
                    ->get()
                    ->map(fn(User $member) => [
                        'id' => $member->id,
                        'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
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

    public function index(Request $request)
    {
        $projectsQuery = Project::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('project_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            });

        $projectsCount = (clone $projectsQuery)->count();
        $inProgressCount = (clone $projectsQuery)->where('status', 'in_progress')->count();

        $projects = (clone $projectsQuery)
            ->with('customer:id,first_name,last_name,email,profile_image')
            ->latest()
            ->paginate(10);

        $collection = $projects->getCollection();
        $projectIds = $collection->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $projectStats = $this->buildProjectListStatsMap($projectIds);
        $staffLookup = $this->buildProjectMemberLookup($collection);

        $projects->getCollection()->transform(function (Project $project) use ($projectStats, $staffLookup) {
            $stats = $projectStats[$project->id] ?? null;
            $project->setAttribute('staffMembers', collect($this->resolveProjectMembers($project, $staffLookup)));
            $project->setAttribute('progress', $this->calculateProgress($project, $stats));

            return $project;
        });

        $data = [
            'projects_count' => $projectsCount,
            'in_progress_count' => $inProgressCount,
            'projects' => $projects,
        ];

        return ApiResponse::success($data, 'Projects retrieved successfully.');
    }


    public function apiIndex(Request $request): JsonResponse
    {
        $projects = $this->visibleProjectsQuery()
            ->with(['customer', 'statusLogs'])
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('customer_id'), fn($query) => $query->where('customer_id', $request->input('customer_id')))
            ->when($request->filled('priority'), fn($query) => $query->where('priority', $request->input('priority')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('project_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        $projectIds = $projects->pluck('id')->map(fn ($id) => (int) $id)->values();
        $projectStats = $this->buildProjectListStatsMap($projectIds->all());
        $staffLookup = $this->buildProjectMemberLookup($projects);

        return response()->json([
            'success' => true,
            'data' => $projects->map(fn(Project $project) => $this->formatProjectListResource($project, $projectStats[$project->id] ?? null, $staffLookup))->values(),
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
            'statusLogs' => fn($query) => $query->orderBy('started_at'),
        ])->findOrFail($id);

        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->buildProjectDetailPayload($project),
        ]);
    }

    public function apiDetails($projectId): JsonResponse
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
                'created_at',
                'updated_at',
            ])
            ->with([
                'customer',
                'customerUser.address',
                'customerUser',
                'statusLogs' => fn ($query) => $query->orderBy('started_at'),
            ])
            ->findOrFail($projectId);

        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->buildProjectDetailsDashboardPayload($project),
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
            $validated = $validator->validated();
            $this->projectLifecycleService->ensureValidStage($validated['lifecycle_stage'] ?? null);
            $project = Project::create($this->buildProjectPayload($validated));

            if ($request->hasFile('project_files')) {
                foreach ($request->file('project_files') as $file) {
                    $this->storeProjectFile($project->id, $file);
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully.',
                'data' => $this->buildProjectDetailPayload($project->fresh(['customer', 'statusLogs'])),
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
                'message' => 'Failed to create project: ' . $e->getMessage(),
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
            $this->projectLifecycleService->ensureValidStage($validated['lifecycle_stage'] ?? $project->lifecycle_stage);

            $project->update($this->buildProjectPayload($validated));

            if ($request->hasFile('project_files')) {
                foreach ($request->file('project_files') as $file) {
                    $this->storeProjectFile($project->id, $file);
                }
            }

            $this->syncStatusTimeline($project->fresh('statusLogs'), $oldStatus, $newStatus);
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully.',
                'data' => $this->buildProjectDetailPayload($project->fresh(['customer', 'statusLogs'])),
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
                'message' => 'Failed to update project: ' . $e->getMessage(),
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
                'message' => 'Failed to delete project: ' . $e->getMessage(),
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
            'data' => $milestones->map(fn(ProjectMilestone $milestone) => $this->formatMilestoneResource($milestone))->values(),
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

        return response()->json([
            'success' => true,
            'message' => 'Milestone deleted successfully.',
        ]);
    }

    public function apiChangeRequestIndex($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $requests = ProjectChangeRequest::query()
            ->where('project_id', $project->id)
            ->with(['requester:id,first_name,last_name,email', 'approver:id,first_name,last_name,email'])
            ->latest('requested_at')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests->map(fn (ProjectChangeRequest $changeRequest) => $this->formatChangeRequestResource($changeRequest))->values(),
        ]);
    }

    public function apiStoreChangeRequest(Request $request, $projectId): JsonResponse
    {
        if (($auth = $this->authorizeProjectWriteAction('edit_projects')) !== null) {
            return $auth;
        }

        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'impact_level' => 'nullable|in:low,medium,high,critical',
        ]);

        $changeRequest = ProjectChangeRequest::create([
            'project_id' => $project->id,
            'requested_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'impact_level' => $validated['impact_level'] ?? null,
            'status' => 'requested',
            'requested_at' => now(),
        ]);

        $this->projectActivityService->log(
            (int) $project->id,
            'project_change_request_created',
            'Project Change Request Created',
            'Change request created: '.$changeRequest->title,
            null,
            Auth::id(),
            [
                'change_request_id' => $changeRequest->id,
                'status' => $changeRequest->status,
                'impact_level' => $changeRequest->impact_level,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Change request created successfully.',
            'data' => $this->formatChangeRequestResource($changeRequest->fresh(['requester', 'approver'])),
        ], 201);
    }

    public function apiUpdateChangeRequestStatus(Request $request, $projectId, $changeRequestId): JsonResponse
    {
        if (($auth = $this->authorizeProjectWriteAction('edit_projects')) !== null) {
            return $auth;
        }

        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project.');

        $changeRequest = ProjectChangeRequest::query()
            ->where('project_id', $project->id)
            ->where('id', $changeRequestId)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:requested,analysis,approved,rejected,implemented',
            'description' => 'nullable|string',
            'impact_level' => 'nullable|in:low,medium,high,critical',
        ]);

        $oldStatus = (string) $changeRequest->status;
        $newStatus = (string) $validated['status'];
        $this->ensureValidChangeRequestTransition($oldStatus, $newStatus);

        $payload = [
            'status' => $newStatus,
        ];

        if (array_key_exists('description', $validated)) {
            $payload['description'] = $validated['description'];
        }
        if (array_key_exists('impact_level', $validated)) {
            $payload['impact_level'] = $validated['impact_level'];
        }
        if ($newStatus === 'approved') {
            $payload['approved_by'] = Auth::id();
            $payload['approved_at'] = now();
        }
        if ($newStatus !== 'approved') {
            $payload['approved_by'] = $changeRequest->approved_by;
            $payload['approved_at'] = $changeRequest->approved_at;
        }

        $changeRequest->update($payload);

        $this->projectActivityService->log(
            (int) $project->id,
            'project_change_request_updated',
            'Project Change Request Updated',
            'Change request status updated: '.$changeRequest->title,
            null,
            Auth::id(),
            [
                'change_request_id' => $changeRequest->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Change request updated successfully.',
            'data' => $this->formatChangeRequestResource($changeRequest->fresh(['requester', 'approver'])),
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
            'data' => $issues->map(fn(ProjectIssue $issue) => $this->formatIssueResource($issue))->values(),
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
            'data' => $comments->map(fn(ProjectComment $comment) => $this->formatCommentResource($comment))->values(),
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
        $this->projectActivityService->log(
            (int) $project->id,
            'project_comment_added',
            'Project Comment Added',
            'A project comment was added.',
            null,
            Auth::id()
        );

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
            'data' => $files->map(fn(ProjectFile $file) => $this->formatFileResource($file))->values(),
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
        $this->projectActivityService->log(
            (int) $projectId,
            'project_file_uploaded',
            'File Uploaded',
            'A file was uploaded to the project.',
            null,
            Auth::id(),
            ['file_id' => $projectFile->id]
        );

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

    public function apiKanbanBoard($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->projectDashboardService->kanbanBoard((int) $projectId),
        ]);
    }

    public function apiKanbanMove(KanbanMoveRequest $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project, 'You are not authorized to update this project board.');

        $task = Task::query()
            ->where('project_id', $projectId)
            ->where('id', $request->integer('task_id'))
            ->firstOrFail();

        $movedTask = $this->projectDashboardService->moveKanbanTask($project, $task, (string) $request->input('to_column'));

        if ($movedTask->milestone_id) {
            $this->milestoneProgressService->syncForMilestone((int) $movedTask->milestone_id);
        }

        $this->projectActivityService->log(
            (int) $project->id,
            'task_kanban_moved_api',
            'Task moved on API Kanban board',
            'Task moved via API Kanban board: '.$movedTask->title,
            (int) $movedTask->id,
            Auth::id(),
            [
                'status' => $movedTask->status,
                'workflow_status' => $movedTask->workflow_status,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Task moved successfully.',
            'data' => [
                'task_id' => (int) $movedTask->id,
                'status' => $movedTask->status,
                'workflow_status' => $movedTask->workflow_status,
            ],
        ]);
    }

    public function apiCharts($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->projectDashboardService->charts((int) $projectId),
        ]);
    }

    public function apiActivityFeed(Request $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $perPage = max(5, min(50, (int) $request->input('per_page', 15)));
        $feed = $this->projectDashboardService->activityFeed((int) $projectId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $feed->items(),
            'meta' => [
                'current_page' => $feed->currentPage(),
                'last_page' => $feed->lastPage(),
                'per_page' => $feed->perPage(),
                'total' => $feed->total(),
            ],
        ]);
    }

    public function apiMilestoneProgress($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        return response()->json([
            'success' => true,
            'data' => $this->projectDashboardService->milestoneProgress((int) $projectId),
        ]);
    }

    public function apiFilterTasks(ProjectTaskFilterRequest $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProjectAccess($project);

        $tasks = $this->projectDashboardService->filteredTasks((int) $projectId, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $tasks->map(fn (Task $task) => [
                'id' => (int) $task->id,
                'title' => (string) $task->title,
                'status' => (string) $task->status,
                'workflow_status' => (string) ($task->workflow_status ?? 'backlog'),
                'priority' => (string) ($task->priority ?? 'medium'),
                'deadline' => optional($task->deadline)?->toDateString(),
                'assignees' => is_array($task->assignees) ? $task->assignees : [],
            ])->values(),
            'meta' => [
                'count' => $tasks->count(),
            ],
        ]);
    }

    private function getLoggedInCustomer(): ?User
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return User::query()
            ->where('role', 'client')
            ->where(function ($query) use ($user) {
                $query->where('id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->first();
    }

    private function getLoggedInStaff(): ?User
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }


        return User::query()
            ->where('role', 'staff')
            ->where('id', $user->id)
            ->first();
    }

    private function isPrivilegedProjectUser(): bool
    {
        $user = Auth::user();

        return (bool) ($user && $user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2']));
    }

    private function authorizeProjectWriteAction(string $permission): ?JsonResponse
    {
        $user = Auth::user();
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

        if ($this->isPrivilegedProjectUser()) {
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

    private function visibleProjectsQuery()
    {
        if ($this->isPrivilegedProjectUser()) {
            return Project::query();
        }

        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            return Project::query()->where('customer_id', $customer->id);
        }

        $staff = $this->getLoggedInStaff();
        if ($staff) {
            return Project::query()->where(function ($query) use ($staff) {
                // Handle both integer and string values stored in the JSON members array.
                $query->whereJsonContains('members', (int) $staff->id)
                    ->orWhereJsonContains('members', (string) $staff->id);
            });
        }
        return Project::query();
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
        abort_if(! $staff, 403, $message);

        $memberIds = collect($project->members ?? [])
            ->filter(fn($memberId) => $memberId !== null && $memberId !== '')
            ->map(fn($memberId) => (int) $memberId);

        abort_if(! $memberIds->contains((int) $staff->id), 403, $message);
    }

    private function projectValidationRules(bool $includeFiles = true): array
    {
        $rules = [
            'project_name' => 'required|string|max:255',
            'customer' => 'nullable|exists:users,id',
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
            'members.*' => 'exists:users,id',
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
            'lifecycle_stage' => $validated['lifecycle_stage'] ?? 'project_created',
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
            $project->start_date->format('Y-m-d') . ' 00:00:00',
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
            'statusLogs' => fn($query) => $query->orderBy('started_at'),
        ]);

        $elapsedBoundary = $this->getElapsedBoundary();
        $inProgressIntervals = $this->getProjectActiveIntervals($project, $elapsedBoundary);
        $projectElapsedMinutes = $this->calculateIntervalsElapsedMinutes($inProgressIntervals);

        $tasks = Task::where('project_id', $project->id)->latest()->get();
        $projectFiles = ProjectFile::where('project_id', $project->id)->with('uploader')->latest()->get();
        $milestones = ProjectMilestone::where('project_id', $project->id)->orderBy('sort_order')->orderBy('due_date')->orderBy('id')->get();
        $issues = ProjectIssue::where('project_id', $project->id)->with('customer')->latest()->get();
        $comments = ProjectComment::where('project_id', $project->id)->with('user')->latest()->get();
        $taskStatusCounts = $tasks->countBy(fn (Task $task) => (string) $task->status);

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
        $inProgressTasks = (int) ($taskStatusCounts['in_progress'] ?? 0);
        $notStartedTasks = (int) ($taskStatusCounts['not_started'] ?? 0);

        $completedTasks = (int) ($taskStatusCounts['completed'] ?? 0);
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
            $availableSignals = array_values(array_filter($progressSignals, fn($value) => $value !== null));
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
            'tasks' => $tasks->map(fn(Task $task) => [
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
            'files' => $projectFiles->map(fn(ProjectFile $file) => $this->formatFileResource($file))->values(),
            'milestones' => $milestones->map(fn(ProjectMilestone $milestone) => $this->formatMilestoneResource($milestone))->values(),
            'issues' => $issues->map(fn(ProjectIssue $issue) => $this->formatIssueResource($issue))->values(),
            'comments' => $comments->map(fn(ProjectComment $comment) => $this->formatCommentResource($comment))->values(),
            'status_logs' => $project->statusLogs->map(fn(ProjectStatusLog $log) => [
                'id' => $log->id,
                'status' => $log->status,
                'started_at' => optional($log->started_at)?->toISOString(),
                'ended_at' => optional($log->ended_at)?->toISOString(),
            ])->values(),
        ];
    }

    private function buildProjectDetailsDashboardPayload(Project $project): array
    {
        $project->loadMissing([
            'customer',
            'customerUser.address',
            'customerUser',
            'statusLogs' => fn ($query) => $query->orderBy('started_at'),
        ]);

        $elapsedBoundary = $this->getElapsedBoundary();
        $inProgressIntervals = $this->getProjectActiveIntervals($project, $elapsedBoundary);
        $projectElapsedMinutes = $this->calculateIntervalsElapsedMinutes($inProgressIntervals);

        $tasks = Task::query()
            ->select([
                'id',
                'project_id',
                'title',
                'assignees',
                'status',
                'priority',
                'workflow_status',
                'deadline',
                'created_at',
            ])
            ->where('project_id', $project->id)
            ->orderByDesc('created_at')
            ->get();

        $taskStatusMeta = [
            'not_started' => ['label' => 'Not Started', 'badge_class' => 'bg-secondary'],
            'in_progress' => ['label' => 'In Progress', 'badge_class' => 'bg-primary'],
            'on_hold' => ['label' => 'On Hold', 'badge_class' => 'bg-warning'],
            'completed' => ['label' => 'Completed', 'badge_class' => 'bg-success'],
            'cancelled' => ['label' => 'Cancelled', 'badge_class' => 'bg-danger'],
        ];

        $taskStatusCounts = $tasks->countBy(fn ($task) => (string) $task->status);
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

        $memberIds = collect($project->members ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->values();

        $taskAssigneeIds = $tasks->pluck('assignees')
            ->flatten()
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->values();

        $relevantStaffIds = $memberIds
            ->merge($taskAssigneeIds)
            ->unique()
            ->values()
            ->all();

        $staff = User::staffMembers()
            ->select(['id', 'first_name', 'last_name', 'profile_image', 'email'])
            ->whereIn('id', $relevantStaffIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');

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

        $projectFiles = ProjectFile::query()
            ->where('project_id', $project->id)
            ->with('uploader')
            ->orderByDesc('created_at')
            ->get();

        $milestones = ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->orderBy('sort_order')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $issues = ProjectIssue::query()
            ->where('project_id', $project->id)
            ->with('customer')
            ->orderByDesc('created_at')
            ->get();

        $comments = ProjectComment::query()
            ->where('project_id', $project->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $milestoneStats = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('status', 'completed')->count(),
            'in_progress' => $milestones->where('status', 'in_progress')->count(),
            'pending' => $milestones->where('status', 'pending')->count(),
        ];

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
        $resolvedIssues = ($issueStats['resolved'] ?? 0) + ($issueStats['closed'] ?? 0);

        $progressSignals = [
            $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : null,
            ($milestoneStats['total'] ?? 0) > 0 ? (($milestoneStats['completed'] / $milestoneStats['total']) * 100) : null,
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
                    'in_progress' => 0,
                    'on_hold' => 0,
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
            'completed_milestones' => $milestoneStats['completed'] ?? 0,
            'total_milestones' => $milestoneStats['total'] ?? 0,
            'resolved_issues' => $resolvedIssues,
            'total_issues' => $issueStats['total'] ?? 0,
        ];

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
            $overdueTrendLabels[] = $date->format('D');
            $overdueTrendCounts[] = (int) ($overdueCountsByDate[$date->format('Y-m-d')] ?? 0);
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

        $recentActivities = collect();
        $recentActivityMeta = null;
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

            $recentActivityMeta = [
                'current_page' => $recentActivities->currentPage(),
                'last_page' => $recentActivities->lastPage(),
                'total' => $recentActivities->total(),
                'per_page' => $recentActivities->perPage(),
            ];
            $recentActivities = collect($recentActivities->items())->values();
        }

        return [
            'project' => [
                'id' => $project->id,
                'project_name' => $project->project_name,
                'customer_id' => $project->customer_id,
                'status' => $project->status,
                'lifecycle_stage' => $project->lifecycle_stage,
                'start_date' => optional($project->start_date)?->toDateString(),
                'deadline' => optional($project->deadline)?->toDateString(),
                'tags' => $project->tags ?? [],
                'members' => $project->members ?? [],
                'description' => $project->description,
                'priority' => $project->priority,
                'technologies' => $project->technologies ?? [],
                'deployment_date' => optional($project->deployment_date)?->toDateString(),
                'maintenance_expiry' => optional($project->maintenance_expiry)?->toDateString(),
                'customer' => $project->customer ? [
                    'id' => $project->customer->id,
                    'name' => $this->formatUserName($project->customer),
                    'email' => $project->customer->email,
                ] : null,
                'customer_user' => $project->customerUser ? [
                    'id' => $project->customerUser->id,
                    'name' => $project->customerUser->name,
                    'email' => $project->customerUser->email,
                    'phone' => $project->customerUser->phone,
                    'address' => $project->customerUser->address ? [
                        'address_line_1' => $project->customerUser->address->address_line_1 ?? null,
                        'address_line_2' => $project->customerUser->address->address_line_2 ?? null,
                        'city' => $project->customerUser->address->city ?? null,
                        'state' => $project->customerUser->address->state ?? null,
                        'country' => $project->customerUser->address->country ?? null,
                        'pincode' => $project->customerUser->address->pincode ?? null,
                    ] : null,
                ] : null,
            ],
            'member_metrics' => $memberMetrics,
            'project_elapsed_hours' => round($projectElapsedMinutes / 60, 1),
            'tasks' => $tasks->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'workflow_status' => $task->workflow_status ?? 'backlog',
                'priority' => $task->priority,
                'deadline' => optional($task->deadline)?->toDateString(),
                'assignees' => $task->assignees ?? [],
                'created_at' => optional($task->created_at)?->toISOString(),
            ])->values(),
            'project_files' => $projectFiles->map(fn (ProjectFile $file) => $this->formatFileResource($file))->values(),
            'milestones' => $milestones->map(fn (ProjectMilestone $milestone) => $this->formatMilestoneResource($milestone))->values(),
            'milestone_stats' => $milestoneStats,
            'issues' => $issues->map(fn (ProjectIssue $issue) => $this->formatIssueResource($issue))->values(),
            'issue_stats' => $issueStats,
            'project_comments' => $comments->map(fn (ProjectComment $comment) => $this->formatCommentResource($comment))->values(),
            'usage_distribution' => $usageDistribution,
            'usage_chart_labels' => $usageChartLabels,
            'usage_chart_data' => $usageChartData,
            'weekly_activity_labels' => $weeklyActivityLabels,
            'weekly_activity_data' => $weeklyActivityData,
            'total_tasks' => $totalTasks,
            'project_progress' => $projectProgress,
            'recent_activities' => $recentActivities,
            'recent_activity_meta' => $recentActivityMeta,
            'pending_issues' => $pendingIssues->values(),
            'time_tracking_stats' => $timeTrackingStats,
            'deployment_summary' => $deploymentSummary,
            'workload_distribution' => $workloadDistribution,
            'task_status_analytics' => $taskStatusAnalytics,
            'workload_analytics' => $workloadAnalytics,
            'overdue_trend_labels' => $overdueTrendLabels,
            'overdue_trend_counts' => $overdueTrendCounts,
            'milestone_completion_analytics' => $milestoneCompletionAnalytics,
            'sprint_velocity_analytics' => $sprintVelocityAnalytics,
            'status_logs' => $project->statusLogs->map(fn (ProjectStatusLog $log) => [
                'id' => $log->id,
                'status' => $log->status,
                'started_at' => optional($log->started_at)?->toISOString(),
                'ended_at' => optional($log->ended_at)?->toISOString(),
            ])->values(),
            'charts' => $this->projectDashboardService->charts((int) $project->id),
            'kanban' => $this->projectDashboardService->kanbanBoard((int) $project->id),
            'activity_feed' => $this->projectDashboardService->activityFeed((int) $project->id, 10)->items(),
            'milestone_progress' => $this->projectDashboardService->milestoneProgress((int) $project->id),
            'project_dashboard' => [
                'summary' => [
                    'elapsed_hours' => round($projectElapsedMinutes / 60, 1),
                    'overall_progress' => $overallProgress,
                    'tasks_total' => $totalTasks,
                    'issues_total' => $issueStats['total'],
                    'milestones_total' => $milestoneStats['total'],
                ],
            ],
        ];
    }

    private function formatProjectListResource(Project $project, ?array $stats = null, ?array $staffLookup = null): array
    {
        $issueStats = $stats['issue_stats'] ?? [
            'total' => 0,
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];
        $resolvedIssues = $issueStats['resolved'] + $issueStats['closed'];
        $completedTasks = (int) ($stats['completed_tasks'] ?? 0);
        $totalTasks = (int) ($stats['total_tasks'] ?? 0);

        $milestoneStats = $stats['milestone_stats'] ?? [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'pending' => 0,
        ];

        $progressSignals = [
            $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : null,
            $milestoneStats['total'] > 0 ? (($milestoneStats['completed'] / $milestoneStats['total']) * 100) : null,
            $issueStats['total'] > 0 ? (($resolvedIssues / $issueStats['total']) * 100) : null,
        ];

        if ($project->status === 'completed') {
            $overallProgress = 100;
        } elseif ($project->status === 'cancelled') {
            $overallProgress = 0;
        } else {
            $availableSignals = array_values(array_filter($progressSignals, fn($value) => $value !== null));
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
                'name' => $this->formatUserName($project->customer),
                'email' => $project->customer->email,
            ] : null,
            'start_date' => optional($project->start_date)?->toDateString(),
            'deadline' => optional($project->deadline)?->toDateString(),
            'members' => $this->resolveProjectMembers($project, $staffLookup),
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
                    'show' => url('/api/v1/projects/' . $project->id),
                    'update' => url('/api/v1/projects/' . $project->id),
                    'delete' => url('/api/v1/projects/' . $project->id),
                    'milestones' => url('/api/v1/projects/' . $project->id . '/milestones'),
                    'issues' => url('/api/v1/projects/' . $project->id . '/issues'),
                    'comments' => url('/api/v1/projects/' . $project->id . '/comments'),
                    'files' => url('/api/v1/projects/' . $project->id . '/files'),
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
                'name' => $this->formatUserName($project->customer),
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
                'name' => $this->formatUserName($issue->customer),
                'email' => $issue->customer->email,
            ] : null,
            'issue_description' => $issue->issue_description,
            'priority' => $issue->priority,
            'status' => $issue->status,
            'created_at' => optional($issue->created_at)?->toISOString(),
            'updated_at' => optional($issue->updated_at)?->toISOString(),
        ];
    }

    private function buildProjectMemberLookup($projects): array
    {
        $memberIds = collect($projects)
            ->pluck('members')
            ->flatten()
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($memberIds->isEmpty()) {
            return [];
        }

        return User::staffMembers()
            ->whereIn('id', $memberIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id')
            ->all();
    }

    private function resolveProjectMembers(Project $project, ?array $staffLookup): array
    {
        if ($staffLookup === null) {
            return $project->membersList()->all();
        }

        return collect($project->members ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->map(fn ($id) => $staffLookup[$id] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function buildProjectListStatsMap(array $projectIds): array
    {
        if (empty($projectIds)) {
            return [];
        }

        $taskRows = Task::query()
            ->select('project_id', 'status', DB::raw('COUNT(*) as total'))
            ->whereIn('project_id', $projectIds)
            ->groupBy('project_id', 'status')
            ->get();

        $issueRows = ProjectIssue::query()
            ->select('project_id', 'status', DB::raw('COUNT(*) as total'))
            ->whereIn('project_id', $projectIds)
            ->groupBy('project_id', 'status')
            ->get();

        $milestoneRows = ProjectMilestone::query()
            ->select('project_id', 'status', DB::raw('COUNT(*) as total'))
            ->whereIn('project_id', $projectIds)
            ->groupBy('project_id', 'status')
            ->get();

        $map = [];
        foreach ($projectIds as $projectId) {
            $taskBucket = $taskRows->where('project_id', $projectId);
            $issueBucket = $issueRows->where('project_id', $projectId);
            $milestoneBucket = $milestoneRows->where('project_id', $projectId);

            $map[$projectId] = [
                'total_tasks' => (int) $taskBucket->sum('total'),
                'completed_tasks' => (int) optional($taskBucket->firstWhere('status', 'completed'))->total,
                'issue_stats' => [
                    'total' => (int) $issueBucket->sum('total'),
                    'open' => (int) optional($issueBucket->firstWhere('status', 'open'))->total,
                    'in_progress' => (int) optional($issueBucket->firstWhere('status', 'in_progress'))->total,
                    'resolved' => (int) optional($issueBucket->firstWhere('status', 'resolved'))->total,
                    'closed' => (int) optional($issueBucket->firstWhere('status', 'closed'))->total,
                ],
                'milestone_stats' => [
                    'total' => (int) $milestoneBucket->sum('total'),
                    'completed' => (int) optional($milestoneBucket->firstWhere('status', 'completed'))->total,
                    'in_progress' => (int) optional($milestoneBucket->firstWhere('status', 'in_progress'))->total,
                    'pending' => (int) optional($milestoneBucket->firstWhere('status', 'pending'))->total,
                ],
            ];
        }

        return $map;
    }

    private function ensureValidChangeRequestTransition(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        $transitions = [
            'requested' => ['analysis', 'rejected'],
            'analysis' => ['approved', 'rejected'],
            'approved' => ['implemented', 'rejected'],
            'rejected' => [],
            'implemented' => [],
        ];

        if (! in_array($to, $transitions[$from] ?? [], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid change request transition: %s -> %s', $from, $to));
        }
    }

    private function formatChangeRequestResource(ProjectChangeRequest $changeRequest): array
    {
        return [
            'id' => $changeRequest->id,
            'project_id' => $changeRequest->project_id,
            'title' => $changeRequest->title,
            'description' => $changeRequest->description,
            'impact_level' => $changeRequest->impact_level,
            'status' => $changeRequest->status,
            'requested_at' => optional($changeRequest->requested_at)?->toISOString(),
            'approved_at' => optional($changeRequest->approved_at)?->toISOString(),
            'requested_by' => $changeRequest->requester ? [
                'id' => $changeRequest->requester->id,
                'name' => trim(($changeRequest->requester->first_name ?? '').' '.($changeRequest->requester->last_name ?? '')),
                'email' => $changeRequest->requester->email,
            ] : null,
            'approved_by' => $changeRequest->approver ? [
                'id' => $changeRequest->approver->id,
                'name' => trim(($changeRequest->approver->first_name ?? '').' '.($changeRequest->approver->last_name ?? '')),
                'email' => $changeRequest->approver->email,
            ] : null,
            'created_at' => optional($changeRequest->created_at)?->toISOString(),
            'updated_at' => optional($changeRequest->updated_at)?->toISOString(),
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

    private function formatUserName(User $user): string
    {
        $name = trim((string) ($user->client_name ?? ''));

        if ($name !== '') {
            return $name;
        }

        $contactPerson = trim((string) ($user->contact_person ?? ''));

        if ($contactPerson !== '') {
            return $contactPerson;
        }

        return 'Client #' . $user->id;
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
                'delete' => url('/api/v1/projects/' . $file->project_id . '/files/' . $file->id),
            ],
        ];
    }

    private function storeProjectFile($projectId, $file): ProjectFile
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $fileSize = $file->getSize();
        $fileType = $file->getMimeType();

        $directory = public_path('uploads/project_files/' . $projectId);
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
            'file_path' => 'uploads/project_files/' . $projectId . '/' . $fileName,
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
                Log::error('Failed to send project creation email to admin: ' . $e->getMessage(), [
                    'project_id' => $project->id,
                    'admin_email' => $adminEmail,
                ]);
            }
        }

        $memberEmails = User::query()
            ->whereIn('id', collect($project->members ?? [])->filter()->map(fn($id) => (int) $id)->all())
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn($email) => trim((string) $email))
            ->filter()
            ->unique()
            ->values();

        foreach ($memberEmails as $memberEmail) {
            try {
                Mail::to($memberEmail)->send(new ProjectCreatedMail($project, 'member'));
            } catch (\Throwable $e) {
                Log::error('Failed to send project creation email to member: ' . $e->getMessage(), [
                    'project_id' => $project->id,
                    'member_email' => $memberEmail,
                ]);
            }
        }
    }

    private function calculateProgress(Project $project, ?array $stats = null)
    {
        if ($stats === null) {
            $statsMap = $this->buildProjectListStatsMap([$project->id]);
            $stats = $statsMap[$project->id] ?? null;
        }

        $issueStats = $stats['issue_stats'] ?? [
            'total' => 0,
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];
        $resolvedIssues = $issueStats['resolved'] + $issueStats['closed'];
        $completedTasks = (int) ($stats['completed_tasks'] ?? 0);
        $totalTasks = (int) ($stats['total_tasks'] ?? 0);

        $milestoneStats = $stats['milestone_stats'] ?? [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'pending' => 0,
        ];

        $progressSignals = [
            $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : null,
            $milestoneStats['total'] > 0 ? (($milestoneStats['completed'] / $milestoneStats['total']) * 100) : null,
            $issueStats['total'] > 0 ? (($resolvedIssues / $issueStats['total']) * 100) : null,
        ];

        if ($project->status === 'completed') {
            $overallProgress = 100;
        } elseif ($project->status === 'cancelled') {
            $overallProgress = 0;
        } else {
            $availableSignals = array_values(array_filter($progressSignals, fn($value) => $value !== null));
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
            'overall' => $overallProgress,
            'resolved_issues' => $resolvedIssues,
        ];
    }
}
