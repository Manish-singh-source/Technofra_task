<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ClientIssue;
use App\Models\Project;
use App\Models\Task;
use App\Models\Lead;
use App\Models\Staff;
use App\Services\NotificationService;
use Carbon\Carbon;
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
        // Get current date
        $today = Carbon::today();
        $weekFromNow = $today->copy()->addWeek();
        $fiveDaysFromNow = $today->copy()->addDays(5);

        $totalProjects = Project::count();
        $totalTasks = Task::count();

        // Build monthly project/task summary for last 12 months
        $startMonth = $today->copy()->startOfMonth()->subMonths(11);
        $endMonth = $today->copy()->endOfMonth();

        $projectCountsByMonth = Project::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $taskCountsByMonth = Task::select(
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

        // Build task status summary for dashboard doughnut chart
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

        $taskCountsByStatus = Task::select('status', DB::raw('COUNT(*) as total'))
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

        // Build leads summary for Leads Overview chart
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

        $leadCountsByStatus = Lead::select('status', DB::raw('COUNT(*) as total'))
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

        // Team availability: completed tasks by staff (weekly / monthly / yearly)
        $staffMembers = Staff::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);
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

        $completedTasks = Task::where('status', 'completed')
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

        // Calculate renewal statistics
        $totalRenewals = Service::count();

        // Renewals due this week (services ending within 7 days)
        $renewalsDueThisWeek = Service::whereBetween('end_date', [$today, $weekFromNow])->count();

        // Overdue renewals (services that ended before today)
        $overdueRenewals = Service::where('end_date', '<', $today)->count();

        // Combined critical renewals (overdue + upcoming in next 5 days)
        $criticalRenewals = Service::with(['client', 'vendor'])
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('end_date', '<', $today) // Overdue
                      ->orWhereBetween('end_date', [$today, $fiveDaysFromNow]); // Upcoming
            })
            ->orderByRaw('CASE WHEN end_date < ? THEN 0 ELSE 1 END, end_date ASC', [$today])
            ->get();

        // Get support tickets (client issues) - Recent 10
        $supportTickets = ClientIssue::with(['project', 'customer'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        // Get notification data
        $renewalNotifications = NotificationService::getUrgentNotifications(10);
        $notificationCounts = NotificationService::getNotificationCounts();
        $hasCriticalNotifications = NotificationService::hasCriticalNotifications();

        return view('index', compact(
            'totalProjects',
            'totalTasks',
            'totalRenewals',
            'renewalsDueThisWeek',
            'overdueRenewals',
            'criticalRenewals',
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
}
