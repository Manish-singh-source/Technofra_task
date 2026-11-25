<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SocialContentPlan;
use App\Models\SocialFestivalEvent;
use App\Models\SocialPost;
use App\Models\SocialPostApproval;
use App\Models\SocialPostAssignment;
use App\Models\SocialPostComment;
use App\Models\SocialPostPlatform;
use App\Models\SocialPostPublishingRecord;
use App\Models\SocialPostStatusHistory;
use App\Models\SocialPostVersion;
use App\Models\SocialPostVersionFile;
use App\Models\User;
use App\Exports\SocialMediaPlanPostsTemplateExport;
use App\Imports\SocialMediaPlanPostsImport;
use App\Services\UnifiedNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SocialMediaController extends Controller
{
    public function dashboard(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) $selectedMonth = now()->format('Y-m');
        $selectedYear = (int) substr($selectedMonth, 0, 4);
        $selectedMonthNum = (int) substr($selectedMonth, 5, 2);

        $canViewContentPlans = $this->canViewContentPlans();
        $canManageContentPlans = $this->canManageContentPlans();
        $selectedClientIds = array_values(array_filter(
            (array) $request->input('client_ids', $request->filled('client_id') ? [$request->input('client_id')] : []),
            fn ($clientId) => filter_var($clientId, FILTER_VALIDATE_INT) !== false && (int) $clientId > 0
        ));

        // Query planned monthly content plans created on /social-media/plans.
        // Staff without plan access must not receive plan data or links from the dashboard.
        $plansQuery = SocialContentPlan::with(['client', 'creator'])->withCount('posts');
        if (! $canViewContentPlans) {
            $plansQuery->whereRaw('1 = 0');
        } elseif ($this->isClient()) {
            $plansQuery->where('client_id', auth()->id())->where('status', '!=', 'draft');
        } elseif ($selectedClientIds) {
            $plansQuery->whereIn('client_id', $selectedClientIds);
        }
        $totalPlansCount = (clone $plansQuery)->count();
        $calendarPlansQuery = clone $plansQuery;

        $posts = $this->applyPostFilters($this->visiblePosts($request), $request)->get();
        $counts = collect(SocialPost::STATUSES)->mapWithKeys(fn ($status) => [$status => $posts->where('status', $status)->count()]);
        // Display planned content from /social-media/plans on "Planned" metric
        $counts['planned'] = $totalPlansCount;
        if ($this->isClient()) {
            $assignedPosts = collect();
        } else {
            $userAssignedQuery = SocialPost::query()->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))->with(['client', 'platforms'])->latest();
            if (! $this->canReviewPosts()) $userAssignedQuery->where('status', '!=', 'internal_review');
            $userAssigned = $userAssignedQuery->get();
            if ($userAssigned->isNotEmpty()) {
                $assignedPosts = $userAssigned;
            } elseif ($this->isManager()) {
                $assignedPosts = SocialPost::query()->with(['client', 'platforms'])->latest()->take(10)->get();
            } else {
                $assignedPosts = $userAssigned;
            }
        }

        $clientApprovals = $this->isClient()
            ? $this->visiblePosts($request)->whereIn('status', ['pending_client_approval', 'client_changes_requested'])->with(['platforms', 'client'])->latest()->get()
            : collect();

        
        if ($request->filled('month')) {
            $plansQuery->where('plan_year', $selectedYear)->where('plan_month', $selectedMonthNum);
            $monthPlans = $plansQuery->latest()->get();
        } else {
            // Default to selected (current) month; if none found, load recent plans so created plans are immediately visible
            $monthPlans = (clone $plansQuery)->where('plan_year', $selectedYear)->where('plan_month', $selectedMonthNum)->latest()->get();
            if ($monthPlans->isEmpty()) {
                $monthPlans = (clone $plansQuery)->latest()->take(10)->get();
            }
        }

        $monthlyPlan = $this->isClient()
            ? $monthPlans->first()
            : ($request->filled('client_id') ? $monthPlans->first() : $monthPlans->first());

        $monthQuery = $this->applyPostFilters($this->visiblePosts($request), $request)
            ->whereYear('planned_at', $selectedYear)->whereMonth('planned_at', $selectedMonthNum);
        $monthPosts = $monthQuery->with(['platforms', 'client'])->orderBy('planned_at')->get();

        // Only list clients present in the viewer's authorized post scope.
        $dashboardClients = $this->isClient() ? collect() : User::query()
            ->whereIn('id', $this->visiblePosts($request)->select('client_id'))
            ->orderBy('first_name')->orderBy('last_name')->get();

        // Calendar events for embedded Post Calendar
        $calendarPostsQuery = $this->applyPostFilters($this->visiblePosts($request), $request)
            ->where(function ($query) { $query->whereNotNull('planned_at')->orWhereNotNull('publishing_scheduled_at'); });
        if ($this->isClient()) {
            // Draft plans are internal work. A client must not see their linked posts
            // in the calendar until the plan moves out of draft.
            $calendarPostsQuery->where(function ($query) {
                $query->whereNull('social_content_plan_id')
                    ->orWhereHas('plan', fn ($planQuery) => $planQuery->where('status', '!=', 'draft'));
            });
        }
        $calendarPosts = $calendarPostsQuery
            ->with(['client', 'platforms'])->orderByRaw('COALESCE(publishing_scheduled_at, planned_at)')->get();
        $postCalendarEvents = $calendarPosts->map(function (SocialPost $post) {
            $statusMeta = match ($post->status) {
                'planned' => ['type' => 'planned', 'label' => 'Planned', 'color' => '#6c757d'],
                'assigned', 'in_progress' => ['type' => 'in_progress', 'label' => 'In Design', 'color' => '#0d6efd'],
                'internal_review', 'internal_changes_requested' => ['type' => 'internal_review', 'label' => 'In Review', 'color' => '#fd7e14'],
                'pending_client_approval', 'client_changes_requested' => ['type' => 'pending_client_approval', 'label' => 'Client Approval', 'color' => '#0dcaf0'],
                'client_approved', 'ready_for_publishing', 'scheduled' => ['type' => 'scheduled', 'label' => 'Scheduled', 'color' => '#6f42c1'],
                'published', 'completed' => ['type' => 'published', 'label' => 'Published', 'color' => '#198754'],
                default => ['type' => 'planned', 'label' => ucwords(str_replace('_', ' ', $post->status)), 'color' => '#6c757d']
            };

            $clientName = $post->client->name ?? '';
            $platforms = $post->platforms->pluck('platform')->join(', ');
            $descParts = array_filter([$clientName, $platforms, $post->creative_brief ?: $post->caption]);
            $description = implode(' • ', $descParts);

            return [
                'id' => (string) $post->id,
                'title' => $post->title,
                'start' => optional($post->publishing_scheduled_at ?: $post->planned_at)->format('Y-m-d\\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => $statusMeta['color'],
                'borderColor' => $statusMeta['color'],
                'textColor' => '#ffffff',
                'url' => route('social-media.posts.show', $post),
                'description' => $description,
                'extendedProps' => [
                    'event_type' => $statusMeta['type'],
                    'event_type_label' => $statusMeta['label'],
                    'status' => $post->status,
                    'status_label' => ucwords(str_replace('_', ' ', $post->status)),
                    'client_name' => $clientName,
                    'platforms' => $platforms,
                    'post_type' => ucfirst($post->post_type),
                    'priority' => ucfirst($post->priority),
                    'date_label' => $post->publishing_scheduled_at ? 'Publishing scheduled' : 'Content planned',
                    'description' => $description,
                    'url' => route('social-media.posts.show', $post),
                ],
            ];
        });

        $planCalendarEvents = $calendarPlansQuery->get()->map(function (SocialContentPlan $plan) {
            $planDate = Carbon::create($plan->plan_year, $plan->plan_month, 1)->startOfDay();
            $statusMeta = match ($plan->status) {
                'client_approved' => ['label' => 'Approved plan', 'color' => '#198754'],
                'pending_client_review' => ['label' => 'Plan awaiting review', 'color' => '#fd7e14'],
                'changes_requested' => ['label' => 'Plan changes requested', 'color' => '#dc3545'],
                default => ['label' => 'Draft plan', 'color' => '#6c757d'],
            };
            $clientName = $plan->client->name ?? '';
            $title = $plan->title ?: ($clientName ? $clientName . ' Content Plan' : 'Monthly Content Plan');
            $planMonth = $planDate->format('Y-m');

            return [
                'id' => 'plan-' . $plan->id,
                'title' => 'Plan: ' . $title,
                'start' => $planDate->toDateString(),
                'allDay' => true,
                'backgroundColor' => $statusMeta['color'],
                'borderColor' => $statusMeta['color'],
                'textColor' => '#ffffff',
                'url' => route('social-media.plans.show', $plan),
                'description' => implode(' • ', array_filter([$clientName, $plan->posts_count . ' linked post(s)', $plan->notes])),
                'extendedProps' => [
                    'event_type' => 'content_plan',
                    'event_type_label' => 'Monthly Plan',
                    'status' => $plan->status,
                    'status_label' => $statusMeta['label'],
                    'client_name' => $clientName,
                    'platforms' => '',
                    'post_type' => 'Monthly plan',
                    'plan_id' => $plan->id,
                ],
            ];
        });

        // The dashboard calendar represents the actual posting schedule. Monthly
        // plans remain available in the plan table below and on /plans.
        $calendarEvents = $postCalendarEvents->values()->all();

        return view('social-media.dashboard', compact('counts', 'assignedPosts', 'clientApprovals', 'monthPosts', 'monthPlans', 'selectedMonth', 'selectedClientIds', 'monthlyPlan', 'dashboardClients', 'calendarEvents', 'canViewContentPlans', 'canManageContentPlans'));
    }

    public function index(Request $request)
    {
        $query = $this->visiblePosts($request)->with(['client', 'platforms', 'assignments.user']);
        foreach (['client_id', 'status', 'priority'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }
        if ($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $request->month)) {
            $query->whereYear('planned_at', substr($request->month, 0, 4))
                ->whereMonth('planned_at', substr($request->month, 5, 2));
        }
        $posts = $query->latest('planned_at')->paginate(20)->withQueryString();
        $clients = $this->isClient() ? collect() : User::query()->whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('first_name')->orderBy('last_name')->get();

        return view('social-media.posts.index', compact('posts', 'clients'));
    }

    public function plans(Request $request)
    {
        if (! $this->canViewContentPlans()) {
            return redirect()->route('social-media.dashboard')
                ->with('error', 'You do not have permission to view content plans.');
        }

        $allowedTabs = ['plans', 'work', 'review', 'approvals', 'publishing', 'festivals'];
        $activeTab = $request->string('tab', 'plans')->toString();
        if (! in_array($activeTab, $allowedTabs, true) || ($this->isClient() && $activeTab !== 'plans')) {
            $activeTab = 'plans';
        }

        $query = SocialContentPlan::withCount('posts')->with('client')->latest('plan_year')->latest('plan_month');
        if ($this->isClient()) $query->where('client_id', auth()->id())->where('status', '!=', 'draft');
        elseif ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $request->month)) {
            $query->where('plan_year', substr($request->month, 0, 4))->where('plan_month', substr($request->month, 5, 2));
        }
        // The planning workspace uses a client-side DataTable, so it needs the
        // complete filtered result set instead of Laravel's separate pagination.
        $plans = $query->get();

        // These are deliberately loaded here, rather than redirecting to separate pages,
        // so Content Planning remains one clear workspace for staff.
        $tabPosts = collect();
        $festivals = collect();
        if (! $this->isClient()) {
            if ($activeTab === 'work') {
                $tabPosts = SocialPost::query()
                    ->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))
                    ->when(! $this->canReviewPosts(), fn ($query) => $query->where('status', '!=', 'internal_review'))
                    ->with(['client', 'platforms'])
                    ->orderByRaw('CASE WHEN internal_deadline_at IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('internal_deadline_at')
                    ->get();
            } elseif ($activeTab === 'review') {
                $tabPosts = $this->visiblePosts($request)
                    ->whereIn('status', ['internal_review', 'internal_changes_requested'])
                    ->with(['client', 'platforms'])
                    ->latest()
                    ->get();
            } elseif ($activeTab === 'approvals') {
                $tabPosts = $this->visiblePosts($request)
                    ->whereIn('status', ['pending_client_approval', 'client_changes_requested'])
                    ->with(['client', 'platforms'])
                    ->latest()
                    ->get();
            } elseif ($activeTab === 'publishing') {
                $tabPosts = $this->visiblePosts($request)
                    ->whereIn('status', ['client_approved', 'ready_for_publishing', 'scheduled', 'published'])
                    ->with(['client', 'platforms'])
                    ->orderByRaw('CASE WHEN publishing_scheduled_at IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('publishing_scheduled_at')
                    ->get();
            } elseif ($activeTab === 'festivals') {
                $festivals = SocialFestivalEvent::with('client')->orderBy('event_date')->get();
            }
        }

        return view('social-media.plans.index', [
            'plans' => $plans,
            'clients' => $this->formData()['clients'],
            'activeTab' => $activeTab,
            'tabPosts' => $tabPosts,
            'festivals' => $festivals,
            'festivalYears' => SocialFestivalEvent::whereNotNull('event_date')->selectRaw('YEAR(event_date) as festival_year')->distinct()->orderByDesc('festival_year')->pluck('festival_year'),
            'festivalImportYears' => collect(range(now()->year, now()->year + 10)),
            'canManageContentPlans' => $this->canManageContentPlans(),
        ]);
    }

    public function storePlan(Request $request)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        $data = $request->validate([
            'client_ids' => 'required_without:client_id|array|min:1',
            'client_ids.*' => 'integer|distinct|exists:users,id',
            'client_id' => 'nullable|required_without:client_ids|exists:users,id',
            'plan_month' => 'required|integer|between:1,12',
            'plan_year' => 'required|integer|between:2020,2100',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:4000',
            'status' => 'nullable|in:draft,pending_client_review,client_approved,changes_requested,not_required'
        ]);
        $clientIds = $data['client_ids'] ?? [$data['client_id']];
        unset($data['client_ids'], $data['client_id']);
        $data['created_by'] = auth()->id();
        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }
        $plans = DB::transaction(function () use ($clientIds, $data) {
            return collect($clientIds)->map(function ($clientId) use ($data) {
                $planData = array_merge($data, ['client_id' => $clientId]);

                return SocialContentPlan::updateOrCreate(
                    ['client_id' => $clientId, 'plan_month' => $data['plan_month'], 'plan_year' => $data['plan_year']],
                    $planData
                );
            });
        });
        foreach ($plans as $plan) {
            if ($plan->status === 'pending_client_review' && $plan->client) {
                app(UnifiedNotificationService::class)->sendToUser($plan->client, 'Monthly content plan ready for review', "Please review your {$plan->title} content plan.", 'social_media', ['url' => route('social-media.plans.index')]);
            }
        }

        return back()->with('success', $plans->count().' monthly content plan(s) saved.');
    }

    /** Display one monthly plan with every content item planned for the client. */
    public function showPlan(SocialContentPlan $plan)
    {
        if (! $this->canViewContentPlans() || ($this->isClient() && ($plan->client_id !== auth()->id() || $plan->status === 'draft'))) {
            return redirect()->route('social-media.dashboard')
                ->with('error', 'You do not have permission to view this content plan.');
        }

        $plan->load([
            'client',
            'posts' => fn ($query) => $query->with(['platforms', 'versions.files', 'assignments', 'festival'])->orderBy('planned_at'),
        ]);

        $plan->posts->each(function (SocialPost $post) {
            $latestVersion = $post->versions->sortByDesc('version_number')->first();
            $post->preview_file = $latestVersion?->files->first(fn ($file) => str_starts_with((string) $file->mime_type, 'image/'));
            $post->can_see_creative = ! $this->isClient() || in_array($post->status, ['pending_client_approval', 'client_changes_requested', 'client_approved', 'ready_for_publishing', 'scheduled', 'published', 'completed', 'rejected'], true);
        });

        $planFestivals = SocialFestivalEvent::query()
            ->where(function ($query) use ($plan) {
                $query->whereNull('client_id')->orWhere('client_id', $plan->client_id);
            })
            ->whereYear('event_date', $plan->plan_year)
            ->whereMonth('event_date', $plan->plan_month)
            ->orderBy('event_date')
            ->get();

        $visiblePostIds = $this->visiblePosts(request())
            ->where('social_content_plan_id', $plan->id)
            ->pluck('id')
            ->all();

        return view('social-media.plans.show', [
            'plan' => $plan,
            'isClient' => $this->isClient(),
            'canManageContentPlans' => $this->canManageContentPlans(),
            'visiblePostIds' => $visiblePostIds,
            'planFestivals' => $planFestivals,
        ]);
    }

    public function downloadPlanPostsTemplate(SocialContentPlan $plan)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        return Excel::download(new SocialMediaPlanPostsTemplateExport(), 'social-media-plan-posts-template.xlsx');
    }

    public function importPlanPosts(Request $request, SocialContentPlan $plan)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $import = new SocialMediaPlanPostsImport($plan);
        Excel::import($import, $request->file('file'));

        $message = "{$import->created} post(s) imported successfully.";
        if ($import->errors) {
            $message .= ' Skipped: '.implode(' ', $import->errors);
        }

        return back()->with($import->created ? 'success' : 'error', $message);
    }

    public function updatePlan(Request $request, SocialContentPlan $plan)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        $data = $request->validate([
            'client_id' => 'required|exists:users,id',
            'plan_month' => 'required|integer|between:1,12',
            'plan_year' => 'required|integer|between:2020,2100',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:4000',
            'status' => 'nullable|in:draft,pending_client_review,client_approved,changes_requested,not_required'
        ]);
        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }
        $wasPendingReview = $plan->status === 'pending_client_review';
        $plan->update($data);
        if ($plan->status === 'pending_client_review' && ! $wasPendingReview && $plan->client) {
            app(UnifiedNotificationService::class)->sendToUser($plan->client, 'Monthly content plan ready for review', "Please review your {$plan->title} content plan.", 'social_media', ['url' => route('social-media.plans.index')]);
        }
        return back()->with('success', 'Monthly content plan updated successfully.');
    }

    public function destroyPlan(SocialContentPlan $plan)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        $deletedPosts = DB::transaction(function () use ($plan) {
            $postCount = SocialPost::where('social_content_plan_id', $plan->id)->delete();
            $plan->delete();

            return $postCount;
        });

        return back()->with('success', "Monthly content plan and {$deletedPosts} related post(s) deleted successfully.");
    }

    /** Clients own the final decision for their monthly plan. */
    public function clientPlanDecision(Request $request, SocialContentPlan $plan)
    {
        abort_unless($this->isClient() && $plan->client_id === auth()->id(), 403);
        abort_unless($plan->status === 'pending_client_review', 422, 'This plan is not awaiting your review.');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'changes_requested', 'not_required'])],
            'feedback' => 'nullable|required_if:decision,changes_requested|string|max:4000',
        ]);

        $status = match ($data['decision']) {
            'approved' => 'client_approved',
            'not_required' => 'not_required',
            default => 'changes_requested',
        };
        $plan->update([
            'status' => $status,
            'client_feedback' => $data['feedback'] ?? null,
            'client_reviewed_at' => now(),
        ]);

        if ($plan->creator) {
            app(UnifiedNotificationService::class)->sendToUser(
                $plan->creator,
                match ($status) {
                    'client_approved' => 'Monthly content plan approved',
                    'not_required' => 'Monthly content plan not required',
                    default => 'Monthly content plan changes requested',
                },
                "{$plan->client->name}'s {$plan->title} plan was ".str_replace('_', ' ', $status).'.',
                'social_media',
                ['url' => route('social-media.plans.index', ['client_id' => $plan->client_id, 'month' => sprintf('%04d-%02d', $plan->plan_year, $plan->plan_month)])]
            );
        }

        return back()->with('success', match ($status) {
            'client_approved' => 'Monthly plan approved.',
            'not_required' => 'The team was informed that you do not need monthly planning.',
            default => 'Your changes request was sent to the team.',
        });
    }

    public function calendar(Request $request)
    {
        $query = $this->applyPostFilters($this->visiblePosts($request), $request)->with(['client', 'platforms']);
        if ($request->filled('month')) {
            $query->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2));
        }
        $posts = $query->where(function ($calendarQuery) {
            $calendarQuery->whereNotNull('planned_at')->orWhereNotNull('publishing_scheduled_at');
        })->orderByRaw('COALESCE(publishing_scheduled_at, planned_at)')->get();

        $calendarEvents = $posts->map(function (SocialPost $post) {
            $statusMeta = match ($post->status) {
                'planned' => ['type' => 'planned', 'label' => 'Planned', 'color' => '#6c757d'],
                'assigned', 'in_progress' => ['type' => 'in_progress', 'label' => 'In Design', 'color' => '#0d6efd'],
                'internal_review', 'internal_changes_requested' => ['type' => 'internal_review', 'label' => 'In Review', 'color' => '#fd7e14'],
                'pending_client_approval', 'client_changes_requested' => ['type' => 'pending_client_approval', 'label' => 'Client Approval', 'color' => '#0dcaf0'],
                'client_approved', 'ready_for_publishing', 'scheduled' => ['type' => 'scheduled', 'label' => 'Scheduled', 'color' => '#6f42c1'],
                'published', 'completed' => ['type' => 'published', 'label' => 'Published', 'color' => '#198754'],
                default => ['type' => 'planned', 'label' => ucwords(str_replace('_', ' ', $post->status)), 'color' => '#6c757d']
            };

            $clientName = $post->client->name ?? '';
            $platforms = $post->platforms->pluck('platform')->join(', ');
            $descParts = array_filter([$clientName, $platforms, $post->creative_brief ?: $post->caption]);
            $description = implode(' • ', $descParts);

            return [
                'id' => (string) $post->id,
                'title' => $post->title,
                'start' => optional($post->publishing_scheduled_at ?: $post->planned_at)->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => $statusMeta['color'],
                'borderColor' => $statusMeta['color'],
                'textColor' => '#ffffff',
                'url' => route('social-media.posts.show', $post),
                'description' => $description,
                'extendedProps' => [
                    'event_type' => $statusMeta['type'],
                    'event_type_label' => $statusMeta['label'],
                    'status' => $post->status,
                    'status_label' => ucwords(str_replace('_', ' ', $post->status)),
                    'client_name' => $clientName,
                    'platforms' => $platforms,
                    'post_type' => ucfirst($post->post_type),
                    'priority' => ucfirst($post->priority),
                    'date_label' => $post->publishing_scheduled_at ? 'Publishing scheduled' : 'Content planned',
                    'description' => $description,
                    'url' => route('social-media.posts.show', $post),
                ],
            ];
        })->values()->all();

        return view('social-media.calendar', compact('posts', 'calendarEvents'));
    }

    public function reviewQueue(Request $request)
    {
        $this->ensureReviewPermission();
        $posts = $this->visiblePosts($request)->whereIn('status', ['internal_review', 'internal_changes_requested'])->with(['client', 'versions'])->latest()->paginate(20);
        return view('social-media.queue', ['posts' => $posts, 'title' => 'Internal Review']);
    }

    public function approvalsQueue(Request $request)
    {
        $posts = $this->visiblePosts($request)->whereIn('status', ['pending_client_approval', 'client_changes_requested'])->with('client')->latest()->paginate(20);
        return view('social-media.queue', ['posts' => $posts, 'title' => $this->isClient() ? 'My Approvals' : 'Client Approvals']);
    }

    public function publishingTracker(Request $request)
    {
        $this->ensureStaffPermission('manage_social_publishing');
        $query = $this->visiblePosts($request)->whereIn('status', ['client_approved', 'ready_for_publishing', 'scheduled', 'published'])->with(['client', 'platforms', 'publishingRecords']);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('status') && in_array($request->status, ['client_approved', 'ready_for_publishing', 'scheduled', 'published'], true)) $query->where('status', $request->status);
        if ($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $request->month)) {
            $query->whereYear('publishing_scheduled_at', substr($request->month, 0, 4))->whereMonth('publishing_scheduled_at', substr($request->month, 5, 2));
        }
        $posts = $query->orderByRaw('CASE WHEN publishing_scheduled_at IS NULL THEN 1 ELSE 0 END')->orderBy('publishing_scheduled_at')->paginate(20)->withQueryString();
        return view('social-media.publishing', ['posts' => $posts, 'clients' => $this->formData()['clients']]);
    }

    public function festivals()
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        return view('social-media.festivals', ['events' => SocialFestivalEvent::with('client')->orderBy('event_date')->paginate(25), 'clients' => $this->formData()['clients']]);
    }

    public function storeFestival(Request $request)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        SocialFestivalEvent::create($this->validatedFestival($request));
        return back()->with('success', 'Festival/event added to the master library.');
    }

    public function updateFestival(Request $request, SocialFestivalEvent $festival)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        $festival->update($this->validatedFestival($request));
        return back()->with('success', 'Festival/event updated successfully.');
    }

    public function destroyFestival(SocialFestivalEvent $festival)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        $festival->delete();
        return back()->with('success', 'Festival/event removed.');
    }

    public function importFestivals(Request $request)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        $data = $request->validate(['year' => 'required|integer|between:2000,2100']);
        $year = (int) $data['year'];
        $count = app(\App\Services\FestivalCalendarService::class)->seedYear($year);
        return back()->with('success', "{$count} India and international festivals for {$year} are ready. Existing matching records were updated, not duplicated.");
    }

    public function bulkDestroyFestivals(Request $request)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        $data = $request->validate(['festival_ids' => 'required|array|min:1', 'festival_ids.*' => 'integer|exists:social_festival_events,id']);
        $deleted = SocialFestivalEvent::whereIn('id', $data['festival_ids'])->delete();
        return back()->with('success', "{$deleted} festival/event(s) removed.");
    }

    public function destroyFestivalsByYear(Request $request)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        $data = $request->validate(['year' => 'required|integer|between:2000,2100']);
        $deleted = SocialFestivalEvent::whereYear('event_date', $data['year'])->delete();
        return back()->with('success', "{$deleted} festival/event(s) from {$data['year']} removed.");
    }

    public function reports(Request $request)
    {
        $this->ensureStaffPermission('view_social_media_reports');
        $query = $this->visiblePosts($request)->with('client');
        if ($request->filled('month')) $query->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2));
        $posts = $query->get();
        $summary = ['planned' => $posts->count(), 'approved' => $posts->whereIn('status', ['client_approved','ready_for_publishing','scheduled','published','completed'])->count(), 'published' => $posts->whereIn('status', ['published','completed'])->count(), 'pending' => $posts->where('status', 'pending_client_approval')->count(), 'delayed' => $posts->filter(fn ($p) => $p->status !== 'published' && $p->publishing_scheduled_at && $p->publishing_scheduled_at->isPast())->count(), 'rejected' => $posts->where('status', 'rejected')->count(), 'revisions' => $posts->sum(fn ($p) => $p->versions()->count())];
        return view('social-media.reports', compact('posts', 'summary'));
    }

    public function create()
    {
        $this->ensureStaffPermission('create_social_media_posts');
        $festival = null;
        if (request()->filled('festival_id')) {
            $festival = SocialFestivalEvent::findOrFail(request('festival_id'));
            if (request()->filled('plan_id')) {
                $plan = SocialContentPlan::findOrFail(request('plan_id'));
                abort_unless((int) $plan->client_id === (int) request('client_id'), 422, 'The selected festival plan client is invalid.');
                abort_unless($festival->event_date && (int) $festival->event_date->year === (int) $plan->plan_year && (int) $festival->event_date->month === (int) $plan->plan_month, 422, 'The selected festival is outside the monthly plan period.');
            }
        }
        return view('social-media.posts.form', array_merge($this->formData(), compact('festival')));
    }

    /** Staff-only work inbox: this is the entry point from a new assignment notification. */
    public function myAssigned(Request $request)
    {
        abort_unless(! $this->isClient(), 403);
        $query = SocialPost::query()->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))->with(['client', 'assignments.user']);
        if (! $this->canReviewPosts()) $query->where('status', '!=', 'internal_review');
        if ($request->filled('status')) $query->where('status', $request->status);
        $posts = $query->orderByRaw('CASE WHEN internal_deadline_at IS NULL THEN 1 ELSE 0 END')->orderBy('internal_deadline_at')->paginate(20)->withQueryString();
        return view('social-media.my-assigned', compact('posts'));
    }

    public function startWork(SocialPost $post)
    {
        $this->authorizePost($post);
        abort_unless($post->assignments()->where('user_id', auth()->id())->exists(), 403);
        abort_unless(in_array($post->status, ['planned', 'assigned', 'internal_changes_requested', 'client_changes_requested'], true), 422, 'This post cannot be started from its current status.');
        $this->transition($post, 'in_progress', 'Staff started work.');
        return redirect()->route('social-media.posts.show', $post)->with('success', 'Work started. You can now upload the creative version.');
    }

    public function store(Request $request)
    {
        $this->ensureStaffPermission('create_social_media_posts');
        $request->merge(['platforms' => $this->normalizePlatforms($request->input('platforms'))]);
        $data = $this->validatedPost($request);
        $this->validatePlanClient($data);
        $data['created_by'] = auth()->id();
        // A post must not appear as assigned until at least one staff member is actually selected.
        $assignments = $this->normalizeAssignments($data['assignments'] ?? []);
        $hasAssignments = ! empty($assignments);
        $data['status'] = $request->boolean('save_as_draft') || ! $hasAssignments ? 'planned' : 'assigned';

        DB::transaction(function () use ($data, $request, &$post) {
            $post = SocialPost::create($data);
            $this->syncPlatforms($post, $request->input('platforms', []));
            $this->syncAssignments($post, $data['assignments'] ?? []);
            $this->recordStatus($post, null, $post->status, 'Post created');
        });

        return redirect()->route('social-media.posts.show', $post)->with('success', 'Social media post created.');
    }

    public function show(SocialPost $post)
    {
        $this->authorizePost($post);
        $post->load(['client', 'project', 'platforms', 'assignments.user', 'versions.files', 'versions.creator', 'approvals.decider', 'histories.user', 'publishingRecords', 'comments.user']);
        $comments = $post->comments->where('is_internal', false);
        if (! $this->isClient()) $comments = $post->comments;
        $canSeeCreative = ! $this->isClient() || in_array($post->status, ['pending_client_approval', 'client_changes_requested', 'client_approved', 'ready_for_publishing', 'scheduled', 'published', 'completed', 'rejected'], true);
        $canManageReview = $this->canReviewPosts();
        return view('social-media.posts.show', compact('post', 'comments', 'canSeeCreative', 'canManageReview'));
    }

    public function edit(SocialPost $post)
    {
        $this->authorizePost($post);
        $this->ensureStaffPermission('edit_social_media_posts');
        return view('social-media.posts.form', array_merge($this->formData(), compact('post')));
    }

    public function update(Request $request, SocialPost $post)
    {
        $this->authorizePost($post);
        $this->ensureStaffPermission('edit_social_media_posts');
        $oldStatus = $post->status;
        $request->merge(['platforms' => $this->normalizePlatforms($request->input('platforms'))]);
        $data = $this->validatedPost($request);
        $this->validatePlanClient($data);
        $post->update($data);
        $this->syncPlatforms($post, $request->input('platforms', []));
        $this->syncAssignments($post, $data['assignments'] ?? []);
        if (in_array($oldStatus, ['client_approved', 'ready_for_publishing', 'scheduled'], true)) {
            $this->transition($post, 'pending_client_approval', 'Publishing or content details updated; client reapproval required.');
        }
        return redirect()->route('social-media.posts.show', $post)->with('success', 'Post updated.');
    }

    public function destroy(SocialPost $post)
    {
        $this->authorizePost($post);
        $this->ensureStaffPermission('delete_social_media_posts');
        $title = $post->title;
        $post->delete();

        return back()->with('success', "{$title} was deleted successfully.");
    }

    public function submitVersion(Request $request, SocialPost $post)
    {
        $this->authorizePost($post);
        abort_unless($this->canWorkOn($post), 403);
        $request->validate(['note' => 'nullable|string|max:2000', 'files' => 'required|array|min:1|max:12', 'files.*' => 'file|max:51200|mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm,pdf']);
        $version = DB::transaction(function () use ($request, $post) {
            $next = ((int) $post->versions()->max('version_number')) + 1;
            $version = $post->versions()->create([
                'version_number' => $next, 'caption_snapshot' => $post->caption, 'hashtags_snapshot' => $post->hashtags,
                'publishing_snapshot' => ['planned_at' => optional($post->planned_at)->toIso8601String(), 'platforms' => $post->platforms->pluck('platform')->values()],
                'note' => $request->input('note'), 'created_by' => auth()->id(), 'submitted_for_review_at' => now(),
            ]);
            foreach ($request->file('files') as $file) {
                $path = $file->store('social-media/'.$post->id.'/versions/'.$next, 'local');
                $version->files()->create(['original_name' => $file->getClientOriginalName(), 'file_path' => $path, 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize()]);
            }
            $this->transition($post, 'internal_review', "Version V{$next} submitted for internal review.");
            return $version;
        });
        $this->notifyReviewers('Creative ready for review', "{$post->title} V{$version->version_number} needs internal review.", $post->id);
        return redirect()->route('social-media.plans.index')->with('success', 'Version submitted for internal review.');
    }

    public function internalReview(Request $request, SocialPost $post)
    {
        $this->authorizePost($post); $this->ensureReviewPermission();
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_requested'])], 'comment' => 'nullable|string|max:3000']);
        $version = $post->versions()->latest('version_number')->firstOrFail();
        SocialPostApproval::create(['social_post_id' => $post->id, 'social_post_version_id' => $version->id, 'decided_by' => auth()->id(), 'stage' => 'internal', 'decision' => $data['decision'], 'comment' => $data['comment'] ?? null]);
        $next = $data['decision'] === 'approved' ? 'pending_client_approval' : 'internal_changes_requested';
        $this->transition($post, $next, $data['comment'] ?? 'Internal review completed.');
        if ($next === 'pending_client_approval') $this->notifyClient($post, 'Post awaiting your approval', "{$post->title} is ready for your review.");
        else $this->notifyWorkers($post, 'Internal changes requested', "Changes were requested for {$post->title}.");
        return back()->with('success', 'Internal review saved.');
    }

    public function clientDecision(Request $request, SocialPost $post)
    {
        $this->authorizePost($post); abort_unless($this->isClient() && $post->client_id === auth()->id(), 403);
        abort_unless($post->status === 'pending_client_approval', 422, 'This post is not awaiting client approval.');
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_requested', 'rejected'])], 'comment' => 'nullable|required_if:decision,changes_requested,rejected|string|max:3000']);
        $version = $post->versions()->latest('version_number')->firstOrFail();
        SocialPostApproval::create(['social_post_id' => $post->id, 'social_post_version_id' => $version->id, 'decided_by' => auth()->id(), 'stage' => 'client', 'decision' => $data['decision'], 'comment' => $data['comment'] ?? null]);
        $next = ['approved' => 'client_approved', 'changes_requested' => 'client_changes_requested', 'rejected' => 'rejected'][$data['decision']];
        $this->transition($post, $next, $data['comment'] ?? 'Client decision recorded.');
        $this->notifyWorkers($post, 'Client decision received', "Client marked {$post->title} as ".str_replace('_', ' ', $data['decision']).'.');
        return back()->with('success', 'Your decision has been recorded.');
    }

    public function comment(Request $request, SocialPost $post)
    {
        $this->authorizePost($post);
        $data = $request->validate(['comment' => 'required|string|max:3000', 'is_internal' => 'nullable|boolean']);
        $internal = ! $this->isClient() && $request->boolean('is_internal');
        SocialPostComment::create(['social_post_id' => $post->id, 'user_id' => auth()->id(), 'comment' => $data['comment'], 'is_internal' => $internal]);
        if (! $internal) $this->notifyWorkers($post, 'New post comment', "A new comment was added to {$post->title}.");
        return back()->with('success', 'Comment added.');
    }

    public function schedule(Request $request, SocialPost $post)
    {
        $this->authorizePost($post); $this->ensureStaffPermission('manage_social_publishing');
        abort_unless(in_array($post->status, ['client_approved', 'ready_for_publishing', 'scheduled'], true), 422);
        $data = $request->validate(['scheduled_at' => 'required|date', 'publisher_id' => 'nullable|exists:users,id']);
        SocialPostPublishingRecord::create(['social_post_id' => $post->id, 'published_by' => $data['publisher_id'] ?? auth()->id(), 'status' => 'scheduled', 'scheduled_at' => $data['scheduled_at']]);
        $post->update(['publishing_scheduled_at' => $data['scheduled_at']]); $this->transition($post, 'scheduled', 'Publishing scheduled.');
        return back()->with('success', 'Post scheduled.');
    }

    public function publish(Request $request, SocialPost $post)
    {
        $this->authorizePost($post); $this->ensureStaffPermission('manage_social_publishing');
        $data = $request->validate(['published_url' => 'nullable|url|max:2048', 'actual_published_at' => 'required|date', 'proof' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,webp,pdf']);
        $proof = $request->file('proof')?->store('social-media/'.$post->id.'/publishing', 'local');
        SocialPostPublishingRecord::create(['social_post_id' => $post->id, 'published_by' => auth()->id(), 'status' => 'published', 'actual_published_at' => $data['actual_published_at'], 'published_url' => $data['published_url'] ?? null, 'proof_path' => $proof]);
        $this->transition($post, 'published', 'Publishing manually confirmed.');
        return back()->with('success', 'Publishing confirmation saved.');
    }

    public function downloadVersionFile(SocialPost $post, SocialPostVersionFile $file)
    {
        $this->authorizePost($post); abort_unless($file->socialPostVersion?->social_post_id === $post->id, 404);
        return Storage::disk('local')->download($file->file_path, $file->original_name);
    }

    public function previewVersionFile(SocialPost $post, SocialPostVersionFile $file)
    {
        $this->authorizePost($post);
        abort_unless($file->socialPostVersion?->social_post_id === $post->id, 404);
        abort_unless(str_starts_with((string) $file->mime_type, 'image/') || str_starts_with((string) $file->mime_type, 'video/'), 404);
        abort_unless(Storage::disk('local')->exists($file->file_path), 404);
        return response()->file(Storage::disk('local')->path($file->file_path), [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
        ]);
    }

    private function visiblePosts(Request $request) {
        $query = SocialPost::query();
        if ($this->isClient()) return $query->where('client_id', auth()->id());
        // Regular staff work only from their assigned queue; managers and users with
        // the explicit view permission can see the complete social-media workspace.
        if ($this->isManager()) {
            return $query;
        }

        if (! $this->canReviewPosts()) {
            $query->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))
                ->where('status', '!=', 'internal_review');
        } else {
            $query->where(function ($q) {
                $q->where('status', 'internal_review')
                    ->orWhere(function ($assigned) {
                        $assigned->where('status', '!=', 'internal_review')
                            ->whereHas('assignments', fn ($assignment) => $assignment->where('user_id', auth()->id()));
                    });
            });
        }
        return $query;
    }
    /** Apply UI filters only after the role-specific visibility scope has been applied. */
    private function applyPostFilters($query, Request $request) {
        if (! $this->isClient()) {
            $clientIds = array_values(array_filter(
                (array) $request->input('client_ids', []),
                fn ($clientId) => filter_var($clientId, FILTER_VALIDATE_INT) !== false && (int) $clientId > 0
            ));
            if ($clientIds) $query->whereIn('client_id', $clientIds);
            elseif ($request->filled('client_id')) $query->where('client_id', $request->input('client_id'));
        }
        if ($request->filled('status') && in_array($request->input('status'), SocialPost::STATUSES, true)) $query->where('status', $request->input('status'));
        return $query;
    }
    private function validatedFestival(Request $request): array {
        return $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'nullable|date',
            'category' => 'nullable|string|max:100',
            'country_region' => 'nullable|string|max:120',
            'recurrence_type' => ['required', Rule::in(['none', 'yearly'])],
            'client_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string|max:4000',
            'reference_url' => 'nullable|url|max:2048',
        ]);
    }
    private function isClient(): bool { return in_array(strtolower((string) auth()->user()?->getRawOriginal('role')), ['client', 'customer'], true); }
    private function isPrivileged(): bool { return ! $this->isClient() && (auth()->user()->can('view_social_media_posts') || $this->isManager()); }
    private function canViewContentPlans(): bool { return (bool) auth()->user(); }
    private function canManageContentPlans(): bool { return ! $this->isClient() && (auth()->user()->can('manage_social_content_plans') || $this->isManager()); }
    private function isManager(): bool { return auth()->user()->hasRole(['super_admin', 'super-admin', 'superadmin', 'super_admin2', 'admin', 'Admin', 'Super Admin']); }
    private function authorizePost(SocialPost $post): void { abort_unless($this->isManager() || ($this->isClient() && $post->client_id === auth()->id()) || $this->canWorkOn($post) || ($post->status === 'internal_review' && $this->canReviewPosts()), 403); }
    private function canWorkOn(SocialPost $post): bool { return $post->assignments()->where('user_id', auth()->id())->exists() || $this->isManager(); }
    private function ensureStaffPermission(string $permission): void { abort_unless(! $this->isClient() && (auth()->user()->can($permission) || $this->isManager()), 403); }
    private function ensureReviewPermission(): void { abort_unless($this->canReviewPosts(), 403); }
    private function canReviewPosts(): bool { return ! $this->isClient() && auth()->user()->can('review_social_media_posts'); }
    private function formData(): array { return ['clients' => User::query()->whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('first_name')->orderBy('last_name')->get(), 'projects' => Project::orderBy('project_name')->get(), 'staff' => User::query()->whereRaw('LOWER(role) IN (?, ?)', ['staff', 'manager'])->orderBy('first_name')->orderBy('last_name')->get(), 'statuses' => SocialPost::STATUSES]; }
    private function validatedPost(Request $request): array { return $request->validate(['social_content_plan_id' => 'nullable|exists:social_content_plans,id', 'social_festival_event_id' => 'nullable|exists:social_festival_events,id', 'client_id' => 'required|exists:users,id', 'project_id' => 'nullable|exists:projects,id', 'title' => 'required|string|max:255', 'content_category' => 'nullable|string|max:100', 'post_type' => ['required', Rule::in(['image', 'carousel', 'reel', 'video', 'story', 'other'])], 'creative_brief' => 'nullable|string', 'caption' => 'nullable|string', 'hashtags' => 'nullable|string', 'reference_links' => 'nullable|string', 'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])], 'planned_at' => 'nullable|date', 'internal_deadline_at' => 'nullable|date', 'client_approval_deadline_at' => 'nullable|date', 'platforms' => 'nullable|array', 'platforms.*' => 'string|max:60', 'assignments' => 'nullable|array', 'assignments.*' => 'nullable|array', 'assignments.*.*' => 'nullable|exists:users,id']); }
    private function validatePlanClient(array $data): void {
        if (!empty($data['social_content_plan_id']) && !SocialContentPlan::whereKey($data['social_content_plan_id'])->where('client_id', $data['client_id'])->exists()) abort(422, 'The selected monthly plan does not belong to the selected client.');
        if (!empty($data['social_festival_event_id'])) {
            $festival = SocialFestivalEvent::findOrFail($data['social_festival_event_id']);
            if ($festival->client_id && (int) $festival->client_id !== (int) $data['client_id']) abort(422, 'The selected festival does not belong to the selected client.');
            if (!empty($data['social_content_plan_id'])) {
                $plan = SocialContentPlan::findOrFail($data['social_content_plan_id']);
                abort_unless($festival->event_date && (int) $festival->event_date->year === (int) $plan->plan_year && (int) $festival->event_date->month === (int) $plan->plan_month, 422, 'The selected festival is outside the monthly plan period.');
            }
        }
    }
    private function normalizePlatforms(mixed $platforms): array {
        if (is_string($platforms)) $platforms = [$platforms];
        return collect($platforms ?: [])->flatten()->filter(fn ($platform) => is_scalar($platform) && trim((string) $platform) !== '')->map(fn ($platform) => trim((string) $platform))->values()->all();
    }
    private function syncPlatforms(SocialPost $post, array $platforms): void { $platforms = collect($platforms)->flatMap(fn ($value) => preg_split('/[,\n]+/', (string) $value))->map(fn ($value) => trim($value))->filter()->unique()->values()->all(); $post->platforms()->whereNotIn('platform', $platforms)->delete(); foreach ($platforms as $platform) SocialPostPlatform::firstOrCreate(['social_post_id' => $post->id, 'platform' => $platform]); }
    private function normalizeAssignments(array $assignments): array
    {
        $roles = ['designer', 'writer', 'reviewer', 'publisher'];
        $normalized = [];

        foreach ($assignments as $key => $value) {
            if (is_string($key) && in_array($key, $roles, true)) {
                foreach ((array) $value as $userId) {
                    if (! empty($userId)) $normalized[] = ['user_id' => $userId, 'role' => $key];
                }
                continue;
            }

            // Keep accepting the previous one-assignee-per-role payload shape.
            if (is_array($value) && ! empty($value['user_id']) && ! empty($value['role'])) {
                $normalized[] = ['user_id' => $value['user_id'], 'role' => $value['role']];
            }
        }

        return collect($normalized)->unique(fn ($assignment) => $assignment['user_id'].'|'.$assignment['role'])->values()->all();
    }
    private function syncAssignments(SocialPost $post, array $assignments): void { $oldAssignments = $post->assignments()->get()->map(fn ($a) => $a->user_id.'|'.$a->assignment_role)->all(); $post->assignments()->delete(); foreach ($this->normalizeAssignments($assignments) as $assignment) { $key = $assignment['user_id'].'|'.$assignment['role']; SocialPostAssignment::create(['social_post_id' => $post->id, 'user_id' => $assignment['user_id'], 'assignment_role' => $assignment['role']]); $user = User::find($assignment['user_id']); if ($user && !in_array($key, $oldAssignments, true)) app(UnifiedNotificationService::class)->sendToUser($user, 'New social media assignment', "You are assigned as {$assignment['role']} for {$post->title}. Open My Assigned Posts to start work.", 'social_media', ['post_id' => $post->id, 'url' => route('social-media.my-assigned')]); } }
    private function transition(SocialPost $post, string $to, string $comment): void { $from = $post->status; $post->update(['status' => $to]); $this->recordStatus($post, $from, $to, $comment); }
    private function recordStatus(SocialPost $post, ?string $from, string $to, string $comment): void { SocialPostStatusHistory::create(['social_post_id' => $post->id, 'user_id' => auth()->id(), 'from_status' => $from, 'to_status' => $to, 'action' => str_replace('_', ' ', $to), 'comment' => $comment]); }
    private function notifyClient(SocialPost $post, string $title, string $body): void { if ($post->client) app(UnifiedNotificationService::class)->sendToUser($post->client, $title, $body, 'social_media', ['post_id' => $post->id]); }
    private function notifyRole(SocialPost $post, string $role, string $title, string $body): void { $post->assignments()->where('assignment_role', $role)->with('user')->get()->each(fn ($a) => app(UnifiedNotificationService::class)->sendToUser($a->user, $title, $body, 'social_media', ['post_id' => $post->id])); }
    private function notifyReviewers(string $title, string $body, int $postId): void { User::query()->get()->filter(fn ($user) => ! in_array(strtolower((string) $user->getRawOriginal('role')), ['client', 'customer'], true) && $user->can('review_social_media_posts'))->each(fn ($user) => app(UnifiedNotificationService::class)->sendToUser($user, $title, $body, 'social_media', ['post_id' => $postId])); }
    private function notifyWorkers(SocialPost $post, string $title, string $body): void { $post->assignments()->with('user')->get()->unique('user_id')->each(fn ($a) => app(UnifiedNotificationService::class)->sendToUser($a->user, $title, $body, 'social_media', ['post_id' => $post->id])); }
}
