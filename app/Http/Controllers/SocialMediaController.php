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
use App\Services\UnifiedNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SocialMediaController extends Controller
{
    public function dashboard(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $selectedYear = (int) substr($selectedMonth, 0, 4);
        $selectedMonthNum = (int) substr($selectedMonth, 5, 2);

        // Query planned monthly content plans created on /social-media/plans
        $plansQuery = SocialContentPlan::with(['client', 'creator'])->withCount('posts');
        if ($this->isClient()) {
            $plansQuery->where('client_id', auth()->id());
        } elseif ($request->filled('client_id')) {
            $plansQuery->where('client_id', $request->client_id);
        }
        $totalPlansCount = (clone $plansQuery)->count();

        $posts = $this->visiblePosts($request)->get();
        $counts = collect(SocialPost::STATUSES)->mapWithKeys(fn ($status) => [$status => $posts->where('status', $status)->count()]);
        // Display planned content from /social-media/plans on "Planned" metric
        $counts['planned'] = $totalPlansCount;
        if ($this->isClient()) {
            $assignedPosts = collect();
        } else {
            $userAssigned = SocialPost::query()->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))->with(['client', 'platforms'])->latest()->get();
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

        $monthQuery = $this->visiblePosts($request)->whereYear('planned_at', $selectedYear)->whereMonth('planned_at', $selectedMonthNum);
        if (! $this->isClient() && $request->filled('client_id')) $monthQuery->where('client_id', $request->client_id);
        $monthPosts = $monthQuery->with(['platforms', 'client'])->orderBy('planned_at')->get();

        $dashboardClients = $this->isClient() ? collect() : $this->formData()['clients'];

        // Calendar events for embedded Post Calendar
        $calendarPosts = $this->visiblePosts($request)->whereNotNull('planned_at')->with(['client', 'platforms'])->orderBy('planned_at')->get();
        $calendarEvents = $calendarPosts->map(function (SocialPost $post) {
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
                'start' => optional($post->planned_at)->format('Y-m-d\\TH:i:s'),
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
                    'description' => $description,
                    'url' => route('social-media.posts.show', $post),
                ],
            ];
        })->values()->all();

        return view('social-media.dashboard', compact('counts', 'assignedPosts', 'clientApprovals', 'monthPosts', 'monthPlans', 'selectedMonth', 'monthlyPlan', 'dashboardClients', 'calendarEvents'));
    }

    public function index(Request $request)
    {
        $query = $this->visiblePosts($request)->with(['client', 'platforms', 'assignments.user']);
        foreach (['client_id', 'status', 'priority'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }
        $posts = $query->latest('planned_at')->paginate(20)->withQueryString();
        $clients = $this->isClient() ? collect() : User::query()->whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('first_name')->orderBy('last_name')->get();

        return view('social-media.posts.index', compact('posts', 'clients'));
    }

    public function plans(Request $request)
    {
        $query = SocialContentPlan::withCount('posts')->with('client')->latest('plan_year')->latest('plan_month');
        if ($this->isClient()) $query->where('client_id', auth()->id());
        $plans = $query->paginate(15);
        return view('social-media.plans.index', ['plans' => $plans, 'clients' => $this->formData()['clients']]);
    }

    public function storePlan(Request $request)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        $data = $request->validate([
            'client_id' => 'required|exists:users,id',
            'plan_month' => 'required|integer|between:1,12',
            'plan_year' => 'required|integer|between:2020,2100',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:4000',
            'status' => 'nullable|in:draft,pending_client_review,client_approved,changes_requested'
        ]);
        $data['created_by'] = auth()->id();
        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }
        SocialContentPlan::updateOrCreate(['client_id' => $data['client_id'], 'plan_month' => $data['plan_month'], 'plan_year' => $data['plan_year']], $data);
        return back()->with('success', 'Monthly content plan saved.');
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
            'status' => 'nullable|in:draft,pending_client_review,client_approved,changes_requested'
        ]);
        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }
        $plan->update($data);
        return back()->with('success', 'Monthly content plan updated successfully.');
    }

    public function destroyPlan(SocialContentPlan $plan)
    {
        $this->ensureStaffPermission('manage_social_content_plans');
        $plan->delete();
        return back()->with('success', 'Monthly content plan deleted successfully.');
    }

    public function calendar(Request $request)
    {
        $query = $this->visiblePosts($request)->with(['client', 'platforms']);
        if ($request->filled('month')) {
            $query->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2));
        }
        $posts = $query->whereNotNull('planned_at')->orderBy('planned_at')->get();

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
                'start' => optional($post->planned_at)->format('Y-m-d\TH:i:s'),
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
                    'description' => $description,
                    'url' => route('social-media.posts.show', $post),
                ],
            ];
        })->values()->all();

        return view('social-media.calendar', compact('posts', 'calendarEvents'));
    }

    public function reviewQueue(Request $request)
    {
        $this->ensureStaffPermission('review_social_media_posts');
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
        $posts = $this->visiblePosts($request)->whereIn('status', ['client_approved', 'ready_for_publishing', 'scheduled', 'published'])->with(['client', 'publishingRecords'])->latest('publishing_scheduled_at')->paginate(20);
        return view('social-media.publishing', compact('posts'));
    }

    public function festivals()
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        return view('social-media.festivals', ['events' => SocialFestivalEvent::with('client')->orderBy('event_date')->paginate(25), 'clients' => $this->formData()['clients']]);
    }

    public function storeFestival(Request $request)
    {
        $this->ensureStaffPermission('manage_social_festival_events');
        SocialFestivalEvent::create($request->validate(['name' => 'required|string|max:255', 'event_date' => 'nullable|date', 'category' => 'nullable|string|max:100', 'country_region' => 'nullable|string|max:120', 'recurrence_type' => ['required', Rule::in(['none', 'yearly'])], 'client_id' => 'nullable|exists:users,id', 'description' => 'nullable|string|max:4000', 'reference_url' => 'nullable|url|max:2048']));
        return back()->with('success', 'Festival/event added to the master library.');
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
        return view('social-media.posts.form', $this->formData());
    }

    /** Staff-only work inbox: this is the entry point from a new assignment notification. */
    public function myAssigned(Request $request)
    {
        abort_unless(! $this->isClient(), 403);
        $query = SocialPost::query()->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))->with(['client', 'assignments' => fn ($q) => $q->where('user_id', auth()->id())]);
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
        $data = $this->validatedPost($request);
        $data['created_by'] = auth()->id();
        // A post must not appear as assigned until at least one staff member is actually selected.
        $hasAssignments = collect($request->input('assignments', []))->contains(fn ($assignment) => ! empty($assignment['user_id']));
        $data['status'] = $request->boolean('save_as_draft') || ! $hasAssignments ? 'planned' : 'assigned';

        DB::transaction(function () use ($data, $request, &$post) {
            $post = SocialPost::create($data);
            $this->syncPlatforms($post, $request->input('platforms', []));
            $this->syncAssignments($post, $request->input('assignments', []));
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
        return view('social-media.posts.show', compact('post', 'comments', 'canSeeCreative'));
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
        $post->update($this->validatedPost($request));
        $this->syncPlatforms($post, $request->input('platforms', []));
        $this->syncAssignments($post, $request->input('assignments', []));
        if (in_array($oldStatus, ['client_approved', 'ready_for_publishing', 'scheduled'], true)) {
            $this->transition($post, 'pending_client_approval', 'Publishing or content details updated; client reapproval required.');
        }
        return redirect()->route('social-media.posts.show', $post)->with('success', 'Post updated.');
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
        $this->notifyRole($post, 'reviewer', 'Creative ready for review', "{$post->title} V{$version->version_number} needs internal review.");
        return back()->with('success', 'Version submitted for internal review.');
    }

    public function internalReview(Request $request, SocialPost $post)
    {
        $this->authorizePost($post); $this->ensureStaffPermission('review_social_media_posts');
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
        if (! $this->isPrivileged()) $query->whereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()));
        return $query;
    }
    private function isClient(): bool { return in_array(strtolower((string) auth()->user()?->getRawOriginal('role')), ['client', 'customer'], true); }
    private function isPrivileged(): bool { return ! $this->isClient() && (auth()->user()->can('view_social_media_posts') || $this->isManager()); }
    private function isManager(): bool { return auth()->user()->hasRole(['super_admin', 'super-admin', 'superadmin', 'super_admin2', 'admin', 'Admin', 'Super Admin']); }
    private function authorizePost(SocialPost $post): void { abort_unless($this->isPrivileged() || ($this->isClient() && $post->client_id === auth()->id()) || $this->canWorkOn($post), 403); }
    private function canWorkOn(SocialPost $post): bool { return $post->assignments()->where('user_id', auth()->id())->exists() || $this->isPrivileged(); }
    private function ensureStaffPermission(string $permission): void { abort_unless(! $this->isClient() && (auth()->user()->can($permission) || $this->isManager()), 403); }
    private function formData(): array { return ['clients' => User::query()->whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('first_name')->orderBy('last_name')->get(), 'projects' => Project::orderBy('project_name')->get(), 'staff' => User::query()->whereRaw('LOWER(role) IN (?, ?)', ['staff', 'manager'])->orderBy('first_name')->orderBy('last_name')->get(), 'statuses' => SocialPost::STATUSES]; }
    private function validatedPost(Request $request): array { return $request->validate(['social_content_plan_id' => 'nullable|exists:social_content_plans,id', 'client_id' => 'required|exists:users,id', 'project_id' => 'nullable|exists:projects,id', 'title' => 'required|string|max:255', 'content_category' => 'nullable|string|max:100', 'post_type' => ['required', Rule::in(['image', 'carousel', 'reel', 'video', 'story', 'other'])], 'creative_brief' => 'nullable|string', 'caption' => 'nullable|string', 'hashtags' => 'nullable|string', 'reference_links' => 'nullable|string', 'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])], 'planned_at' => 'nullable|date', 'internal_deadline_at' => 'nullable|date', 'client_approval_deadline_at' => 'nullable|date', 'platforms' => 'nullable|array', 'platforms.*' => 'string|max:60', 'assignments' => 'nullable|array', 'assignments.*.user_id' => 'nullable|exists:users,id', 'assignments.*.role' => ['nullable', Rule::in(['designer', 'writer', 'reviewer', 'publisher'])]]); }
    private function syncPlatforms(SocialPost $post, array $platforms): void { $platforms = collect($platforms)->flatMap(fn ($value) => preg_split('/[,\n]+/', (string) $value))->map(fn ($value) => trim($value))->filter()->unique()->values()->all(); $post->platforms()->whereNotIn('platform', $platforms)->delete(); foreach ($platforms as $platform) SocialPostPlatform::firstOrCreate(['social_post_id' => $post->id, 'platform' => $platform]); }
    private function syncAssignments(SocialPost $post, array $assignments): void { $oldAssignments = $post->assignments()->get()->map(fn ($a) => $a->user_id.'|'.$a->assignment_role)->all(); $post->assignments()->delete(); foreach ($assignments as $assignment) if (!empty($assignment['user_id']) && !empty($assignment['role'])) { $key = $assignment['user_id'].'|'.$assignment['role']; SocialPostAssignment::create(['social_post_id' => $post->id, 'user_id' => $assignment['user_id'], 'assignment_role' => $assignment['role']]); $user = User::find($assignment['user_id']); if ($user && !in_array($key, $oldAssignments, true)) app(UnifiedNotificationService::class)->sendToUser($user, 'New social media assignment', "You are assigned as {$assignment['role']} for {$post->title}. Open My Assigned Posts to start work.", 'social_media', ['post_id' => $post->id, 'url' => route('social-media.my-assigned')]); } }
    private function transition(SocialPost $post, string $to, string $comment): void { $from = $post->status; $post->update(['status' => $to]); $this->recordStatus($post, $from, $to, $comment); }
    private function recordStatus(SocialPost $post, ?string $from, string $to, string $comment): void { SocialPostStatusHistory::create(['social_post_id' => $post->id, 'user_id' => auth()->id(), 'from_status' => $from, 'to_status' => $to, 'action' => str_replace('_', ' ', $to), 'comment' => $comment]); }
    private function notifyClient(SocialPost $post, string $title, string $body): void { if ($post->client) app(UnifiedNotificationService::class)->sendToUser($post->client, $title, $body, 'social_media', ['post_id' => $post->id]); }
    private function notifyRole(SocialPost $post, string $role, string $title, string $body): void { $post->assignments()->where('assignment_role', $role)->with('user')->get()->each(fn ($a) => app(UnifiedNotificationService::class)->sendToUser($a->user, $title, $body, 'social_media', ['post_id' => $post->id])); }
    private function notifyWorkers(SocialPost $post, string $title, string $body): void { $post->assignments()->with('user')->get()->unique('user_id')->each(fn ($a) => app(UnifiedNotificationService::class)->sendToUser($a->user, $title, $body, 'social_media', ['post_id' => $post->id])); }
}
