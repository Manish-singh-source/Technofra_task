<?php

namespace App\Http\Controllers;

use App\Helpers\RenewalStatusHelper;
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
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Text;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with renewal statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        RenewalStatusHelper::markExpiredClientRenewals();
        RenewalStatusHelper::markExpiredVendorRenewals();

        $today = Carbon::today();
        $weekFromNow = $today->copy()->addWeek();
        $fiveDaysFromNow = $today->copy()->addDays(5);
        $taskBaseQuery = $this->dashboardTaskQuery();
        $projectBaseQuery = $this->dashboardProjectQuery();
        $leadBaseQuery = $this->dashboardLeadQuery();
        $clientIssueBaseQuery = $this->dashboardClientIssueQuery();
        $clientRenewalBaseQuery = $this->dashboardClientRenewalQuery();
        $vendorRenewalBaseQuery = $this->dashboardVendorRenewalQuery();

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

        $clientRenewalsDueThisWeek = (clone $clientRenewalBaseQuery)
            ->whereBetween('end_date', [$today, $weekFromNow])
            ->count();
        $vendorRenewalsDueThisWeek = (clone $vendorRenewalBaseQuery)
            ->whereBetween('end_date', [$today, $weekFromNow])
            ->count();
        $renewalsDueThisWeek = $clientRenewalsDueThisWeek + $vendorRenewalsDueThisWeek;

        $clientExpiredRenewals = (clone $clientRenewalBaseQuery)
            ->where('status', 'expired')
            ->count();
        $vendorExpiredRenewals = (clone $vendorRenewalBaseQuery)
            ->where('status', 'expired')
            ->count();
        $overdueRenewals = $clientExpiredRenewals + $vendorExpiredRenewals;

        $totalRenewals = (clone $clientRenewalBaseQuery)->count() + (clone $vendorRenewalBaseQuery)->count();

        $clientCriticalRenewals = $this->orderCriticalRenewals(
            $this->applyCriticalRenewalWindow(
                $clientRenewalBaseQuery->with(['client', 'vendor']),
                $today,
                $fiveDaysFromNow
            ),
            $today
        )->get();

        $vendorCriticalRenewals = $this->orderCriticalRenewals(
            $this->applyCriticalRenewalWindow(
                $vendorRenewalBaseQuery->with('vendor'),
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
            'clientExpiredRenewals',
            'vendorExpiredRenewals',
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

    /**
     * Download a Word document containing today's operations summary.
     */
    public function downloadOperationsSummary()
    {
        $today = Carbon::today();
        $weekFromNow = $today->copy()->addWeek();
        $documentDate = $today->format('d M Y');

        $releaseTasks = $this->dashboardTaskQuery()
            ->whereDate('deadline', $today)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('project')
            ->orderBy('priority')
            ->orderBy('deadline')
            ->get();


        $vendorFollowUps = $this->dashboardVendorRenewalQuery()
            ->with('vendor')
            ->where(function (Builder $query) use ($today, $weekFromNow) {
                $query->whereIn('status', ['pending', 'inactive', 'expired'])
                    ->orWhereBetween('end_date', [$today, $weekFromNow]);
            })
            ->orderBy('end_date')
            ->get();

        $clientGoLives = $this->dashboardProjectQuery()
            ->whereNotNull('deployment_date')
            ->whereBetween('deployment_date', [$today, $weekFromNow])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['customerUser', 'manager'])
            ->orderBy('deployment_date')
            ->get();

        $pendingRenewals = $this->dashboardClientRenewalQuery()
            ->with(['company', 'vendor'])
            ->where(function (Builder $query) use ($today, $weekFromNow) {
                $query->where('status', 'expired')
                    ->orWhereBetween('end_date', [$today, $weekFromNow]);
            })
            ->orderBy('end_date')
            ->get();

        Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $section = $phpWord->addSection();

        $section->addText('Daily Operations Summary');
        $section->addText('Date: ' . $documentDate);
        $section->addTextBreak(1);

        $this->addSummarySection($section, '1. Work Scheduled for Release Today', $releaseTasks->map(function (Task $task) {
            return [
                'title' => $task->title,
                'meta' => trim(collect([
                    $task->project?->project_name ? 'Project: ' . $task->project->project_name : null,
                    $task->priority ? 'Priority: ' . ucfirst($task->priority) : null,
                    $task->deadline ? 'Deadline: ' . $task->deadline->format('d M Y') : null,
                ])->filter()->implode(' | ')),
                'status' => ucfirst(str_replace('_', ' ', (string) $task->status)),
            ];
        })->all());

        $this->addSummarySection($section, '2. Vendor Tasks That Need Follow Up', $vendorFollowUps->map(function (VendorService $service) {
            return [
                'title' => $service->service_name,
                'meta' => trim(collect([
                    $service->vendor?->name ? 'Vendor: ' . $service->vendor->name : null,
                    $service->plan_type ? 'Plan: ' . ucwords(str_replace('_', ' ', $service->plan_type)) : null,
                    $service->end_date ? 'End Date: ' . $service->end_date->format('d M Y') : null,
                ])->filter()->implode(' | ')),
                'status' => ucfirst(str_replace('_', ' ', (string) $service->effective_status)),
            ];
        })->all());

        $this->addSummarySection($section, '3. Client Projects / Releases Going Live', $clientGoLives->map(function (Project $project) {
            return [
                'title' => $project->project_name,
                'meta' => trim(collect([
                    $project->customerUser?->name ? 'Client: ' . $project->customerUser->name : null,
                    $project->manager?->name ? 'Manager: ' . $project->manager->name : null,
                    $project->deployment_date ? 'Go-live: ' . $project->deployment_date->format('d M Y') : null,
                ])->filter()->implode(' | ')),
                'status' => ucfirst(str_replace('_', ' ', (string) $project->status)),
            ];
        })->all());

        $this->addSummarySection($section, '4. Pending Renewals', $pendingRenewals->map(function (Service $service) {
            $companyName = $service->company?->company_name ?: ($service->client?->name ?? null);

            return [
                'title' => $service->service_name,
                'meta' => trim(collect([
                    $companyName ? 'Company: ' . $companyName : null,
                    $service->vendor?->name ? 'Vendor: ' . $service->vendor->name : null,
                    $service->end_date ? 'End Date: ' . $service->end_date->format('d M Y') : null,
                ])->filter()->implode(' | ')),
                'status' => ucfirst(str_replace('_', ' ', (string) $service->effective_status)),
            ];
        })->all());

        $fileName = 'daily-operations-summary-' . $today->format('Y-m-d') . '.docx';
        $tempBase = tempnam(sys_get_temp_dir(), 'ops-summary-');

        if ($tempBase === false) {
            abort(500, 'Unable to prepare the summary document.');
        }

        $tempDocx = $tempBase . '.docx';
        @rename($tempBase, $tempDocx);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempDocx);

        return response()->download($tempDocx, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Render a section in the operations summary document.
     *
     * @param array<int, array{title:string,meta:string,status:string}> $items
     */

    private function applyCriticalRenewalWindow(Builder $query, Carbon $today, Carbon $windowEnd): Builder
    {
        return $query->where(function (Builder $query) use ($today, $windowEnd) {
            $query->where('status', 'expired')
                ->orWhere(function (Builder $query) use ($today, $windowEnd) {
                    $query->where('status', 'active')
                        ->whereBetween('end_date', [$today, $windowEnd]);
                });
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

    private function canViewAllRenewals(): bool
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

    private function dashboardClientRenewalQuery(): Builder
    {
        $query = Service::query()->whereNotNull('client_id');

        if ($this->canViewAllRenewals()) {
            return $query;
        }

        $userId = optional(auth()->user())->id;

        if (! $userId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('client_id', $userId);
    }

    private function dashboardVendorRenewalQuery(): Builder
    {
        $query = VendorService::query();

        if ($this->canViewAllRenewals()) {
            return $query;
        }

        $userId = optional(auth()->user())->id;

        if (! $userId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('vendor_id', Service::query()
            ->whereNotNull('vendor_id')
            ->where('client_id', $userId)
            ->select('vendor_id'));
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


