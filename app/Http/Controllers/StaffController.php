<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Team;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Mail\StaffInviteMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    private const DEFAULT_BUSINESS_TZ = 'UTC';
    private const DEFAULT_WORK_START_TIME = '09:00';
    private const DEFAULT_LUNCH_START_TIME = '13:00';
    private const DEFAULT_LUNCH_END_TIME = '14:00';
    private const DEFAULT_WORK_END_TIME = '18:00';

    /**
     * Display the staff creation form.
     */
    public function create()
    {
        $roles = Role::all();
        $teams = Team::getTeamOptions();
        return view('add-staff', compact('roles', 'teams'));
    }

    /**
     * Display a listing of staff members.
     */
    public function index()
    {
        $staff = Staff::with('user')->get();
        return view('staff', compact('staff'));
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        $staff = Staff::with('user')->findOrFail($id);
        $roles = Role::all();
        $teams = Team::getTeamOptions();
        $projects = $staff->projects()->latest()->get();
        $tasks = $staff->tasks()->with('project')->latest()->get();
        $loggedTimeStats = $this->buildStaffLoggedTimeStats($staff);
        return view('view-staff', compact('staff', 'roles', 'teams', 'projects', 'tasks', 'loggedTimeStats'));
    }

    private function buildStaffLoggedTimeStats(Staff $staff): array
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

    private function getStaffRatioForProject(Project $project, int $staffId): float
    {
        $memberIds = collect($project->members ?? [])
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        if (!$memberIds->contains($staffId)) {
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
        if (!$project->deadline) {
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
        if (!$project->start_date) {
            return $createdAt;
        }

        $workSchedule = $this->workSchedule();
        $startDateAtWorkStart = Carbon::parse(
            $project->start_date->format('Y-m-d') . ' ' . $workSchedule['office_start_time'] . ':00',
            $this->businessTimezone()
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
        if (!$isOrderValid) {
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
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
            return $fallback;
        }

        return $time;
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
            'departments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $staff = Staff::findOrFail($id);
        
        // Get old role and email to update user later
        $oldRole = $staff->role;
        $oldEmail = $staff->email;
        
        DB::beginTransaction();
        try {
            $staff->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
                'team' => $request->team ?: null,
                'departments' => $request->departments,
            ]);

            // Update associated user
            $user = $staff->user ?? User::where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                ]);

                // Update role if changed
                if ($oldRole !== $request->role) {
                    $user->removeRole($oldRole);
                    $newRole = Role::where('name', $request->role)->first();
                    if ($newRole) {
                        $user->assignRole($newRole);
                    }
                }
            }

            // Clear permission cache
            Cache::forget('spatie.permission.cache');
            
            DB::commit();
            return redirect()->route('staff')->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update staff: ' . $e->getMessage());
        }
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
            'email' => 'required|email|unique:staff,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profileImage')) {
                $image = $request->file('profileImage');
                $ext = $image->getClientOriginalExtension();
                $imageName = time() . "." . $ext;
                $image->move(public_path('uploads/staff'), $imageName);
                $profileImagePath = $imageName;
            }

            // Create User first
            $user = User::create([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role to user
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Create Staff record linked to user
            $staff = Staff::create([
                'user_id' => $user->id,
                'profile_image' => $profileImagePath,
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'departments' => $request->departments,
                'team' => $request->team ?: null,
            ]);

            // Clear permission cache
            Cache::forget('spatie.permission.cache');

            // Send invitation email with login credentials
            $staffName = $request->firstName . ' ' . $request->lastName;
            try {
                Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
            } catch (\Exception $mailException) {
                // Log the mail error but don't fail the staff creation
                Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
            }

            DB::commit();
            return redirect()->route('staff')->with('success', 'Staff added successfully. Invitation email sent to ' . $request->email);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create staff: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete associated user if exists
            if ($staff->user) {
                $staff->user->delete();
            } else {
                // Fallback: find user by email
                $user = User::where('email', $staff->email)->first();
                if ($user) {
                    $user->delete();
                }
            }
            
            $staff->delete();
            
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
        $ids = explode(',', $request->ids);
        
        if (empty($ids)) {
            return redirect()->route('staff')->with('error', 'No staff selected for deletion.');
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $staff = Staff::find($id);
                if ($staff) {
                    // Delete associated user if exists
                    if ($staff->user) {
                        $staff->user->delete();
                    } else {
                        // Fallback: find user by email
                        $user = User::where('email', $staff->email)->first();
                        if ($user) {
                            $user->delete();
                        }
                    }
                    $staff->delete();
                }
            }
            
            DB::commit();
            return redirect()->route('staff')->with('success', 'Selected staff members deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('staff')->with('error', 'Failed to delete selected staff: ' . $e->getMessage());
        }
    }

    /**
     * API: Get all staff members.
     */
    public function apiIndex()
    {
        $staff = Staff::with('user.roles')->get();
        return response()->json([
            'success' => true,
            'data' => $staff,
        ]);
    }

    /**
     * API: Store a new staff member.
     */
    public function apiStore(Request $request)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create User first
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role to user
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Create Staff record linked to user
            $staff = Staff::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'departments' => $request->departments,
                'team' => $request->team ?: null,
            ]);

            // Clear permission cache
            Cache::forget('spatie.permission.cache');

            // Send invitation email with login credentials
            $staffName = $request->first_name . ' ' . $request->last_name;
            try {
                Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
            } catch (\Exception $mailException) {
                // Log the mail error but don't fail the staff creation
                Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully. Invitation email sent.',
                'data' => $staff->load('user.roles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff: ' . $e->getMessage(),
            ], 500);
        }
    }
}
