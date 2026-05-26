<?php

namespace App\Http\Controllers;

use App\Models\ClientIssue;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Task;
use App\Models\User;
use App\Models\VendorService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with renewal statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $today = Carbon::today();
        $weekFromNow = $today->copy()->addWeek();
        $fiveDaysFromNow = $today->copy()->addDays(5);
        $taskBaseQuery = $this->dashboardTaskQuery();
        $projectBaseQuery = $this->dashboardProjectQuery();
        $leadBaseQuery = $this->dashboardLeadQuery();
        $clientIssueBaseQuery = $this->dashboardClientIssueQuery();

        $totalProjects = (clone $projectBaseQuery)->count();
        $totalTasks = (clone $taskBaseQuery)->count();

        $startMonth = $today->copy()->startOfMonth()->subMonths(11);
        $endMonth = $today->copy()->endOfMonth();

        $projectCountsByMonth = (clone $projectBaseQuery)->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $taskCountsByMonth = (clone $taskBaseQuery)->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $projectSummaryLabels = [];
        $projectSummaryProjects = [];
        $projectSummaryTasks = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $monthKey = $month->format('Y-m');

            $projectSummaryLabels[] = $month->format('M');
            $projectSummaryProjects[] = (int) ($projectCountsByMonth[$monthKey] ?? 0);
            $projectSummaryTasks[] = (int) ($taskCountsByMonth[$monthKey] ?? 0);
        }

        $taskStatusOrder = ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        $taskStatusLabels = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        $taskStatusBadges = [
            'not_started' => 'bg-secondary',
            'in_progress' => 'bg-primary',
            'on_hold' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
        ];

        $taskCountsByStatus = (clone $taskBaseQuery)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $taskSummaryLabels = [];
        $taskSummaryCounts = [];
        $taskSummaryBreakdown = [];

        foreach ($taskStatusOrder as $status) {
            $count = (int) ($taskCountsByStatus[$status] ?? 0);
            $label = $taskStatusLabels[$status];

            $taskSummaryLabels[] = $label;
            $taskSummaryCounts[] = $count;
            $taskSummaryBreakdown[] = [
                'label' => $label,
                'count' => $count,
                'badge' => $taskStatusBadges[$status],
            ];
        }

        $leadStatusOrder = ['new', 'contacted', 'qualified', 'converted', 'lost'];
        $leadStatusLabels = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];
        $leadStatusBadges = [
            'new' => 'bg-primary',
            'contacted' => 'bg-info',
            'qualified' => 'bg-warning text-dark',
            'converted' => 'bg-success',
            'lost' => 'bg-danger',
        ];

        $leadCountsByStatus = (clone $leadBaseQuery)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $leadSummaryLabels = [];
        $leadSummaryCounts = [];
        $leadSummaryBreakdown = [];

        foreach ($leadStatusOrder as $status) {
            $count = (int) ($leadCountsByStatus[$status] ?? 0);
            $label = $leadStatusLabels[$status];

            $leadSummaryLabels[] = $label;
            $leadSummaryCounts[] = $count;
            $leadSummaryBreakdown[] = [
                'label' => $label,
                'count' => $count,
                'badge' => $leadStatusBadges[$status],
            ];
        }

        if ($this->isPrivilegedTaskUser()) {
            $staffMembers = User::where('role', 'staff')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name']);
        } else {
            $staffMembers = User::where('id', $this->authenticatedStaffId())
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name']);
        }
        $weekStart = $today->copy()->startOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $yearStart = $today->copy()->startOfYear();

        $teamCountMap = [];
        foreach ($staffMembers as $staff) {
            $displayName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
            $teamCountMap[$staff->id] = [
                'label' => $displayName !== '' ? $displayName : ('Staff #' . $staff->id),
                'weekly' => 0,
                'monthly' => 0,
                'yearly' => 0,
            ];
        }

        $completedTasks = (clone $taskBaseQuery)->where('status', 'completed')
            ->where('updated_at', '>=', $yearStart)
            ->get(['assignees', 'updated_at']);

        foreach ($completedTasks as $task) {
            $completedAt = $task->updated_at;
            $assignees = collect($task->assignees ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            foreach ($assignees as $assigneeId) {
                if (!isset($teamCountMap[$assigneeId])) {
                    continue;
                }

                $teamCountMap[$assigneeId]['yearly']++;

                if ($completedAt && $completedAt->gte($monthStart)) {
                    $teamCountMap[$assigneeId]['monthly']++;
                }

                if ($completedAt && $completedAt->gte($weekStart)) {
                    $teamCountMap[$assigneeId]['weekly']++;
                }
            }
        }

        $teamAvailabilityLabels = array_values(array_map(
            fn ($item) => $item['label'],
            $teamCountMap
        ));
        $teamAvailabilityWeekly = array_values(array_map(
            fn ($item) => $item['weekly'],
            $teamCountMap
        ));
        $teamAvailabilityMonthly = array_values(array_map(
            fn ($item) => $item['monthly'],
            $teamCountMap
        ));
        $teamAvailabilityYearly = array_values(array_map(
            fn ($item) => $item['yearly'],
            $teamCountMap
        ));

        $clientRenewalsDueThisWeek = Service::whereNotNull('client_id')->whereBetween('end_date', [$today, $weekFromNow])->count();
        $vendorRenewalsDueThisWeek = VendorService::whereBetween('end_date', [$today, $weekFromNow])->count();
        $renewalsDueThisWeek = $clientRenewalsDueThisWeek + $vendorRenewalsDueThisWeek;

        $clientOverdueRenewals = Service::whereNotNull('client_id')->where('end_date', '<', $today)->count();
        $vendorOverdueRenewals = VendorService::where('end_date', '<', $today)->count();
        $overdueRenewals = $clientOverdueRenewals + $vendorOverdueRenewals;

        $totalRenewals = Service::whereNotNull('client_id')->count() + VendorService::count();

        $clientCriticalRenewals = $this->orderCriticalRenewals(
            $this->applyCriticalRenewalWindow(
                Service::with(['client', 'vendor'])->whereNotNull('client_id'),
                $today,
                $fiveDaysFromNow
            ),
            $today
        )->get();

        $vendorCriticalRenewals = $this->orderCriticalRenewals(
            $this->applyCriticalRenewalWindow(
                VendorService::with('vendor'),
                $today,
                $fiveDaysFromNow
            ),
            $today
        )->get();

        $supportTickets = (clone $clientIssueBaseQuery)->with(['project', 'customer'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $userId = auth()->id();
        $renewalNotifications = NotificationService::getUrgentNotifications(10, $userId);
        $notificationCounts = NotificationService::getNotificationCounts($userId);
        $hasCriticalNotifications = NotificationService::hasCriticalNotifications($userId);

        return view('index', compact(
            'totalProjects',
            'totalTasks',
            'totalRenewals',
            'renewalsDueThisWeek',
            'overdueRenewals',
            'clientRenewalsDueThisWeek',
            'vendorRenewalsDueThisWeek',
            'clientOverdueRenewals',
            'vendorOverdueRenewals',
            'clientCriticalRenewals',
            'vendorCriticalRenewals',
            'supportTickets',
            'projectSummaryLabels',
            'projectSummaryProjects',
            'projectSummaryTasks',
            'taskSummaryLabels',
            'taskSummaryCounts',
            'taskSummaryBreakdown',
            'leadSummaryLabels',
            'leadSummaryCounts',
            'leadSummaryBreakdown',
            'teamAvailabilityLabels',
            'teamAvailabilityWeekly',
            'teamAvailabilityMonthly',
            'teamAvailabilityYearly',
            'renewalNotifications',
            'notificationCounts',
            'hasCriticalNotifications'
        ));
    }

    private function applyCriticalRenewalWindow(Builder $query, Carbon $today, Carbon $windowEnd): Builder
    {
        return $query->where(function (Builder $query) use ($today, $windowEnd) {
            $query->where('end_date', '<', $today)
                ->orWhereBetween('end_date', [$today, $windowEnd]);
        });
    }

    private function orderCriticalRenewals(Builder $query, Carbon $today): Builder
    {
        return $query->orderByRaw(
            'CASE WHEN end_date < ? THEN 0 ELSE 1 END, end_date ASC',
            [$today->toDateString()]
        );
    }

    private function dashboardTaskQuery(): Builder
    {
        $query = Task::query();

        if ($this->isPrivilegedTaskUser()) {
            return $query;
        }

        $staffId = $this->authenticatedStaffId();

        if ($staffId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($staffId) {
            $builder->whereJsonContains('assignees', $staffId)
                ->orWhereJsonContains('assignees', (string) $staffId)
                ->orWhereJsonContains('followers', $staffId)
                ->orWhereJsonContains('followers', (string) $staffId);
        });
    }

    private function isPrivilegedTaskUser(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2'])) {
            return true;
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));

        return in_array($rawRole, ['admin', 'super-admin', 'super_admin', 'super_admin2'], true);
    }

    private function authenticatedStaffId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));

        if ($rawRole !== 'staff') {
            return null;
        }

        return (int) $user->id;
    }

    private function dashboardProjectQuery(): Builder
    {
        $query = Project::query();

        if ($this->isPrivilegedTaskUser()) {
            return $query;
        }

        $staffId = $this->authenticatedStaffId();

        if ($staffId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($staffId) {
            $builder->whereJsonContains('members', $staffId)
                ->orWhereJsonContains('members', (string) $staffId);
        });
    }

    private function dashboardLeadQuery(): Builder
    {
        $query = Lead::query();

        if ($this->isPrivilegedTaskUser()) {
            return $query;
        }

        $staffId = $this->authenticatedStaffId();

        if ($staffId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereExists(function ($subQuery) use ($staffId) {
            $subQuery->selectRaw('1')
                ->from('assigned_leads')
                ->where('assigned_leads.lead_model', 'lead')
                ->whereColumn('assigned_leads.lead_id', 'leads.id')
                ->where(function ($jsonQuery) use ($staffId) {
                    $jsonQuery
                        ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $staffId])
                        ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $staffId]);
                });
        });
    }

    private function dashboardClientIssueQuery(): Builder
    {
        $query = ClientIssue::query();

        if ($this->isPrivilegedTaskUser()) {
            return $query;
        }

        $staffId = $this->authenticatedStaffId();

        if ($staffId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('teamAssignments', function (Builder $builder) use ($staffId) {
            $builder->where('assigned_to', $staffId);
        });
    }
}


