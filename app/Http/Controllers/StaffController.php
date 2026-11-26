<?php

namespace App\Http\Controllers;

use App\Helpers\FileUpload;
use App\Mail\StaffInviteMail;
use App\Models\Department;
use App\Models\Project;
use App\Models\SalaryProfile;
use App\Models\SalaryRecord;
use App\Models\Setting;
use App\Models\StaffActivityLog;
use App\Models\Team;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\StaffLeadAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravolt\Avatar\Avatar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffController extends Controller
{
    public function __construct(private readonly StaffLeadAnalyticsService $staffLeadAnalyticsService)
    {
    }

    private const DEFAULT_BUSINESS_TZ = 'UTC';

    private const DEFAULT_WORK_START_TIME = '09:00';

    private const DEFAULT_LUNCH_START_TIME = '13:00';

    private const DEFAULT_LUNCH_END_TIME = '14:00';

    private const DEFAULT_WORK_END_TIME = '18:00';

    /**
     * Display a listing of staff members.
     */
    public function index()
    {
        $staff = User::query()
            ->with('address')
            ->withMax('activityLogs', 'logged_in_at')
            ->whereNotNull('role')
            ->whereIn('role', ['staff', 'super_admin2', 'admin'])
            ->orderBy('first_name')
            ->get()
            ->map(fn(User $user) => $this->hydrateStaffUser($user));

        return view('staff.index', compact('staff'));
    }

    /**
     * Display staff login sessions and device details.
     */
    public function activityLog(Request $request)
    {
        $request->validate([
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = StaffActivityLog::query()
            ->when($request->filled('staff_id'), fn ($query) => $query->where('user_id', $request->input('staff_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('logged_in_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('logged_in_at', '<=', $request->input('to')))
            ->with('user')
            ->latest('logged_in_at')
            ->paginate(20)
            ->withQueryString();

        $staffMembers = User::query()
            ->where('role', 'staff')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $canManageStaffAccounts = $this->canManageStaffAccounts($request->user());

        return view('staff.activity-log', compact('logs', 'staffMembers', 'canManageStaffAccounts'));
    }

    /**
     * End every active browser and API session for a staff member.
     */
    public function forceLogout(Request $request, User $staff)
    {
        $this->authorizeStaffAccountAction($request, $staff);
        $this->invalidateStaffSessions($staff);

        return redirect()->route('staff.activity-log', $request->query())
            ->with('success', $staff->name . ' has been logged out from all devices.');
    }

    /**
     * Block a staff account and immediately invalidate every active session.
     */
    public function block(Request $request, User $staff)
    {
        $this->authorizeStaffAccountAction($request, $staff);

        $staff->forceFill(['status' => 'inactive'])->save();
        $this->invalidateStaffSessions($staff);

        return redirect()->route('staff.activity-log', $request->query())
            ->with('success', $staff->name . ' has been blocked and logged out.');
    }

    /**
     * Restore login access for a previously blocked staff account.
     */
    public function unblock(Request $request, User $staff)
    {
        $this->authorizeStaffAccountAction($request, $staff);
        $staff->forceFill(['status' => 'active'])->save();

        return redirect()->route('staff.activity-log', $request->query())
            ->with('success', $staff->name . ' can log in again.');
    }

    private function authorizeStaffAccountAction(Request $request, User $staff): void
    {
        $actor = $request->user();
        $staffRole = strtolower((string) $staff->getRawOriginal('role'));

        abort_unless($this->canManageStaffAccounts($actor), 403);
        abort_unless($staffRole === 'staff', 404);
        abort_if((int) $actor->id === (int) $staff->id, 422, 'You cannot block or log out your own account.');
    }

    private function canManageStaffAccounts(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $roles = collect([
            $user->getRawOriginal('role'),
            $user->role,
        ]);

        if (method_exists($user, 'getRoleNames')) {
            $roles = $roles->merge($user->getRoleNames());
        }

        return $roles
            ->filter()
            ->map(fn ($role) => str_replace([' ', '-'], '_', strtolower(trim((string) $role))))
            ->contains(fn ($role) => in_array($role, ['admin', 'super_admin', 'super_admin2', 'superadmin'], true));
    }

    private function invalidateStaffSessions(User $staff): void
    {
        $now = now();

        DB::transaction(function () use ($staff, $now) {
            $staff->forceFill([
                'session_invalidated_at' => $now,
                'remember_token' => null,
            ])->save();

            $staff->tokens()->delete();

            StaffActivityLog::query()
                ->where('user_id', $staff->id)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => $now]);
        });
    }

    /**
     * Display the staff creation form.
     */
    public function create()
    {
        $roles = Role::all();
        $teams = Team::getTeamOptions();
        $departments = Department::getDepartmentOptions();

        return view('staff.create', compact('roles', 'teams', 'departments'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->withoutTrashed(),
            ],
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'departments.*' => 'string|max:255',
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $profileImagePath = basename(FileUpload::uploadOrGenerateAvatar(
                $request->file('profileImage'),
                trim($request->firstName . ' ' . $request->lastName),
                'uploads/staff/'
            ));

            $status = $request->status ?? 'active';
            $team = $request->filled('team') ? $request->team : null;
            $departments = collect($request->input('departments', []))
                ->filter(fn($department) => is_string($department) && trim($department) !== '')
                ->map(fn($department) => trim($department))
                ->unique()
                ->values()
                ->all();

            $existingUser = User::withTrashed()->where('email', $request->email)->first();

            if ($existingUser) {
                $existingUser->restore();

                $existingUser->update([
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'profile_image' => $profileImagePath,
                    'status' => $status,
                    'role' => 'staff',
                ]);

                $user = $existingUser;
            } else {
                $user = User::create([
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'profile_image' => $profileImagePath,
                    'status' => $status,
                    'role' => 'staff',
                ]);
            }

            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            $this->syncUserDepartmentAssignments($user, $departments);
            $this->syncUserTeamAssignment($user, $team);

            $this->refreshPermissionCache();

            $sendWelcomeEmail = $request->boolean('sendWelcomeEmail');
            if ($sendWelcomeEmail) {
                $staffName = $request->firstName . ' ' . $request->lastName;
                try {
                    Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
                } catch (\Exception $mailException) {
                    Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
                }
            }

            DB::commit();
            $successMessage = $sendWelcomeEmail
                ? 'Staff added successfully. Invitation email sent to ' . $request->email
                : 'Staff added successfully. Welcome email was not sent.';

            return redirect()->route('staff')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create staff: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        $staff = $this->hydrateStaffUser(
            User::withTrashed()
                ->with(['roles', 'teams', 'departments', 'address', 'salaryProfile'])
                ->findOrFail($id)
        );

        $latestSalaryMonth = SalaryRecord::query()
            ->where('staff_user_id', $staff->id)
            ->orderByDesc('salary_month')
            ->value('salary_month');
        $defaultSlipCarbon = $latestSalaryMonth ? Carbon::parse($latestSalaryMonth) : now();

        $availableSalaryYears = SalaryRecord::query()
            ->where('staff_user_id', $staff->id)
            ->pluck('salary_month')
            ->filter()
            ->map(fn ($date) => (int) Carbon::parse($date)->year);

        $currentYear = (int) now()->year;
        $salarySlipYears = collect(range(max(2020, $currentYear - 5), $currentYear + 1))
            ->merge($availableSalaryYears)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return view('staff.view', array_merge(
            [
                'staff' => $staff,
                'salarySlipDefaultMonth' => (int) $defaultSlipCarbon->month,
                'salarySlipDefaultYear' => (int) $defaultSlipCarbon->year,
                'salarySlipYears' => $salarySlipYears,
            ],
            $this->buildStaffFormOptions($staff)
        ));
    }

    /**
     * Show the dedicated edit page for the specified staff member.
     */
    public function edit($id)
    {
        $staff = $this->hydrateStaffUser(
            User::withTrashed()
                ->with(['roles', 'teams', 'departments', 'address', 'salaryProfile'])
                ->findOrFail($id)
        );

        if ($staff->trashed()) {
            return redirect()->route('view-staff', $staff->id)
                ->with('error', 'Restore this staff member before editing the record.');
        }

        return view('staff.edit', array_merge(
            ['staff' => $staff],
            $this->buildStaffFormOptions($staff)
        ));
    }

    /**
     * Update staff status along with optional leaving date and reason.
     */
    public function updateStatus(Request $request, $id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        if ($staff->trashed()) {
            return redirect()->route('view-staff', $staff->id)
                ->with('error', 'Restore this staff member before changing status.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'date_of_leaving' => ['nullable', 'date'],
            'status_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $updatePayload = [
            'status' => $validated['status'],
            'date_of_leaving' => $request->filled('date_of_leaving') ? $validated['date_of_leaving'] : null,
            'status_reason' => $request->filled('status_reason') ? trim((string) $validated['status_reason']) : null,
        ];

        $staff->forceFill($updatePayload)->save();

        if ($validated['status'] === 'inactive') {
            $this->invalidateStaffSessions($staff);
        }

        return redirect()->route('view-staff', $staff->id)
            ->with('success', 'Staff status updated successfully.');
    }

    /**
     * Download the staff member's salary slip for the selected month and year.
     */
    public function downloadSalarySlip(Request $request, $id)
    {
        $staff = User::withTrashed()
            ->with(['departments', 'salaryProfile'])
            ->findOrFail($id);

        $authUser = $request->user();
        $hasSalaryAccess = $authUser
            && ($authUser->can('view_salary_management') || $authUser->can('upload_salary_management'));
        if (! $hasSalaryAccess) {
            $this->authorizeStaffAnalyticsAccess($staff);
        }

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];
        $targetCarbon = Carbon::create($year, $month, 1);

        $record = SalaryRecord::query()
            ->where('staff_user_id', $staff->id)
            ->whereYear('salary_month', $year)
            ->whereMonth('salary_month', $month)
            ->latest('id')
            ->first();

        if (! $record) {
            return redirect()
                ->back(fallback: route('view-staff', $staff->id))
                ->with('error', 'No salary slip found for ' . $targetCarbon->format('F Y') . '.');
        }

        $html = view('salary-management.payslip', [
            'record' => $record->load('staffUser'),
        ])->render();

        // Embed company logo and stamp as data URIs so the downloaded HTML works offline.
        $inlineAssets = [
            route('salary-management.logo') => base_path('salary/logo-black.png'),
            route('salary-management.stamp') => base_path('salary/stamp.png'),
        ];
        foreach ($inlineAssets as $assetUrl => $diskPath) {
            if (is_file($diskPath) && is_readable($diskPath)) {
                $encoded = 'data:image/png;base64,' . base64_encode((string) file_get_contents($diskPath));
                $html = str_replace($assetUrl, $encoded, $html);
            }
        }

        $employeeCode = $staff->employee_code
            ?: (optional($staff->salaryProfile)->employee_code ?: 'TF' . str_pad((string) $staff->id, 3, '0', STR_PAD_LEFT));
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $employeeCode) ?: ('staff-' . $staff->id);
        $filename = sprintf('salary-slip-%s-%s-%d.html', $safeCode, strtolower($targetCarbon->format('F')), $year);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Display the dedicated Leads & Projects reports page for a staff member.
     */
    public function reports($id)
    {
        $staff = $this->hydrateStaffUser(
            User::withTrashed()
                ->with(['roles', 'teams', 'departments', 'salaryProfile'])
                ->findOrFail($id)
        );

        $this->authorizeStaffAnalyticsAccess($staff);

        $projects = $staff->projects()
            ->with(['statusHistories' => fn ($query) => $query->orderBy('changed_at')])
            ->latest()
            ->get();
        $tasks = $staff->tasks()->with('project')->latest()->get();
        $staffProjectAnalytics = $this->buildStaffProjectAnalytics($staff, $projects, $tasks);
        $projectTimelineRows = $staffProjectAnalytics['charts']['monthly_project_timeline'] ?? [];
        $analyticsFilters = [
            'period' => request()->query('period', '30d'),
            'from' => request()->query('from'),
            'to' => request()->query('to'),
        ];
        $staffLeadAnalytics = $this->staffLeadAnalyticsService->buildDashboard((int) $staff->id, $analyticsFilters);

        return view('staff.reports', compact(
            'staff',
            'projects',
            'tasks',
            'staffProjectAnalytics',
            'projectTimelineRows',
            'staffLeadAnalytics',
            'analyticsFilters'
        ));
    }

    private function buildStaffFormOptions(User $staff): array
    {
        $roles = Role::all();
        $teams = Team::getTeamOptions();
        $departments = Department::getDepartmentOptions();
        $branchOptions = User::query()
            ->whereNotNull('branch')
            ->where('branch', '!=', '')
            ->distinct()
            ->pluck('branch')
            ->prepend('Technofra Main Branch')
            ->map(fn ($branch) => trim((string) $branch))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $employeeTypeOptions = ['Full Time', 'Part Time', 'Contract', 'Intern', 'Probation', 'Freelance'];
        $workModeOptions = ['On-Site', 'Hybrid', 'Remote'];
        $reportingManagers = User::query()
            ->whereIn('role', ['admin', 'super_admin2', 'staff'])
            ->where('id', '!=', $staff->id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (User $u) => trim($u->first_name . ' ' . $u->last_name))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return compact(
            'roles',
            'teams',
            'departments',
            'branchOptions',
            'employeeTypeOptions',
            'workModeOptions',
            'reportingManagers'
        );
    }

    public function analytics(Request $request, $id): JsonResponse
    {
        $staff = User::withTrashed()->findOrFail($id);
        $this->authorizeStaffAnalyticsAccess($staff);

        $filters = $request->only(['period', 'from', 'to']);
        $leadAnalytics = $this->staffLeadAnalyticsService->buildDashboard((int) $staff->id, $filters);
        $projects = $staff->projects()->latest()->get();
        $tasks = $staff->tasks()->with('project')->latest()->get();
        $projectAnalytics = $this->buildStaffProjectAnalytics($staff, $projects, $tasks);

        return response()->json(array_merge($leadAnalytics, [
            'project_analytics' => $projectAnalytics,
            'project_timeline_rows' => $projectAnalytics['charts']['monthly_project_timeline'] ?? [],
            'task_status_overview' => $projectAnalytics['charts']['task_status_overview'] ?? [],
        ]));
    }

    public function leadChart(Request $request, $id): JsonResponse
    {
        $staff = User::withTrashed()->findOrFail($id);
        $this->authorizeStaffAnalyticsAccess($staff);

        return response()->json(
            $this->staffLeadAnalyticsService->leadChart((int) $staff->id, $request->only(['period', 'from', 'to']))
        );
    }

    public function followupChart(Request $request, $id): JsonResponse
    {
        $staff = User::withTrashed()->findOrFail($id);
        $this->authorizeStaffAnalyticsAccess($staff);

        return response()->json(
            $this->staffLeadAnalyticsService->followupChart((int) $staff->id, $request->only(['period', 'from', 'to']))
        );
    }

    private function buildStaffProjectAnalytics(User $staff, $projects, $tasks): array
    {
        $projects = collect($projects);
        $tasks = collect($tasks);
        $now = now();

        $projectStatusLabels = ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled'];
        $taskStatusLabels = ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled'];

        $projectStatusCounts = $projects->countBy(fn ($project) => (string) ($project->status ?? 'not_started'));
        $taskStatusCounts = $tasks->countBy(fn ($task) => (string) ($task->status ?? 'not_started'));

        $projectsStartedByMonth = $projects
            ->filter(fn ($project) => ! empty($project->start_date))
            ->groupBy(fn ($project) => $project->start_date->format('Y-m'))
            ->map->count();

        $projectDeadlinesByMonth = $projects
            ->filter(fn ($project) => ! empty($project->deadline))
            ->groupBy(fn ($project) => $project->deadline->format('Y-m'))
            ->map->count();

        $projectStatusDistribution = [
            'labels' => collect($projectStatusLabels)->map(fn ($label) => ucfirst(str_replace('_', ' ', $label)))->all(),
            'series' => collect($projectStatusLabels)->map(fn ($label) => (int) ($projectStatusCounts[$label] ?? 0))->values()->all(),
        ];

        $taskStatusDistribution = [
            'labels' => collect($taskStatusLabels)->map(fn ($label) => ucfirst(str_replace('_', ' ', $label)))->all(),
            'series' => collect($taskStatusLabels)->map(fn ($label) => (int) ($taskStatusCounts[$label] ?? 0))->values()->all(),
        ];

        $taskStatusOverview = $this->buildTaskStatusOverview($tasks);
        $projectTimeline = $this->buildProjectTimelineRows($projects);

        return [
            'kpis' => [
                'total_projects' => $projects->count(),
                'active_projects' => $projects->where('status', 'in_progress')->count(),
                'completed_projects' => $projects->where('status', 'completed')->count(),
                'pending_projects' => $projects->where('status', 'pending')->count(),
                'overdue_projects' => $projects->filter(function ($project) {
                    return $project->deadline
                        && method_exists($project->deadline, 'isPast')
                        && $project->deadline->isPast()
                        && $project->status !== 'completed';
                })->count(),
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'completed')->count(),
                'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
                'pending_tasks' => $tasks->where('status', 'pending')->count(),
                'completion_rate' => $projects->count() > 0
                    ? round(($projects->where('status', 'completed')->count() / $projects->count()) * 100, 2)
                    : 0,
                'avg_progress' => round((float) $projects->avg(fn ($project) => (float) ($project->progress_percentage ?? 0)), 2),
            ],
            'charts' => [
                'project_status_distribution' => $projectStatusDistribution,
                'task_status_distribution' => $taskStatusDistribution,
                'task_status_overview' => $taskStatusOverview,
                'monthly_project_activity' => [
                    'labels' => collect(range(11, 0))->map(fn ($i) => $now->copy()->startOfMonth()->subMonths($i)->format('M Y'))->values()->all(),
                    'projects_started' => collect(range(11, 0))->map(fn ($i) => (int) ($projectsStartedByMonth[$now->copy()->startOfMonth()->subMonths($i)->format('Y-m')] ?? 0))->values()->all(),
                    'project_deadlines' => collect(range(11, 0))->map(fn ($i) => (int) ($projectDeadlinesByMonth[$now->copy()->startOfMonth()->subMonths($i)->format('Y-m')] ?? 0))->values()->all(),
                ],
                'monthly_project_timeline' => $projectTimeline,
            ],
        ];
    }

    private function buildProjectTimelineRows($projects): array
    {
        $today = now()->startOfDay();

        return collect($projects)
            ->filter(fn ($project) => $project->start_date && $project->deadline)
            ->sortByDesc(fn ($project) => $project->start_date?->timestamp ?? 0)
            ->values()
            ->map(function ($project) use ($today) {
                $startDate = $project->start_date->copy()->startOfDay();
                $endDate = $project->deadline->copy()->startOfDay();
                $latestHistory = collect($project->statusHistories ?? [])
                    ->sortByDesc(fn ($history) => optional($history->changed_at)?->timestamp ?? 0)
                    ->first();

                $currentStatus = strtolower((string) ($project->status ?? 'not_started'));
                $statusChangedAt = $latestHistory?->changed_at?->copy()->startOfDay()
                    ?? $project->updated_at?->copy()->startOfDay();
                $finishedAt = $latestHistory?->changed_at?->copy()->endOfDay()
                    ?? $project->updated_at?->copy()->endOfDay();
                $isFinished = in_array($currentStatus, ['completed', 'finished'], true);
                $isOnHold = $currentStatus === 'on_hold';
                $isUpcoming = $today->lt($startDate);
                $hasStarted = ! $isUpcoming;
                $isPastEndDate = $today->gt($endDate);
                $finishedBeforeDeadline = $isFinished && $finishedAt && $finishedAt->lte($endDate->copy()->endOfDay());

                if ($isUpcoming) {
                    $statusLabel = 'Not Started';
                    $differenceLabel = 'Starts on ' . $startDate->format('M d, Y');
                    $barColor = '#6c757d';
                    $statusTextClass = 'text-secondary';
                } elseif ($isFinished) {
                    if ($isPastEndDate && ! $finishedBeforeDeadline) {
                        $statusLabel = 'Overdue';
                        $differenceDays = $endDate->diffInDays($today);
                        $differenceLabel = 'Finished after deadline, overdue by ' . $differenceDays . ' day' . ($differenceDays === 1 ? '' : 's');
                        $barColor = '#dc3545';
                        $statusTextClass = 'text-danger';
                    } else {
                        $statusLabel = 'Finished';
                        $differenceLabel = 'Finished on ' . ($finishedAt?->format('M d, Y') ?? $today->format('M d, Y'));
                        $barColor = '#198754';
                        $statusTextClass = 'text-success';
                    }
                } elseif ($isPastEndDate) {
                    $statusLabel = 'Overdue';
                    $differenceDays = $endDate->diffInDays($today);
                    $differenceLabel = 'Overdue by ' . $differenceDays . ' day' . ($differenceDays === 1 ? '' : 's');
                    $barColor = '#dc3545';
                    $statusTextClass = 'text-danger';
                } elseif ($hasStarted && ! $isOnHold) {
                    $statusLabel = 'In Progress';
                    $differenceDays = $today->diffInDays($endDate);
                    $differenceLabel = 'Due in ' . $differenceDays . ' day' . ($differenceDays === 1 ? '' : 's');
                    $barColor = '#0d6efd';
                    $statusTextClass = 'text-primary';
                } else {
                    $statusLabel = 'Pending';
                    $differenceLabel = $isOnHold
                        ? 'On hold since ' . ($statusChangedAt?->format('M d, Y') ?? $today->format('M d, Y'))
                        : 'Pending';
                    $barColor = '#6c757d';
                    $statusTextClass = 'text-secondary';
                }
                return [
                    'project_name' => $project->project_name,
                    'start_date' => $startDate->toDateString(),
                    'start_label' => $startDate->format('M d, Y'),
                    'end_date' => $endDate->toDateString(),
                    'end_label' => $endDate->format('M d, Y'),
                    'status_label' => $statusLabel,
                    'status_changed_label' => $statusChangedAt?->format('M d, Y'),
                    'status_text_class' => $statusTextClass,
                    'is_overdue' => $statusLabel === 'Overdue',
                    'difference_days' => $differenceDays ?? null,
                    'difference_label' => $differenceLabel,
                    'bar_color' => $barColor,
                ];
            })
            ->all();
    }

    private function buildTaskStatusOverview($tasks): array
    {
        $tasks = collect($tasks);
        $today = now()->startOfDay();
        $statusLabels = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'overdue' => 'Overdue',
            'completed' => 'Completed',
        ];
        $statusColors = [
            '#6c757d',
            '#0d6efd',
            '#dc3545',
            '#198754',
        ];

        $counts = array_fill_keys(array_keys($statusLabels), 0);

        foreach ($tasks as $task) {
            $status = $this->resolveTaskStatusOverview($task, $today);
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        $data = [];
        foreach ($statusLabels as $key => $label) {
            $data[] = [
                'status' => $label,
                'count' => (int) ($counts[$key] ?? 0),
            ];
        }

        return [
            'data' => $data,
            'labels' => collect($data)->pluck('status')->values()->all(),
            'series' => collect($data)->pluck('count')->values()->all(),
            'colors' => $statusColors,
        ];
    }

    private function resolveTaskStatusOverview($task, Carbon $today): string
    {
        $progress = $this->resolveTaskProgressValue($task);
        $status = strtolower((string) data_get($task, 'status', ''));
        $startDate = $this->normalizeTaskDate(data_get($task, 'start_date'));
        $endDate = $this->normalizeTaskDate(data_get($task, 'deadline'));

        if ($progress >= 100 || $status === 'completed') {
            return 'completed';
        }

        if ($startDate && $today->lessThanOrEqualTo($startDate)) {
            return 'pending';
        }

        if ($startDate && $endDate && $today->greaterThanOrEqualTo($startDate) && $today->lessThanOrEqualTo($endDate) && $progress > 0 && $progress < 100) {
            return 'in_progress';
        }

        if ($endDate && $today->greaterThan($endDate) && $progress < 100) {
            return 'overdue';
        }

        if ($progress > 0 && $progress < 100) {
            return 'in_progress';
        }

        if (in_array($status, ['pending', 'not_started'], true)) {
            return 'pending';
        }

        if ($status === 'in_progress') {
            return 'in_progress';
        }

        if ($endDate && $today->greaterThan($endDate)) {
            return 'overdue';
        }

        return 'pending';
    }

    private function resolveTaskProgressValue($task): int
    {
        $progress = data_get($task, 'progress');

        if ($progress === null) {
            $progress = data_get($task, 'progress_percentage');
        }

        if ($progress === null && data_get($task, 'completed_at')) {
            return 100;
        }

        if ($progress === null) {
            $status = strtolower((string) data_get($task, 'status', ''));

            if ($status === 'completed') {
                return 100;
            }

            if ($status === 'in_progress') {
                return 50;
            }

            return 0;
        }

        return max(0, min(100, (int) round((float) $progress)));
    }

    private function normalizeTaskDate($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->startOfDay();
        }

        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        if ($staff->trashed()) {
            return redirect()->back()->with('error', 'Restore this staff member before updating the record.');
        }

        $validator = $this->validateStaffData($request, $id);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $staff->update($this->buildStaffUpdateData($request, $staff));
            $this->syncUserForStaffUpdate($staff, $request);
            $this->refreshPermissionCache();

            DB::commit();

            return redirect()->route('view-staff', $staff->id)->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to update staff: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified staff member.
     */
    public function destroy($id)
    {
        $staff = User::findOrFail($id);

        if ($staff->trashed()) {
            return redirect()->back()->with('error', 'Staff member is already deleted.');
        }

        DB::beginTransaction();
        try {
            $this->performStaffDelete($staff, false);
            DB::commit();

            return redirect()->route('staff')->with('success', 'Staff deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to delete staff: ' . $e->getMessage());
        }
    }

    /**
     * Delete selected staff members.
     */
    public function deleteSelected(Request $request)
    {
        $ids = collect(is_array($request->ids) ? $request->ids : explode(',', (string) $request->ids))
            ->map(fn($id) => (int) trim((string) $id))
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No staff selected for deletion.'], 400);
            }
            return redirect()->route('staff')->with('error', 'No staff selected for deletion.');
        }

        DB::beginTransaction();
        try {
            $staffMembers = User::whereIn('id', $ids)->get();
            foreach ($staffMembers as $staff) {
                if (! $staff->trashed()) {
                    $this->performStaffDelete($staff, false);
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Selected staff members deleted successfully.']);
            }
            return redirect()->route('staff')->with('success', 'Selected staff members deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete selected staff: ' . $e->getMessage()], 500);
            }
            return redirect()->route('staff')->with('error', 'Failed to delete selected staff: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted staff member.
     */
    public function restore($id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        if (! $staff->trashed()) {
            return redirect()->back()->with('error', 'Staff member is already active.');
        }

        DB::beginTransaction();
        try {
            $staff->restore();
            $this->refreshPermissionCache();

            DB::commit();

            return redirect()->route('view-staff', $staff->id)->with('success', 'Staff restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to restore staff: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a staff member.
     */
    public function forceDelete($id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            $this->performStaffDelete($staff, true);
            DB::commit();

            return redirect()->route('staff')->with('success', 'Staff permanently deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to permanently delete staff: ' . $e->getMessage());
        }
    }

    private function buildStaffLoggedTimeStats(User $staff): array
    {
        $now = now($this->businessTimezone());
        $thisWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $lastMonthStart->copy()->endOfMonth();

        $stats = [
            'total_minutes' => 0,
            'last_month_minutes' => 0,
            'this_month_minutes' => 0,
            'this_week_minutes' => 0,
        ];

        $projects = $staff->projects()
            ->with([
                'statusLogs' => fn($query) => $query->orderBy('started_at'),
                'tasks:id,project_id,assignees,followers',
            ])
            ->get();

        foreach ($projects as $project) {
            $ratio = $this->getStaffRatioForProject($project, (int) $staff->id);
            if ($ratio <= 0) {
                continue;
            }

            $intervals = $this->getProjectActiveIntervals($project);
            if (empty($intervals)) {
                continue;
            }

            $projectTotal = $this->calculateIntervalsWorkingMinutes($intervals);
            $projectLastMonth = $this->calculateIntervalsWorkingMinutesWithinRange($intervals, $lastMonthStart, $lastMonthEnd);
            $projectThisMonth = $this->calculateIntervalsWorkingMinutesWithinRange($intervals, $thisMonthStart, $now);
            $projectThisWeek = $this->calculateIntervalsWorkingMinutesWithinRange($intervals, $thisWeekStart, $now);

            $stats['total_minutes'] += (int) round($projectTotal * $ratio);
            $stats['last_month_minutes'] += (int) round($projectLastMonth * $ratio);
            $stats['this_month_minutes'] += (int) round($projectThisMonth * $ratio);
            $stats['this_week_minutes'] += (int) round($projectThisWeek * $ratio);
        }

        return [
            ...$stats,
            'total_formatted' => $this->formatMinutes($stats['total_minutes']),
            'last_month_formatted' => $this->formatMinutes($stats['last_month_minutes']),
            'this_month_formatted' => $this->formatMinutes($stats['this_month_minutes']),
            'this_week_formatted' => $this->formatMinutes($stats['this_week_minutes']),
        ];
    }

    private function authorizeStaffAnalyticsAccess(User $staff): void
    {
        $authUser = auth()->user();
        abort_unless($authUser, 403);

        $rawRole = strtolower(trim((string) ($authUser->getRawOriginal('role') ?? '')));
        $accessorRole = strtolower(trim((string) ($authUser->role ?? '')));
        $normalizedRawRole = str_replace([' ', '-'], '_', $rawRole);
        $normalizedAccessorRole = str_replace([' ', '-'], '_', $accessorRole);

        $rolePrivileged = in_array($normalizedRawRole, ['admin', 'manager', 'super_admin', 'super_admin2'], true)
            || in_array($normalizedAccessorRole, ['admin', 'manager', 'super_admin', 'super_admin2'], true);

        $spatiePrivileged = method_exists($authUser, 'hasAnyRole')
            && $authUser->hasAnyRole(['admin', 'manager', 'super_admin', 'super-admin', 'super_admin2']);

        $permissionPrivileged = method_exists($authUser, 'can')
            && ($authUser->can('view_staff') || $authUser->can('view_leads'));

        $isPrivileged = $rolePrivileged || $spatiePrivileged || $permissionPrivileged;
        $isSelf = (int) $authUser->id === (int) $staff->id;

        abort_unless($isPrivileged || $isSelf, 403);
    }

    private function getStaffRatioForProject(Project $project, int $staffId): float
    {
        $memberIds = collect($project->members ?? [])
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        if (! $memberIds->contains($staffId)) {
            return 0.0;
        }

        // Baseline share so every active project member gets minimum time ownership.
        $memberWeights = [];
        foreach ($memberIds as $memberId) {
            $memberWeights[$memberId] = 1.0;
        }

        foreach ($project->tasks as $task) {
            $participants = collect(array_merge($task->assignees ?? [], $task->followers ?? []))
                ->map(fn($id) => (int) $id)
                ->filter(fn($participantId) => isset($memberWeights[$participantId]))
                ->unique()
                ->values();

            // If no mapped participants are set on a task, distribute that task equally.
            if ($participants->isEmpty()) {
                $participants = $memberIds;
            }

            $share = 1 / $participants->count();
            foreach ($participants as $participantId) {
                $memberWeights[$participantId] += $share;
            }
        }

        $totalWeight = array_sum($memberWeights);

        if ($totalWeight <= 0) {
            return 0.0;
        }

        return ($memberWeights[$staffId] ?? 0.0) / $totalWeight;
    }

    private function getProjectActiveIntervals(Project $project): array
    {
        $elapsedBoundary = $this->getElapsedBoundary($project);
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

    private function calculateIntervalsWorkingMinutes(array $intervals): int
    {
        $minutes = 0;
        foreach ($intervals as [$start, $end]) {
            $minutes += $this->calculateWorkingMinutes($start, $end);
        }

        return $minutes;
    }

    private function calculateIntervalsWorkingMinutesWithinRange(array $intervals, Carbon $rangeStart, Carbon $rangeEnd): int
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

            $minutes += $this->calculateWorkingMinutes($overlapStart, $overlapEnd);
        }

        return $minutes;
    }

    private function getElapsedBoundary(Project $project): Carbon
    {
        $now = now($this->businessTimezone());
        if (! $project->deadline) {
            return $now;
        }

        $workSchedule = $this->workSchedule();
        $deadlineAtEndOfWork = Carbon::parse(
            $project->deadline->format('Y-m-d') . ' ' . $workSchedule['office_end_time'] . ':00',
            $this->businessTimezone()
        );

        return $deadlineAtEndOfWork->lt($now) ? $deadlineAtEndOfWork : $now;
    }

    private function getFallbackActiveStart(Project $project): ?Carbon
    {
        $createdAt = $this->toBusinessTz($project->created_at);
        if (! $project->start_date) {
            return $createdAt;
        }

        $workSchedule = $this->workSchedule();
        $startDateAtWorkStart = Carbon::parse(
            $project->start_date->format('Y-m-d') . ' ' . $workSchedule['office_start_time'] . ':00',
            $this->businessTimezone()
        );

        if (! $createdAt) {
            return $startDateAtWorkStart;
        }

        return $createdAt->gt($startDateAtWorkStart) ? $createdAt : $startDateAtWorkStart;
    }

    private function toBusinessTz($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->setTimezone($this->businessTimezone());
    }

    private function calculateWorkingMinutes(Carbon $from, Carbon $to): int
    {
        if ($to->lte($from)) {
            return 0;
        }

        $businessTz = $this->businessTimezone();
        $workSchedule = $this->workSchedule();
        $start = $from->copy()->setTimezone($businessTz);
        $end = $to->copy()->setTimezone($businessTz);
        $cursor = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();
        $minutes = 0;

        while ($cursor->lte($lastDay)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $dayDate = $cursor->format('Y-m-d');
                $workMorningStart = Carbon::parse($dayDate . ' ' . $workSchedule['office_start_time'] . ':00', $businessTz);
                $workMorningEnd = Carbon::parse($dayDate . ' ' . $workSchedule['lunch_start_time'] . ':00', $businessTz);
                $workEveningStart = Carbon::parse($dayDate . ' ' . $workSchedule['lunch_end_time'] . ':00', $businessTz);
                $workEveningEnd = Carbon::parse($dayDate . ' ' . $workSchedule['office_end_time'] . ':00', $businessTz);

                if ($workMorningEnd->gt($workMorningStart)) {
                    $minutes += $this->calculateOverlapMinutes($start, $end, $workMorningStart, $workMorningEnd);
                }
                if ($workEveningEnd->gt($workEveningStart)) {
                    $minutes += $this->calculateOverlapMinutes($start, $end, $workEveningStart, $workEveningEnd);
                }
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

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }

    private function refreshPermissionCache(): void
    {
        Cache::forget('spatie.permission.cache');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function businessTimezone(): string
    {
        return (string) config('app.timezone', self::DEFAULT_BUSINESS_TZ);
    }

    private function workSchedule(): array
    {
        $officeStart = $this->normalizeWorkTime(
            Setting::get('office_start_time'),
            self::DEFAULT_WORK_START_TIME
        );
        $lunchStart = $this->normalizeWorkTime(
            Setting::get('lunch_start_time'),
            self::DEFAULT_LUNCH_START_TIME
        );
        $lunchEnd = $this->normalizeWorkTime(
            Setting::get('lunch_end_time'),
            self::DEFAULT_LUNCH_END_TIME
        );
        $officeEnd = $this->normalizeWorkTime(
            Setting::get('office_end_time'),
            self::DEFAULT_WORK_END_TIME
        );

        $isOrderValid = ($officeStart < $lunchStart) && ($lunchStart < $lunchEnd) && ($lunchEnd < $officeEnd);
        if (! $isOrderValid) {
            return [
                'office_start_time' => self::DEFAULT_WORK_START_TIME,
                'lunch_start_time' => self::DEFAULT_LUNCH_START_TIME,
                'lunch_end_time' => self::DEFAULT_LUNCH_END_TIME,
                'office_end_time' => self::DEFAULT_WORK_END_TIME,
            ];
        }

        return [
            'office_start_time' => $officeStart,
            'lunch_start_time' => $lunchStart,
            'lunch_end_time' => $lunchEnd,
            'office_end_time' => $officeEnd,
        ];
    }

    private function normalizeWorkTime($value, string $fallback): string
    {
        $time = is_string($value) ? trim($value) : '';
        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
            return $fallback;
        }

        return $time;
    }

    /**
     * API: Get all staff members.
     */
    public function apiIndex(Request $request)
    {
        $statusFilter = $this->normalizeStaffStatusFilter($request->input('status'));
        $perPage = (int) $request->input('per_page', 10);

        if ($perPage <= 0) {
            $perPage = 10;
        }

        $perPage = min($perPage, 100);

        $staffs = User::with(['roles', 'teams', 'departments'])
            ->whereNotNull('role')
            ->whereIn('role', ['staff', 'super_admin2', 'admin'])
            ->when($statusFilter !== null, function ($query) use ($statusFilter) {
                $query->where(function ($statusQuery) use ($statusFilter) {
                    if ($statusFilter === 'active') {
                        $statusQuery->where('status', 'active')
                            ->orWhere('status', '1');
                    } else {
                        $statusQuery->where('status', 'inactive')
                            ->orWhere('status', '0');
                    }
                });
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();

        $staffs->setCollection(
            $staffs->getCollection()->map(fn(User $member) => $this->formatStaffResource($member))
        );

        return response()->json([
            'success' => true,
            'data' => [
                'staffs' => $staffs,
                'totalStaffs' => $staffs->total(),
                'perPage' => $staffs->perPage(),
                'currentPage' => $staffs->currentPage(),
                'lastPage' => $staffs->lastPage(),
                'from' => $staffs->firstItem(),
                'to' => $staffs->lastItem(),
            ],
        ]);
    }

    private function normalizeStaffStatusFilter(mixed $status): ?string
    {
        if (is_bool($status)) {
            return $status ? 'active' : 'inactive';
        }

        if (is_numeric($status)) {
            return (int) $status === 1 ? 'active' : ((int) $status === 0 ? 'inactive' : null);
        }

        if (! is_string($status)) {
            return null;
        }

        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'active', '1', 'true' => 'active',
            'inactive', '0', 'false' => 'inactive',
            default => null,
        };
    }

    /**
     * API: Get add-staff form options.
     */
    public function apiFormOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'roles' => Role::query()->orderBy('name')->pluck('name')->values(),
                'teams' => Team::getTeamOptions(),
                'departments' => Department::getDepartmentOptions(),
                'statuses' => ['active', 'inactive'],
            ],
        ]);
    }

    /**
     * API: Show a single staff member.
     */
    public function apiShow($id)
    {
        $staff = User::withTrashed()->with(['address', 'roles', 'teams', 'departments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatStaffResource($staff),
        ]);
    }

    /**
     * API: Store a new staff member.
     */
    public function apiStore(Request $request)
    {
        $teams = Team::getTeamOptions();
        $payload = array_merge($request->all(), [
            'first_name' => $request->input('first_name', $request->input('firstName')),
            'last_name' => $request->input('last_name', $request->input('lastName')),
            'sendWelcomeEmail' => $request->input('sendWelcomeEmail', $request->input('send_welcome_email')),
        ]);

        $validator = Validator::make($payload, [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->withoutTrashed(),
            ],
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'departments.*' => 'string|max:255',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'sendWelcomeEmail' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $profileImagePath = basename(FileUpload::uploadOrGenerateAvatar(
                $request->file('profileImage'),
                trim($payload['first_name'] . ' ' . $payload['last_name']),
                'uploads/staff/'
            ));

            $status = $payload['status'] ?? 'active';
            $team = ! empty($payload['team']) ? $payload['team'] : null;
            $departments = collect($payload['departments'] ?? [])
                ->filter(fn($department) => is_string($department) && trim($department) !== '')
                ->map(fn($department) => trim($department))
                ->unique()
                ->values()
                ->all();

            $user = User::create([
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'password' => Hash::make($payload['password']),
                'profile_image' => $profileImagePath,
                'status' => $status,
                'role' => $payload['role'],
            ]);

            $role = Role::where('name', $payload['role'])->first();
            if ($role) {
                $user->assignRole($role);
            }

            $this->syncUserDepartmentAssignments($user, $departments);
            $this->syncUserTeamAssignment($user, $team);

            $this->refreshPermissionCache();

            $sendWelcomeEmail = filter_var($payload['sendWelcomeEmail'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sendWelcomeEmail = $sendWelcomeEmail ?? true;

            if ($sendWelcomeEmail) {
                $staffName = $payload['first_name'] . ' ' . $payload['last_name'];
                try {
                    Mail::to($payload['email'])->send(new StaffInviteMail($staffName, $payload['email'], $payload['password']));
                } catch (\Exception $mailException) {
                    Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $sendWelcomeEmail ? 'Staff created successfully. Invitation email sent.' : 'Staff created successfully. Welcome email was not sent.',
                'data' => $this->formatStaffResource($user->fresh(['roles', 'teams', 'departments'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Update a staff member.
     */
    public function apiUpdate(Request $request, $id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        if ($staff->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Restore this staff member before updating the record.',
            ], 409);
        }

        $validator = $this->validateStaffData($request, $id);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $staff->update($this->buildStaffUpdateData($request, $staff));
            $this->syncUserForStaffUpdate($staff, $request);
            $this->refreshPermissionCache();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully.',
                'data' => $this->formatStaffResource($staff->fresh(['roles', 'teams', 'departments'])),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Soft delete a staff member.
     */
    public function apiDestroy($id)
    {
        $staff = User::findOrFail($id);

        if ($staff->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member is already deleted.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $this->performStaffDelete($staff, false);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Restore a soft deleted staff member.
     */
    public function apiRestore($id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        if (! $staff->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member is already active.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $staff->restore();
            $this->refreshPermissionCache();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff restored successfully.',
                'data' => $this->formatStaffResource($staff->fresh(['roles', 'teams', 'departments'])),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Permanently delete a staff member.
     */
    public function apiForceDelete($id)
    {
        $staff = User::withTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            $this->performStaffDelete($staff, true);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff permanently deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateStaffData(Request $request, int|string $id)
    {
        $teams = Team::getTeamOptions();

        return Validator::make($request->all(), [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'full_name' => 'required_without:first_name|string|max:255',
            'first_name' => 'required_without:full_name|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'gender' => 'nullable|string|max:32',
            'marital_status' => 'nullable|string|max:40',
            'blood_group' => 'nullable|string|max:16',
            'guardian_name' => 'nullable|string|max:150',
            'date_of_birth' => 'nullable|date',
            'alternate_phone' => 'nullable|string|max:25',
            'emergency_contact_name' => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:25',
            'emergency_contact_relationship' => 'nullable|string|max:80',
            'emergency_contact_address' => 'nullable|string|max:1000',
            'current_address' => 'nullable|string|max:1000',
            'permanent_address' => 'nullable|string|max:1000',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'branch' => 'nullable|string|max:150',
            'employee_code' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:150',
            'employee_type' => 'nullable|string|max:50',
            'date_of_joining' => 'nullable|date',
            'date_of_leaving' => 'nullable|date|after_or_equal:date_of_joining',
            'work_mode' => 'nullable|string|max:50',
            'reporting_manager' => 'nullable|string|max:150',
            'official_email' => 'nullable|email|max:255',
            'salary' => 'nullable|numeric|min:0',
            'esi_number' => 'nullable|string|max:64',
            'pf_number' => 'nullable|string|max:64',
            'aadhar_number' => 'nullable|string|max:32',
            'aadhar_front_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'aadhar_back_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'pan_number' => 'nullable|string|max:32',
            'pan_front_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'account_number' => 'nullable|string|max:64',
            'bank_name' => 'nullable|string|max:150',
            'ifsc_code' => 'nullable|string|max:32',
            'past_employment' => 'nullable|array',
            'past_employment.*.company' => 'nullable|string|max:150',
            'past_employment.*.job_title' => 'nullable|string|max:150',
            'past_employment.*.from_date' => 'nullable|date',
            'past_employment.*.to_date' => 'nullable|date',
            // 'role' => ['required', 'string', 'max:255', Rule::exists('roles', 'name')],
            'status' => 'nullable|in:active,inactive',
            'status_reason' => 'nullable|string|max:500',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
            'departments' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (! is_array($value) && ! is_string($value)) {
                        $fail('The departments format is invalid.');
                    }
                },
            ],
            'departments.*' => 'string|max:255',
        ]);
    }

    private function buildStaffUpdateData(Request $request, User $staff): array
    {
        if ($request->filled('full_name') && ! $request->filled('first_name')) {
            $nameParts = preg_split('/\s+/', trim((string) $request->input('full_name')), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = isset($nameParts[1]) && $nameParts[1] !== '' ? $nameParts[1] : null;
        } else {
            $firstName = trim((string) $request->input('first_name'));
            $lastName = $request->has('last_name')
                ? (trim((string) $request->input('last_name')) !== '' ? trim((string) $request->input('last_name')) : null)
                : $staff->last_name;
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'staff',
            'status' => $request->filled('status') ? $request->status : ($staff->status ?: 'active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make((string) $request->input('password'));
        }

        $optionalStringFields = [
            'gender',
            'marital_status',
            'blood_group',
            'guardian_name',
            'alternate_phone',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relationship',
            'emergency_contact_address',
            'current_address',
            'permanent_address',
            'employee_code',
            'designation',
            'employee_type',
            'work_mode',
            'reporting_manager',
            'official_email',
            'esi_number',
            'pf_number',
            'aadhar_number',
            'pan_number',
            'account_number',
            'bank_name',
            'ifsc_code',
            'status_reason',
        ];
        $uppercaseFields = ['pan_number', 'ifsc_code'];
        foreach ($optionalStringFields as $field) {
            if ($request->has($field)) {
                $val = trim((string) $request->input($field));
                if ($val !== '' && in_array($field, $uppercaseFields, true)) {
                    $val = strtoupper($val);
                }
                $updateData[$field] = $val !== '' ? $val : null;
            }
        }

        if ($request->has('past_employment')) {
            $pastRows = collect($request->input('past_employment', []))
                ->filter(fn ($row) => is_array($row))
                ->map(fn ($row) => [
                    'company' => trim((string) ($row['company'] ?? '')),
                    'job_title' => trim((string) ($row['job_title'] ?? '')),
                    'from_date' => trim((string) ($row['from_date'] ?? '')),
                    'to_date' => trim((string) ($row['to_date'] ?? '')),
                ])
                ->filter(fn ($row) => $row['company'] !== '' || $row['job_title'] !== '' || $row['from_date'] !== '' || $row['to_date'] !== '')
                ->values()
                ->all();
            $updateData['past_employment'] = ! empty($pastRows) ? $pastRows : null;
        }

        if ($request->has('branch')) {
            $branch = trim((string) $request->input('branch'));
            $updateData['branch'] = $branch !== '' ? $branch : 'Technofra Main Branch';
        }

        foreach (['date_of_birth', 'date_of_joining', 'date_of_leaving'] as $dateField) {
            if ($request->has($dateField)) {
                $updateData[$dateField] = $request->filled($dateField) ? $request->input($dateField) : null;
            }
        }

        foreach (['aadhar_front_image', 'aadhar_back_image', 'pan_front_image'] as $docField) {
            $docFile = $request->file($docField);
            if ($docFile instanceof UploadedFile && $docFile->isValid()) {
                $existingDocPath = (string) ($staff->getRawOriginal($docField) ?: '');
                $uploadedDocPath = FileUpload::updateFileUpload(
                    $docFile,
                    $existingDocPath,
                    'uploads/staff/documents/'
                );
                if (! empty($uploadedDocPath)) {
                    $updateData[$docField] = $uploadedDocPath;
                }
            }
        }

        // Only update profile image when an actual uploaded file is present.
        // Otherwise keep existing image as-is.
        $profileImageFile = $this->resolveProfileImageFile($request);
        if ($profileImageFile !== null) {
            $existingProfileImage = $staff->getRawOriginal('profile_image');
            $uploadedPath = FileUpload::updateFileUpload(
                $profileImageFile,
                $existingProfileImage ? 'uploads/staff/'.$existingProfileImage : '',
                'uploads/staff/'
            );

            if (! empty($uploadedPath)) {
                $updateData['profile_image'] = basename($uploadedPath);
            }
        }

        return $updateData;
    }

    private function resolveProfileImageFile(Request $request): ?UploadedFile
    {
        $file = $request->file('profileImage');
        if ($file instanceof UploadedFile && $file->isValid()) {
            return $file;
        }

        $file = $request->file('profile_image');
        if ($file instanceof UploadedFile && $file->isValid()) {
            return $file;
        }

        return null;
    }

    private function syncUserForStaffUpdate(User $staff, Request $request): void
    {
        $spatieRole = trim((string) $request->role);
        if ($spatieRole !== '') {
            $staff->syncRoles([$spatieRole]);
        }

        $addressFields = ['address_line_1', 'address_line_2', 'city', 'state', 'country', 'pincode'];
        if ($request->hasAny($addressFields)) {
            $addressPayload = [];
            foreach ($addressFields as $field) {
                $val = trim((string) $request->input($field, ''));
                $addressPayload[$field] = $val !== '' ? $val : null;
            }

            $existingAddress = UserAddress::withTrashed()->where('user_id', $staff->id)->first();
            $hasNonEmptyAddress = collect($addressPayload)->filter()->isNotEmpty();

            if ($existingAddress) {
                if ($existingAddress->trashed() && $hasNonEmptyAddress) {
                    $existingAddress->restore();
                }
                $existingAddress->update($addressPayload);
            } elseif ($hasNonEmptyAddress) {
                UserAddress::create(array_merge(['user_id' => $staff->id], $addressPayload));
            }
        }

        if ($staff->salaryProfile || $request->filled('salary')) {
            $salaryProfileUpdates = [];
            if ($request->has('salary')) {
                $salaryProfileUpdates['monthly_salary'] = $request->filled('salary')
                    ? round((float) $request->input('salary'), 2)
                    : 0;
            }
            if ($request->filled('employee_code')) {
                $salaryProfileUpdates['employee_code'] = trim((string) $request->input('employee_code'));
            }
            if ($request->has('bank_name')) {
                $salaryProfileUpdates['bank_name'] = trim((string) $request->input('bank_name')) ?: null;
            }
            if ($request->has('account_number')) {
                $digits = preg_replace('/\s+/', '', (string) $request->input('account_number'));
                $salaryProfileUpdates['bank_account_last4'] = $digits !== '' ? substr($digits, -4) : null;
            }
            if (! empty($salaryProfileUpdates)) {
                if ($staff->salaryProfile) {
                    $staff->salaryProfile->update($salaryProfileUpdates);
                } else {
                    SalaryProfile::create(array_merge([
                        'staff_user_id' => $staff->id,
                        'monthly_salary' => 0,
                        'standard_deduction' => 0,
                    ], $salaryProfileUpdates));
                }
            }
        }

        $this->syncUserDepartmentAssignments($staff, $this->parseDepartmentNames($request->input('departments', [])));
        if ($request->has('team')) {
            $this->syncUserTeamAssignment($staff, $request->input('team'));
        }
    }

    private function parseDepartmentNames(mixed $rawDepartments): array
    {
        if (is_string($rawDepartments)) {
            $rawDepartments = explode(',', $rawDepartments);
        }

        if (! is_array($rawDepartments)) {
            return [];
        }

        return collect($rawDepartments)
            ->filter(fn ($department) => is_string($department) && trim($department) !== '')
            ->map(fn ($department) => trim($department))
            ->unique()
            ->values()
            ->all();
    }

    private function syncUserDepartmentAssignments(User $user, array $departmentNames): void
    {
        DB::table('staff_department')->where('user_id', $user->id)->delete();

        $normalizedNames = $this->parseDepartmentNames($departmentNames);
        if (empty($normalizedNames)) {
            return;
        }

        $departmentIds = collect($normalizedNames)
            ->map(function (string $departmentName) {
                return Department::firstOrCreate(
                    ['name' => $departmentName],
                    ['is_active' => true]
                )->id;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($departmentIds)) {
            return;
        }

        $timestamp = now();
        DB::table('staff_department')->insert(
            collect($departmentIds)->map(fn($departmentId) => [
                'user_id' => $user->id,
                'department_id' => $departmentId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->all()
        );
    }

    private function syncUserTeamAssignment(User $user, ?string $teamName): void
    {
        DB::table('staff_team')->where('user_id', $user->id)->delete();

        $teamName = trim((string) $teamName);
        if ($teamName === '') {
            return;
        }

        $team = Team::query()->where('name', $teamName)->first();
        if (! $team) {
            return;
        }

        DB::table('staff_team')->insert([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function performStaffDelete(User $staff, bool $forceDelete = false): void
    {
        if ($forceDelete) {
            DB::table('staff_department')->where('user_id', $staff->id)->delete();
            DB::table('staff_team')->where('user_id', $staff->id)->delete();

            if ($staff->profile_image) {
                FileUpload::deleteFile('uploads/staff/'.$staff->profile_image);
            }

            $staff->forceDelete();

            $this->refreshPermissionCache();

            return;
        }

        $staff->delete();
    }

    private function formatStaffResource(User $staff): array
    {
        $staff = $this->hydrateStaffUser($staff->loadMissing(['roles', 'teams', 'departments']));

        return [
            'id' => $staff->id,
            'profile_image' => $staff->profile_image,
            'profile_image_url' => $staff->profile_image ? asset('uploads/staff/' . $staff->profile_image) : null,
            'first_name' => $staff->first_name,
            'last_name' => $staff->last_name,
            'full_name' => $staff->full_name,
            'email' => $staff->email,
            'phone' => $staff->phone,
            'role' => $staff->role,
            'status' => $staff->status,
            'team' => $staff->team,
            'departments' => $staff->departments ?? [],
            'is_deleted' => $staff->trashed(),
            'deleted_at' => optional($staff->deleted_at)?->toISOString(),
            'created_at' => optional($staff->created_at)?->toISOString(),
            'updated_at' => optional($staff->updated_at)?->toISOString(),
            'user' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'roles' => $staff->roles->pluck('name')->values(),
            ],
            'links' => [
                'web' => [
                    'view' => route('view-staff', $staff->id),
                    'update' => route('staff.update', $staff->id),
                    'delete' => route('staff.destroy', $staff->id),
                    'restore' => route('staff.restore', $staff->id),
                    'force_delete' => route('staff.force-delete', $staff->id),
                ],
                'api' => [
                    'show' => url('/api/v1/staff-v2/' . $staff->id),
                    'update' => url('/api/v1/staff-v2/' . $staff->id),
                    'delete' => url('/api/v1/staff-v2/' . $staff->id),
                    'restore' => url('/api/v1/staff-v2/' . $staff->id . '/restore'),
                    'force_delete' => url('/api/v1/staff-v2/' . $staff->id . '/force'),
                ],
            ],
        ];
    }

    private function hydrateStaffUser(User $user): User
    {
        $user->loadMissing(['roles', 'teams', 'departments']);
        $user->setAttribute('team', $user->teams->pluck('name')->filter()->implode(', '));
        $user->setAttribute('departments', $user->departments->pluck('name')->filter()->values()->all());

        return $user;
    }
}
