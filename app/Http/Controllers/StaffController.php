<?php

namespace App\Http\Controllers;

use App\Mail\StaffInviteMail;
use App\Models\Department;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffController extends Controller
{
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
            ->whereNotNull('role')
            ->where('role', 'staff')
            ->orderBy('first_name')
            ->get()
            ->map(fn (User $user) => $this->hydrateStaffUser($user));

        return view('staff.index', compact('staff'));
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
            $profileImagePath = null;
            if ($request->hasFile('profileImage')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profileImage'));
            }

            $status = $request->status ?? 'active';
            $team = $request->filled('team') ? $request->team : null;
            $departments = collect($request->input('departments', []))
                ->filter(fn ($department) => is_string($department) && trim($department) !== '')
                ->map(fn ($department) => trim($department))
                ->unique()
                ->values()
                ->all();

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

            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            $this->syncUserDepartmentAssignments($user, $departments);
            $this->syncUserTeamAssignment($user, $team);

            $this->refreshPermissionCache();

            $sendWelcomeEmail = $request->boolean('sendWelcomeEmail');
            if ($sendWelcomeEmail) {
                $staffName = $request->firstName.' '.$request->lastName;
                try {
                    Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
                } catch (\Exception $mailException) {
                    Log::error('Failed to send staff invitation email: '.$mailException->getMessage());
                }
            }

            DB::commit();
            $successMessage = $sendWelcomeEmail
                ? 'Staff added successfully. Invitation email sent to '.$request->email
                : 'Staff added successfully. Welcome email was not sent.';

            return redirect()->route('staff')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create staff: '.$e->getMessage());
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        $staff = $this->hydrateStaffUser(
            User::withTrashed()
                ->with(['roles', 'teams', 'departments'])
                ->findOrFail($id)
        );

        $roles = Role::all();
        $teams = Team::getTeamOptions();
        $departments = Department::getDepartmentOptions();
        $projects = $staff->projects()->latest()->get();
        $tasks = $staff->tasks()->with('project')->latest()->get();
        $loggedTimeStats = $this->buildStaffLoggedTimeStats($staff);

        return view('staff.view', compact('staff', 'roles', 'teams', 'departments', 'projects', 'tasks', 'loggedTimeStats'));
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
            $oldRole = $staff->role;

            $staff->update($this->buildStaffUpdateData($request, $staff));
            $this->syncUserForStaffUpdate($staff, $request, $oldRole);
            $this->refreshPermissionCache();

            DB::commit();

            return redirect()->route('view-staff', $staff->id)->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to update staff: '.$e->getMessage());
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

            return redirect()->back()->with('error', 'Failed to delete staff: '.$e->getMessage());
        }
    }

    /**
     * Delete selected staff members.
     */
    public function deleteSelected(Request $request)
    {
        $ids = collect(explode(',', (string) $request->ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
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

            return redirect()->route('staff')->with('success', 'Selected staff members deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('staff')->with('error', 'Failed to delete selected staff: '.$e->getMessage());
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

            return redirect()->back()->with('error', 'Failed to restore staff: '.$e->getMessage());
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

            return redirect()->back()->with('error', 'Failed to permanently delete staff: '.$e->getMessage());
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
                'statusLogs' => fn ($query) => $query->orderBy('started_at'),
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

    private function getStaffRatioForProject(Project $project, int $staffId): float
    {
        $memberIds = collect($project->members ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
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
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($participantId) => isset($memberWeights[$participantId]))
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
            $project->deadline->format('Y-m-d').' '.$workSchedule['office_end_time'].':00',
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
            $project->start_date->format('Y-m-d').' '.$workSchedule['office_start_time'].':00',
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
                $workMorningStart = Carbon::parse($dayDate.' '.$workSchedule['office_start_time'].':00', $businessTz);
                $workMorningEnd = Carbon::parse($dayDate.' '.$workSchedule['lunch_start_time'].':00', $businessTz);
                $workEveningStart = Carbon::parse($dayDate.' '.$workSchedule['lunch_end_time'].':00', $businessTz);
                $workEveningEnd = Carbon::parse($dayDate.' '.$workSchedule['office_end_time'].':00', $businessTz);

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
        $staff = User::query()
            ->withTrashed()
            ->with(['roles', 'teams', 'departments'])
            ->whereNotNull('role')
            ->when(! $request->boolean('include_trashed'), function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $staff->map(fn (User $member) => $this->formatStaffResource($member)),
        ]);
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
        $staff = User::withTrashed()->with(['roles', 'teams', 'departments'])->findOrFail($id);

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
            $profileImagePath = null;
            if ($request->hasFile('profileImage')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profileImage'));
            }

            $status = $payload['status'] ?? 'active';
            $team = ! empty($payload['team']) ? $payload['team'] : null;
            $departments = collect($payload['departments'] ?? [])
                ->filter(fn ($department) => is_string($department) && trim($department) !== '')
                ->map(fn ($department) => trim($department))
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
                $staffName = $payload['first_name'].' '.$payload['last_name'];
                try {
                    Mail::to($payload['email'])->send(new StaffInviteMail($staffName, $payload['email'], $payload['password']));
                } catch (\Exception $mailException) {
                    Log::error('Failed to send staff invitation email: '.$mailException->getMessage());
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
                'message' => 'Failed to create staff: '.$e->getMessage(),
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
            $oldRole = $staff->role;

            $staff->update($this->buildStaffUpdateData($request, $staff));
            $this->syncUserForStaffUpdate($staff, $request, $oldRole);
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
                'message' => 'Failed to update staff: '.$e->getMessage(),
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
                'message' => 'Failed to delete staff: '.$e->getMessage(),
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
                'message' => 'Failed to restore staff: '.$e->getMessage(),
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
                'message' => 'Failed to permanently delete staff: '.$e->getMessage(),
            ], 500);
        }
    }

    private function validateStaffData(Request $request, int|string $id)
    {
        $teams = Team::getTeamOptions();

        return Validator::make($request->all(), [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
            'departments' => 'nullable|array',
            'departments.*' => 'string|max:255',
        ]);
    }

    private function buildStaffUpdateData(Request $request, User $staff): array
    {
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'staff',
            'status' => $request->status,
        ];

        if ($request->hasFile('profileImage')) {
            $updateData['profile_image'] = $this->uploadProfileImage($request->file('profileImage'), $staff->profile_image, $staff->id);
        }

        return $updateData;
    }

    private function syncUserForStaffUpdate(User $staff, Request $request, string $oldRole): void
    {
        $staff->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'profile_image' => $staff->profile_image,
            'status' => $request->status,
            'role' => $request->role,
        ]);

        if ($oldRole !== $request->role) {
            if ($staff->hasRole($oldRole)) {
                $staff->removeRole($oldRole);
            }

            $newRole = Role::where('name', $request->role)->first();
            if ($newRole) {
                $staff->assignRole($newRole);
            }
        }

        $this->syncUserDepartmentAssignments($staff, $request->input('departments', []));
        $this->syncUserTeamAssignment($staff, $request->input('team'));
    }

    private function syncUserDepartmentAssignments(User $user, array $departmentNames): void
    {
        DB::table('staff_department')->where('user_id', $user->id)->delete();

        $departmentIds = Department::query()
            ->whereIn('name', collect($departmentNames)
                ->filter(fn ($department) => is_string($department) && trim($department) !== '')
                ->map(fn ($department) => trim($department))
                ->unique()
                ->values()
                ->all())
            ->pluck('id')
            ->all();

        if (empty($departmentIds)) {
            return;
        }

        $timestamp = now();
        DB::table('staff_department')->insert(
            collect($departmentIds)->map(fn ($departmentId) => [
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

    private function uploadProfileImage($image, ?string $oldImage = null, ?int $staffId = null): string
    {
        $extension = $image->getClientOriginalExtension();
        $imageName = $staffId
            ? time().'_'.$staffId.'.'.$extension
            : time().'.'.$extension;

        $image->move(public_path('uploads/staff'), $imageName);

        if ($oldImage) {
            $oldImagePath = public_path('uploads/staff/'.$oldImage);
            if (file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }

        return $imageName;
    }

    private function performStaffDelete(User $staff, bool $forceDelete = false): void
    {
        if ($forceDelete) {
            DB::table('staff_department')->where('user_id', $staff->id)->delete();
            DB::table('staff_team')->where('user_id', $staff->id)->delete();

            if ($staff->profile_image) {
                $imagePath = public_path('uploads/staff/'.$staff->profile_image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
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
            'profile_image_url' => $staff->profile_image ? asset('uploads/staff/'.$staff->profile_image) : null,
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
                    'show' => url('/api/staff/'.$staff->id),
                    'update' => url('/api/staff/'.$staff->id),
                    'delete' => url('/api/staff/'.$staff->id),
                    'restore' => url('/api/staff/'.$staff->id.'/restore'),
                    'force_delete' => url('/api/staff/'.$staff->id.'/force'),
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
