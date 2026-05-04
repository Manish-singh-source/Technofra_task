<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientIssue;
use App\Models\Project;
use App\Models\Service;
use App\Models\Task;
use App\Models\VendorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {

        // Renewal Data
        $clientRenewals = Service::with('client')
            // ->latest()
            ->whereDate('end_date', '>=', Carbon::today())
            ->orderBy('end_date')
            ->limit(3)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'client_id' => $service->client_id,
                'status' => $service->status,
                'start_date' => $service->start_date,
                'end_date' => $service->end_date,
                'billing_date' => $service->billing_date,
                'name' => optional($service->client)->first_name,
                'type' => 'client_renewal'
            ]);

        $vendorRenewals = VendorService::with('vendor')
            // ->latest()
            ->whereDate('end_date', '>=', Carbon::today())
            ->orderBy('end_date')
            ->limit(3)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'vendor_id' => $service->vendor_id,
                'status' => $service->status,
                'start_date' => $service->start_date,
                'end_date' => $service->end_date,
                'billing_date' => $service->billing_date,
                'name' => optional($service->vendor)->name,
                'type' => 'vendor_renewal'
            ]);

        $renewals = $clientRenewals
            ->concat($vendorRenewals)
            ->sortBy('end_date')
            ->take(3)
            ->values();

        // Projects and Tasks Count
        $projectsCount = Project::count();
        $tasksCount = Task::count();

        // Projects Summary month wise
        $months = collect(range(11, 0))
            ->map(fn($offset) => Carbon::now()->subMonths($offset)->format('Y-m'));

        $projectMonthlyCounts = Project::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $taskMonthlyCounts = Task::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $projectsTasksMonthlySummary = $months->map(function ($monthKey) use ($projectMonthlyCounts, $taskMonthlyCounts) {
            $projects = (int) ($projectMonthlyCounts[$monthKey] ?? 0);
            $tasks = (int) ($taskMonthlyCounts[$monthKey] ?? 0);

            return [
                'month_key' => $monthKey,
                'month_label' => Carbon::createFromFormat('Y-m', $monthKey)->format('M Y'),
                'project_count' => $projects,
                'task_count' => $tasks,
                'total_count' => $projects + $tasks,
            ];
        })->values();

        // Tasks Summary 
        $notStartedTasks = Task::where('status', 'not_started')->count();
        $completedTasks = Task::where('status', 'completed')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $onHoldTasks = Task::where('status', 'on_hold')->count();
        $cancelledTasks = Task::where('status', 'cancelled')->count();
        $tasksSummary = [
            'total' => $tasksCount,
            'not_started' => $notStartedTasks,
            'completed' => $completedTasks,
            'in_progress' => $inProgressTasks,
            'on_hold' => $onHoldTasks,
            'cancelled' => $cancelledTasks,
        ];

        // Client Issues 
        $clientIssues = ClientIssue::with('project', 'customer')->latest()->limit(3)->get()->map(fn($issue) => [
            'id' => $issue->id,
            'issue_description' => $issue->issue_description,
            'priority' => $issue->priority,
            'status' => $issue->status,
            // 'client_id' => $issue->customer_id,
            'project_name' => optional($issue->project)->project_name,
            'customer_name' => optional($issue->customer)->first_name,
            'created_at' => $issue->created_at,
            'updated_at' => $issue->updated_at,
        ]);

        return response()->json([
            'data' => 'Dashboard Data Retrieved Successfully.',
            'renewals' => $renewals,
            'project_summary' => [
                'total_projects' => $projectsCount,
                'total_tasks' => $tasksCount,
                'projects_tasks_monthly' => $projectsTasksMonthlySummary,
            ],
            'tasks_summary' => $tasksSummary,
            'client_issues' => $clientIssues,
        ]);
    }
}
