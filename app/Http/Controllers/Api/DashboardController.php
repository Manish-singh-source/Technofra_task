<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClientIssue;
use App\Models\DigitalMarketingLead;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Task;
use App\Models\VendorService;
use App\Models\WebappLead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/dashboard",
     *     tags={"Dashboard"},
     *     summary="Get dashboard data",
     *     description="Returns renewal highlights, project/task monthly summary, task status summary, and recent client issues.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $projectBaseQuery = $this->dashboardProjectQuery();
        $taskBaseQuery = $this->dashboardTaskQuery();
        $leadBaseQuery = $this->dashboardLeadQuery();
        $clientIssueBaseQuery = $this->dashboardClientIssueQuery();
        $canViewAll = $this->isPrivilegedDashboardUser();

        // Renewal Data
        $clientRenewalsQuery = Service::with('client.businessDetail')
            ->whereNotNull('client_id')
            ->whereDate('end_date', '>', $today);

        if (! $canViewAll) {
            $clientRenewalsQuery->where('client_id', optional($user)->id);
        }

        $clientRenewals = $clientRenewalsQuery
            ->orderBy('end_date')
            ->limit(3)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'client_id' => $service->client_id,
                'status' => $service->effective_status,
                'start_date' => $service->start_date,
                'end_date' => $service->end_date,
                'billing_date' => $service->billing_date,
                'name' => optional($service->client)->first_name,
                'company_name' => optional($service->client?->businessDetail)->company_name,
                'type' => 'client_renewal'
            ]);

        $vendorRenewalsQuery = VendorService::with('vendor')
            ->whereDate('end_date', '>', $today);

        if (! $canViewAll) {
            $vendorRenewalsQuery->whereIn('vendor_id', Service::query()
                ->whereNotNull('vendor_id')
                ->where('client_id', optional($user)->id)
                ->select('vendor_id'));
        }

        $vendorRenewals = $vendorRenewalsQuery
            ->orderBy('end_date')
            ->limit(3)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'vendor_id' => $service->vendor_id,
                'status' => $service->effective_status,
                'start_date' => $service->start_date,
                'end_date' => $service->end_date,
                'billing_date' => $service->billing_date,
                'name' => optional($service->vendor)->name,
                'type' => 'vendor_renewal'
            ]);

        $renewals = $clientRenewals
            ->concat($vendorRenewals)
            ->sortBy('end_date')
            ->values();

        // Projects and Tasks Count
        $projectsCount = (clone $projectBaseQuery)->count();
        $tasksCount = (clone $taskBaseQuery)->count();

        // Projects Summary month wise
        $months = collect(range(11, 0))
            ->map(fn($offset) => Carbon::now()->subMonths($offset)->format('Y-m'));

        $projectMonthlyCounts = (clone $projectBaseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $taskMonthlyCounts = (clone $taskBaseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
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
        $notStartedTasks = (clone $taskBaseQuery)->where('status', 'not_started')->count();
        $completedTasks = (clone $taskBaseQuery)->where('status', 'completed')->count();
        $inProgressTasks = (clone $taskBaseQuery)->where('status', 'in_progress')->count();
        $onHoldTasks = (clone $taskBaseQuery)->where('status', 'on_hold')->count();
        $cancelledTasks = (clone $taskBaseQuery)->where('status', 'cancelled')->count();
        $tasksSummary = [
            'total' => $tasksCount,
            'not_started' => $notStartedTasks,
            'completed' => $completedTasks,
            'in_progress' => $inProgressTasks,
            'on_hold' => $onHoldTasks,
            'cancelled' => $cancelledTasks,
        ];

        // Client Issues 
        $clientIssues = (clone $clientIssueBaseQuery)
            ->with('project', 'customer')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn($issue) => [
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
            'client_renewals' => $clientRenewals,
            'vendor_renewals' => $vendorRenewals,
            'project_summary' => [
                'total_projects' => $projectsCount,
                'total_tasks' => $tasksCount,
                'projects_tasks_monthly' => $projectsTasksMonthlySummary,
            ],
            'tasks_summary' => $tasksSummary,
            'client_issues' => $clientIssues,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/quick-stats",
     *     tags={"Dashboard"},
     *     summary="Get quick statistics",
     *     description="Returns total projects, leads, tasks, and issues for dashboard widgets/cards.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Quick stats retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function quickStats()
    {
        $projectBaseQuery = $this->dashboardProjectQuery();
        $taskBaseQuery = $this->dashboardTaskQuery();
        $leadBaseQuery = $this->dashboardLeadQuery();
        $clientIssueBaseQuery = $this->dashboardClientIssueQuery();
        $digitalMarketingLeadsBaseQuery = $this->dashboardDigitalMarketingLeadQuery();
        $webAppLeadsBaseQuery = $this->dashboardWebAppLeadQuery();

        $leadsCount = (clone $leadBaseQuery)->count();
        $digitalMarketingLeadsCount = (clone $digitalMarketingLeadsBaseQuery)->count();
        $webAppLeads = WebappLead::count();
        $webAppLeads = (clone $webAppLeadsBaseQuery)->count();

        $total_leads = $leadsCount + $digitalMarketingLeadsCount + $webAppLeads;


        $data = [
            'total_projects' => (clone $projectBaseQuery)->count() ?? 0,
            'total_leads' => $total_leads ?? 0,
            'total_tasks' => (clone $taskBaseQuery)->count() ?? 0,
            'total_issues' => (clone $clientIssueBaseQuery)->count() ?? 0,
        ];

        return ApiResponse::success($data, 'Quick Stats Retrieved Successfully.');
    }

    private function isPrivilegedDashboardUser(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'super_admin2', 'super_admin']);
    }

    private function dashboardProjectQuery(): Builder
    {
        $query = Project::query();

        if ($this->isPrivilegedDashboardUser()) {
            return $query;
        }

        $userId = (int) auth()->id();

        return $query->where(function (Builder $builder) use ($userId) {
            $builder->whereJsonContains('members', $userId)
                ->orWhereJsonContains('members', (string) $userId);
        });
    }

    private function dashboardTaskQuery(): Builder
    {
        $query = Task::query();

        if ($this->isPrivilegedDashboardUser()) {
            return $query;
        }

        $userId = (int) auth()->id();

        return $query->where(function (Builder $builder) use ($userId) {
            $builder->whereJsonContains('assignees', $userId)
                ->orWhereJsonContains('assignees', (string) $userId)
                ->orWhereJsonContains('followers', $userId)
                ->orWhereJsonContains('followers', (string) $userId);
        });
    }

    private function dashboardLeadQuery(): Builder
    {
        $query = Lead::query();

        if ($this->isPrivilegedDashboardUser()) {
            return $query;
        }

        $userId = (int) auth()->id();

        return $query->whereExists(function ($subQuery) use ($userId) {
            $subQuery->selectRaw('1')
                ->from('assigned_leads')
                ->where('assigned_leads.lead_model', 'lead')
                ->whereColumn('assigned_leads.lead_id', 'leads.id')
                ->where(function ($jsonQuery) use ($userId) {
                    $jsonQuery
                        ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                        ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                });
        });
    }

    private function dashboardClientIssueQuery(): Builder
    {
        $query = ClientIssue::query();

        if ($this->isPrivilegedDashboardUser()) {
            return $query;
        }

        $userId = (int) auth()->id();

        return $query->whereHas('teamAssignments', function (Builder $builder) use ($userId) {
            $builder->where('assigned_to', $userId);
        });
    }

    private function dashboardDigitalMarketingLeadQuery(): Builder
    {
        $query = DigitalMarketingLead::query();

        if ($this->isPrivilegedDashboardUser()) {
            return $query;
        }

        $userId = (int) auth()->id();

        return $query->whereExists(function ($subQuery) use ($userId) {
            $subQuery->selectRaw('1')
                ->from('assigned_leads')
                ->where('assigned_leads.lead_model', 'digital_marketing')
                ->whereColumn('assigned_leads.lead_id', 'digital_marketing_leads.id')
                ->where(function ($jsonQuery) use ($userId) {
                    $jsonQuery
                        ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                        ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                });
        });
    }

    private function dashboardWebAppLeadQuery(): Builder
    {
        $query = WebappLead::query();

        if ($this->isPrivilegedDashboardUser()) {
            return $query;
        }

        $userId = (int) auth()->id();

        return $query->whereExists(function ($subQuery) use ($userId) {
            $subQuery->selectRaw('1')
                ->from('assigned_leads')
                ->where('assigned_leads.lead_model', 'webapp')
                ->whereColumn('assigned_leads.lead_id', 'webapp_leads.id')
                ->where(function ($jsonQuery) use ($userId) {
                    $jsonQuery
                        ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                        ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                });
        });
    }
}
