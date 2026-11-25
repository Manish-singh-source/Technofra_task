<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SocialContentPlan;
use App\Models\SocialFestivalEvent;
use App\Models\SocialPost;
use App\Models\SocialPostApproval;
use App\Models\SocialPostAssignment;
use App\Models\SocialPostComment;
use App\Models\SocialPostPublishingRecord;
use App\Models\SocialPostStatusHistory;
use App\Models\SocialPostVersion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Imports\SocialMediaPlanPostsImport;
use Maatwebsite\Excel\Facades\Excel;

class SocialMediaController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $posts = $this->scopedPosts($request)->get();
        $counts = collect(SocialPost::STATUSES)->mapWithKeys(fn ($status) => [$status => $posts->where('status', $status)->count()]);
        $counts['planned'] = $this->visiblePlans($request)->count();
        return $this->ok(['month' => $request->input('month', now()->format('Y-m')), 'counts' => $counts, 'calendar' => $this->calendarData($request)]);
    }

    public function calendar(Request $request): JsonResponse
    {
        return $this->ok(['month' => $request->input('month'), 'events' => $this->calendarData($request)]);
    }

    public function options(Request $request): JsonResponse
    {
        $staffView = !$this->client($request);
        return $this->ok([
            'clients' => $staffView ? User::whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email'])->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]) : [],
            'staff' => $staffView ? User::whereRaw('LOWER(role) IN (?, ?)', ['staff', 'manager'])->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email'])->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]) : [],
            'projects' => $staffView ? Project::orderBy('project_name')->get(['id', 'project_name'])->map(fn ($p) => ['id' => $p->id, 'name' => $p->project_name]) : [],
            'statuses' => SocialPost::STATUSES,
            'post_types' => ['image', 'carousel', 'reel', 'video', 'story', 'other'],
            'priorities' => ['low', 'medium', 'high', 'urgent'],
            'assignment_roles' => ['designer', 'writer', 'reviewer', 'publisher'],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $posts = $this->scopedPosts($request)->with(['client:id,first_name,last_name,email', 'platforms', 'assignments.user:id,first_name,last_name,email'])
            ->when($request->filled('status') && in_array($request->status, SocialPost::STATUSES, true), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('assigned_to'), fn ($q) => $q->whereHas('assignments', fn ($a) => $a->where('user_id', $request->assigned_to)))
            ->when($request->filled('month'), fn ($q) => $q->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2)))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim($request->search).'%'))
            ->orderBy('planned_at')->paginate(min(max((int) $request->input('per_page', 20), 1), 100));
        return $this->ok($posts->through(fn ($post) => $this->postData($post)), ['meta' => ['current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(), 'total' => $posts->total()]]);
    }

    public function show(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post);
        $post->load(['client:id,first_name,last_name,email', 'project:id,project_name', 'platforms', 'assignments.user:id,first_name,last_name,email', 'versions.files', 'versions.creator:id,first_name,last_name,email', 'comments.user:id,first_name,last_name,email', 'histories.user:id,first_name,last_name,email', 'publishingRecords']);
        return $this->ok($this->postData($post, true));
    }

    public function store(Request $request): JsonResponse
    {
        $this->staff($request, 'create_social_media_posts');
        $data = $this->validatePost($request);
        $this->validatePlanClient($data);
        $data['created_by'] = $request->user()->id;
        $data['status'] = $request->boolean('save_as_draft') || !$this->normalizeAssignments($request->input('assignments', [])) ? 'planned' : 'assigned';
        $post = DB::transaction(function () use ($data, $request) { $post = SocialPost::create($data); $this->sync($post, $request); $this->history($post, null, $post->status, 'Post created'); return $post; });
        return $this->ok($this->postData($post->load(['client', 'platforms', 'assignments.user'])), [], 201);
    }

    public function update(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post); $this->staff($request, 'edit_social_media_posts');
        $data = $this->validatePost($request); $this->validatePlanClient($data); $post->update($data); $this->sync($post, $request);
        return $this->ok($this->postData($post->fresh()->load(['client', 'platforms', 'assignments.user'])));
    }

    public function start(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post); abort_unless($post->assignments()->where('user_id', $request->user()->id)->exists() || $this->manager($request), 403);
        abort_unless(in_array($post->status, ['planned','assigned','internal_changes_requested','client_changes_requested']), 422, 'Post cannot be started in its current status.');
        $this->transition($post, 'in_progress', 'Work started.'); return $this->ok($this->postData($post->fresh()));
    }

    public function uploadVersion(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post); abort_unless($post->assignments()->where('user_id', $request->user()->id)->exists() || $this->manager($request), 403);
        $request->validate(['note' => 'nullable|string|max:2000', 'files' => 'required|array|min:1|max:12', 'files.*' => 'file|max:51200|mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm,pdf']);
        $version = DB::transaction(function () use ($request, $post) { $n = (int)$post->versions()->max('version_number') + 1; $v = SocialPostVersion::create(['social_post_id'=>$post->id,'version_number'=>$n,'caption_snapshot'=>$post->caption,'hashtags_snapshot'=>$post->hashtags,'publishing_snapshot'=>['platforms'=>$post->platforms()->pluck('platform')],'note'=>$request->note,'created_by'=>$request->user()->id,'submitted_for_review_at'=>now()]); foreach($request->file('files') as $file) { $path=$file->store("social-media/{$post->id}/versions/{$n}",'local'); $v->files()->create(['original_name'=>$file->getClientOriginalName(),'file_path'=>$path,'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize()]); } $this->transition($post,'internal_review',"Version V{$n} submitted for internal review."); return $v->load('files'); });
        return $this->ok($version, [], 201);
    }

    public function internalDecision(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post); $this->staff($request, 'review_social_media_posts');
        $data=$request->validate(['decision'=>['required',Rule::in(['approved','changes_requested'])],'comment'=>'nullable|string|max:3000']); $version=$post->versions()->latest('version_number')->firstOrFail();
        SocialPostApproval::create(['social_post_id'=>$post->id,'social_post_version_id'=>$version->id,'decided_by'=>$request->user()->id,'stage'=>'internal','decision'=>$data['decision'],'comment'=>$data['comment']??null]); $this->transition($post,$data['decision']==='approved'?'pending_client_approval':'internal_changes_requested',$data['comment']??'Internal review completed.');
        return $this->ok($this->postData($post->fresh()));
    }

    public function clientDecision(Request $request, SocialPost $post): JsonResponse
    {
        abort_unless($this->client($request) && $post->client_id === $request->user()->id && $post->status === 'pending_client_approval', 403);
        $data=$request->validate(['decision'=>['required',Rule::in(['approved','changes_requested','rejected'])],'comment'=>'nullable|required_if:decision,changes_requested,rejected|string|max:3000']); $version=$post->versions()->latest('version_number')->firstOrFail();
        SocialPostApproval::create(['social_post_id'=>$post->id,'social_post_version_id'=>$version->id,'decided_by'=>$request->user()->id,'stage'=>'client','decision'=>$data['decision'],'comment'=>$data['comment']??null]); $this->transition($post,['approved'=>'client_approved','changes_requested'=>'client_changes_requested','rejected'=>'rejected'][$data['decision']],$data['comment']??'Client decision recorded.');
        return $this->ok($this->postData($post->fresh()));
    }

    public function comment(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post); $data=$request->validate(['comment'=>'required|string|max:3000','is_internal'=>'nullable|boolean','parent_id'=>'nullable|exists:social_post_comments,id']); abort_unless(empty($data['parent_id']) || SocialPostComment::whereKey($data['parent_id'])->where('social_post_id', $post->id)->exists(), 422, 'The parent comment does not belong to this post.');
        $comment=SocialPostComment::create(['social_post_id'=>$post->id,'user_id'=>$request->user()->id,'comment'=>$data['comment'],'is_internal'=>!$this->client($request) && $request->boolean('is_internal'),'parent_id'=>$data['parent_id']??null]); return $this->ok($comment->load('user'), [], 201);
    }

    public function schedule(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request,$post); $this->staff($request,'manage_social_publishing'); abort_unless(in_array($post->status,['client_approved','ready_for_publishing','scheduled']),422);
        $data=$request->validate(['scheduled_at'=>'required|date']); SocialPostPublishingRecord::create(['social_post_id'=>$post->id,'published_by'=>$request->user()->id,'status'=>'scheduled','scheduled_at'=>$data['scheduled_at']]); $post->update(['publishing_scheduled_at'=>$data['scheduled_at']]); $this->transition($post,'scheduled','Publishing scheduled.'); return $this->ok($this->postData($post->fresh()));
    }

    public function publish(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request,$post); $this->staff($request,'manage_social_publishing'); $data=$request->validate(['actual_published_at'=>'required|date','published_url'=>'nullable|url|max:2048','proof'=>'nullable|file|max:20480|mimes:jpg,jpeg,png,webp,pdf']); $proof=$request->file('proof')?->store("social-media/{$post->id}/publishing",'local'); SocialPostPublishingRecord::create(['social_post_id'=>$post->id,'published_by'=>$request->user()->id,'status'=>'published','actual_published_at'=>$data['actual_published_at'],'published_url'=>$data['published_url']??null,'proof_path'=>$proof]); $this->transition($post,'published','Publishing confirmed.'); return $this->ok($this->postData($post->fresh()));
    }

    private function validatePost(Request $r): array { return $r->validate(['social_content_plan_id'=>'nullable|exists:social_content_plans,id','social_festival_event_id'=>'nullable|exists:social_festival_events,id','client_id'=>'required|exists:users,id','project_id'=>'nullable|exists:projects,id','title'=>'required|string|max:255','content_category'=>'nullable|string|max:100','post_type'=>['required',Rule::in(['image','carousel','reel','video','story','other'])],'creative_brief'=>'nullable|string','caption'=>'nullable|string','hashtags'=>'nullable|string','reference_links'=>'nullable|string','priority'=>['required',Rule::in(['low','medium','high','urgent'])],'planned_at'=>'nullable|date','internal_deadline_at'=>'nullable|date','client_approval_deadline_at'=>'nullable|date','platforms'=>'nullable|array','platforms.*'=>'string|max:60','assignments'=>'nullable|array','assignments.*.user_id'=>'nullable|exists:users,id','assignments.*.role'=>['nullable',Rule::in(['designer','writer','reviewer','publisher'])]]); }
    private function validatePlanClient(array $data): void { if (!empty($data['social_content_plan_id']) && !SocialContentPlan::whereKey($data['social_content_plan_id'])->where('client_id', $data['client_id'])->exists()) abort(422, 'The selected monthly plan does not belong to the selected client.'); if (!empty($data['social_festival_event_id'])) { $festival = SocialFestivalEvent::findOrFail($data['social_festival_event_id']); if ($festival->client_id && (int) $festival->client_id !== (int) $data['client_id']) abort(422, 'The selected festival does not belong to the selected client.'); if (!empty($data['social_content_plan_id'])) { $plan = SocialContentPlan::findOrFail($data['social_content_plan_id']); abort_unless($festival->event_date && (int) $festival->event_date->year === (int) $plan->plan_year && (int) $festival->event_date->month === (int) $plan->plan_month, 422, 'The selected festival is outside the monthly plan period.'); } } }
    private function sync(SocialPost $post, Request $r): void { $platforms=collect($r->input('platforms',[]))->flatMap(fn($v)=>preg_split('/[,\n]+/',(string)$v))->map(fn($v)=>trim($v))->filter()->unique()->values(); $post->platforms()->whereNotIn('platform',$platforms)->delete(); foreach($platforms as $p)$post->platforms()->firstOrCreate(['platform'=>$p]); $post->assignments()->delete(); foreach($this->normalizeAssignments($r->input('assignments',[])) as $a) SocialPostAssignment::create(['social_post_id'=>$post->id,'user_id'=>$a['user_id'],'assignment_role'=>$a['role']]); }
    private function normalizeAssignments($assignments): array { $roles = ['designer', 'writer', 'reviewer', 'publisher']; $normalized = []; foreach ((array) $assignments as $key => $value) { if (is_string($key) && in_array($key, $roles, true)) { foreach ((array) $value as $userId) if ($userId) $normalized[] = ['user_id' => $userId, 'role' => $key]; } elseif (is_array($value) && !empty($value['user_id']) && !empty($value['role'])) { $normalized[] = ['user_id' => $value['user_id'], 'role' => $value['role']]; } } return collect($normalized)->unique(fn ($a) => $a['user_id'].'|'.$a['role'])->values()->all(); }
    private function transition(SocialPost $post,string $status,string $comment):void{$from=$post->status;$post->update(['status'=>$status]);$this->history($post,$from,$status,$comment);} private function history(SocialPost $p,?string $from,string $to,string $comment):void{SocialPostStatusHistory::create(['social_post_id'=>$p->id,'user_id'=>auth()->id(),'from_status'=>$from,'to_status'=>$to,'action'=>str_replace('_',' ',$to),'comment'=>$comment]);}
    private function client(Request $r):bool{return in_array(strtolower(trim((string)$r->user()->getRawOriginal('role'))),['client','customer'],true);} private function manager(Request $r):bool{return in_array(strtolower(trim((string)$r->user()->getRawOriginal('role'))),['admin','manager','super_admin','super-admin','superadmin','super_admin2','super admin'],true) || $r->user()->hasRole(['super_admin','super-admin','superadmin','super_admin2','admin','manager','Admin','Super Admin']);} private function visible(Request $r){$q=SocialPost::query();if($this->client($r))return $q->where('client_id',$r->user()->id);if(!$this->manager($r)&&!$r->user()->checkPermissionTo('view_social_media_posts'))$q->whereHas('assignments',fn($a)=>$a->where('user_id',$r->user()->id));return $q;} private function guardPost(Request $r,SocialPost $p):void{abort_unless($this->visible($r)->whereKey($p->id)->exists() || (!$this->client($r) && $p->social_content_plan_id),403);} private function staff(Request $r,string $permission):void{abort_unless(!$this->client($r)&&($this->manager($r)||$r->user()->checkPermissionTo($permission)),403);}
    public function destroy(Request $request, SocialPost $post): JsonResponse { $this->guardPost($request, $post); $this->staff($request, 'delete_social_media_posts'); $post->delete(); return $this->ok(['id' => $post->id, 'deleted' => true]); }
    public function versionFile(Request $request, SocialPost $post, SocialPostVersion $version, \App\Models\SocialPostVersionFile $file) { $this->guardPost($request, $post); abort_unless($version->social_post_id === $post->id && $file->social_post_version_id === $version->id, 404); abort_unless(Storage::disk('local')->exists($file->file_path), 404); return Storage::disk('local')->download($file->file_path, $file->original_name, ['Content-Type' => $file->mime_type ?: 'application/octet-stream']); }
    public function downloadVersionFile(Request $request, SocialPost $post, \App\Models\SocialPostVersionFile $file) { $this->guardPost($request, $post); abort_unless($file->socialPostVersion?->social_post_id === $post->id, 404); abort_unless(Storage::disk('local')->exists($file->file_path), 404); return Storage::disk('local')->download($file->file_path, $file->original_name, ['Content-Type' => $file->mime_type ?: 'application/octet-stream']); }
    public function previewVersionFile(Request $request, SocialPost $post, \App\Models\SocialPostVersionFile $file) { $this->guardPost($request, $post); abort_unless($file->socialPostVersion?->social_post_id === $post->id, 404); abort_unless(str_starts_with((string) $file->mime_type, 'image/') || str_starts_with((string) $file->mime_type, 'video/'), 404); abort_unless(Storage::disk('local')->exists($file->file_path), 404); return response()->file(Storage::disk('local')->path($file->file_path), ['Content-Type' => $file->mime_type ?: 'application/octet-stream']); }
    public function assigned(Request $request): JsonResponse { abort_unless(!$this->client($request), 403); $query = $this->manager($request) || $request->user()->checkPermissionTo('view_social_media_posts') ? $this->visible($request) : $this->assignedQuery($request); $posts = $query->with(['client', 'platforms', 'assignments.user'])->paginate($this->perPage($request)); return $this->paginated($posts, fn ($post) => $this->postData($post)); }
    public function queue(Request $request, string $queue): JsonResponse { $statuses = match ($queue) { 'review' => ['internal_review', 'internal_changes_requested'], 'approvals' => ['pending_client_approval', 'client_changes_requested'], default => abort(404, 'Unknown social media queue.') }; if ($queue === 'review') $this->staff($request, 'review_social_media_posts'); $posts = $this->visible($request)->whereIn('status', $statuses)->with(['client', 'platforms', 'versions'])->latest()->paginate($this->perPage($request)); return $this->paginated($posts, fn ($post) => $this->postData($post)); }
    public function publishing(Request $request): JsonResponse { $this->staff($request, 'manage_social_publishing'); $posts = $this->visible($request)->whereIn('status', ['client_approved', 'ready_for_publishing', 'scheduled', 'published'])->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))->when($request->filled('status') && in_array($request->status, ['client_approved', 'ready_for_publishing', 'scheduled', 'published'], true), fn ($q) => $q->where('status', $request->status))->when($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->month), fn ($q) => $q->whereYear('publishing_scheduled_at', substr($request->month, 0, 4))->whereMonth('publishing_scheduled_at', substr($request->month, 5, 2)))->with(['client', 'platforms', 'publishingRecords'])->latest('publishing_scheduled_at')->paginate($this->perPage($request)); return $this->paginated($posts, fn ($post) => $this->postData($post)); }
    public function reports(Request $request): JsonResponse { $this->staff($request, 'view_social_media_reports'); $posts = $this->visible($request)->when($request->filled('month'), fn ($q) => $q->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2)))->with('client')->get(); return $this->ok(['month' => $request->input('month'), 'summary' => ['planned' => $posts->count(), 'approved' => $posts->whereIn('status', ['client_approved','ready_for_publishing','scheduled','published','completed'])->count(), 'published' => $posts->whereIn('status', ['published','completed'])->count(), 'pending' => $posts->where('status', 'pending_client_approval')->count(), 'delayed' => $posts->filter(fn ($p) => $p->status !== 'published' && $p->publishing_scheduled_at?->isPast())->count(), 'rejected' => $posts->where('status', 'rejected')->count(), 'revisions' => $posts->sum(fn ($p) => $p->versions()->count())], 'posts' => $posts->map(fn ($p) => $this->postData($p))]); }
    private function perPage(Request $request): int { return min(max((int) $request->input('per_page', 20), 1), 100); }
    private function paginated($items, callable $map): JsonResponse { return $this->ok($items->getCollection()->map($map)->values(), ['meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total(), 'per_page' => $items->perPage()]]); }
    private function scopedPosts(Request $request) { return $this->visible($request)->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))->when($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->month), fn ($q) => $q->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2))); }
    private function assignedQuery(Request $request) { return SocialPost::query()->whereHas('assignments', fn ($q) => $q->where('user_id', $request->user()->id))->when(!$this->manager($request) && !$request->user()->checkPermissionTo('review_social_media_posts'), fn ($q) => $q->where('status', '!=', 'internal_review')); }
    private function visiblePlans(Request $request) { return SocialContentPlan::query()->when($this->client($request), fn ($q) => $q->where('client_id', $request->user()->id)->where('status', '!=', 'draft')); }
    private function canViewPlans(Request $request): void { abort_unless((bool) $request->user(), 401); }
    private function guardPlan(Request $request, SocialContentPlan $plan): void { $this->canViewPlans($request); abort_unless(!$this->client($request) || ($plan->client_id === $request->user()->id && $plan->status !== 'draft'), 403); }
    private function validateFestival(Request $request): array { return $request->validate(['name' => 'required|string|max:255', 'event_date' => 'nullable|date', 'category' => 'nullable|string|max:100', 'country_region' => 'nullable|string|max:100', 'recurrence_type' => ['required', Rule::in(['none', 'yearly'])], 'client_id' => 'nullable|exists:users,id', 'description' => 'nullable|string', 'reference_url' => 'nullable|url|max:2048']); }
    private function planData(SocialContentPlan $plan, bool $detail = false): array { $data = ['id' => $plan->id, 'client_id' => $plan->client_id, 'client' => $plan->client?->only(['id', 'first_name', 'last_name', 'email']), 'plan_month' => $plan->plan_month, 'plan_year' => $plan->plan_year, 'title' => $plan->title, 'notes' => $plan->notes, 'status' => $plan->status, 'client_feedback' => $plan->client_feedback, 'client_reviewed_at' => $plan->client_reviewed_at, 'posts_count' => $plan->posts_count ?? $plan->posts?->count()]; if ($detail) $data['posts'] = $plan->posts->map(fn ($post) => $this->postData($post, true)); return $data; }
    private function festivalData(SocialFestivalEvent $event): array { return ['id' => $event->id, 'name' => $event->name, 'event_date' => $event->event_date, 'category' => $event->category, 'country_region' => $event->country_region, 'recurrence_type' => $event->recurrence_type, 'client' => $event->client?->only(['id', 'first_name', 'last_name', 'email']), 'description' => $event->description, 'reference_url' => $event->reference_url]; }
    private function calendarData(Request $request): array { $posts = $this->visible($request)->where(function ($q) { $q->whereNotNull('planned_at')->orWhereNotNull('publishing_scheduled_at'); })->when($request->filled('month'), fn ($q) => $q->where(function ($m) use ($request) { $m->whereYear('planned_at', substr($request->month, 0, 4))->whereMonth('planned_at', substr($request->month, 5, 2)); }))->with(['client', 'platforms'])->orderByRaw('COALESCE(publishing_scheduled_at, planned_at)')->get(); return $posts->map(fn ($p) => ['id' => $p->id, 'title' => $p->title, 'start' => ($p->publishing_scheduled_at ?: $p->planned_at)?->toIso8601String(), 'status' => $p->status, 'client' => $p->client?->name, 'platforms' => $p->platforms->pluck('platform')->values()])->values()->all(); }
    public function plans(Request $request): JsonResponse { $this->canViewPlans($request); $plans = $this->visiblePlans($request)->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))->when($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->month), fn ($q) => $q->where('plan_year', substr($request->month, 0, 4))->where('plan_month', substr($request->month, 5, 2)))->withCount('posts')->with('client')->latest('plan_year')->latest('plan_month')->paginate($this->perPage($request)); return $this->paginated($plans, fn ($plan) => $this->planData($plan)); }
    public function storePlan(Request $request): JsonResponse { $this->staff($request, 'manage_social_content_plans'); $data = $request->validate(['client_ids' => 'required_without:client_id|array|min:1', 'client_ids.*' => 'integer|distinct|exists:users,id', 'client_id' => 'nullable|required_without:client_ids|exists:users,id', 'plan_month' => 'required|integer|between:1,12', 'plan_year' => 'required|integer|between:2020,2100', 'title' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:4000', 'status' => 'nullable|in:draft,pending_client_review,client_approved,changes_requested,not_required']); $ids = $data['client_ids'] ?? [$data['client_id']]; unset($data['client_ids'], $data['client_id']); $data['created_by'] = $request->user()->id; $data['status'] ??= 'draft'; $plans = collect($ids)->map(fn ($id) => SocialContentPlan::updateOrCreate(['client_id' => $id, 'plan_month' => $data['plan_month'], 'plan_year' => $data['plan_year']], array_merge($data, ['client_id' => $id]))); return $this->ok($plans->map(fn ($p) => $this->planData($p->load('client'))), [], 201); }
    public function showPlan(Request $request, SocialContentPlan $plan): JsonResponse { $this->guardPlan($request, $plan); return $this->ok($this->planData($plan->load(['client', 'posts.platforms', 'posts.assignments.user', 'posts.versions.files', 'posts.festival']), true)); }
    public function downloadPlanPostsTemplate(Request $request, SocialContentPlan $plan) { $this->staff($request, 'manage_social_content_plans'); return Excel::download(new \App\Exports\SocialMediaPlanPostsTemplateExport(), 'social-media-plan-posts-template.xlsx'); }
    public function updatePlan(Request $request, SocialContentPlan $plan): JsonResponse { $this->staff($request, 'manage_social_content_plans'); $plan->update($request->validate(['client_id' => 'required|exists:users,id', 'plan_month' => 'required|integer|between:1,12', 'plan_year' => 'required|integer|between:2020,2100', 'title' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:4000', 'status' => 'nullable|in:draft,pending_client_review,client_approved,changes_requested,not_required'])); return $this->ok($this->planData($plan->fresh()->load('client'))); }
    public function destroyPlan(Request $request, SocialContentPlan $plan): JsonResponse { $this->staff($request, 'manage_social_content_plans'); DB::transaction(fn () => tap($plan->posts()->delete(), fn () => $plan->delete())); return $this->ok(['id' => $plan->id, 'deleted' => true]); }
    public function clientPlanDecision(Request $request, SocialContentPlan $plan): JsonResponse { abort_unless($this->client($request) && $plan->client_id === $request->user()->id && $plan->status === 'pending_client_review', 403); $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_requested', 'not_required'])], 'feedback' => 'nullable|required_if:decision,changes_requested|string|max:4000']); $plan->update(['status' => $data['decision'] === 'approved' ? 'client_approved' : ($data['decision'] === 'not_required' ? 'not_required' : 'changes_requested'), 'client_feedback' => $data['feedback'] ?? null, 'client_reviewed_at' => now()]); return $this->ok($this->planData($plan->fresh())); }
    public function importPlanPosts(Request $request, SocialContentPlan $plan): JsonResponse { $this->staff($request, 'manage_social_content_plans'); $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']); $import = new SocialMediaPlanPostsImport($plan); Excel::import($import, $request->file('file')); return $this->ok(['created' => $import->created, 'errors' => $import->errors]); }
    public function bulkAssignPlanPosts(Request $request, SocialContentPlan $plan): JsonResponse { $this->staff($request, 'edit_social_media_posts'); $data = $request->validate(['post_ids' => 'required|array|min:1', 'post_ids.*' => 'integer|distinct|exists:social_posts,id', 'role' => ['required', Rule::in(['designer', 'writer', 'reviewer', 'publisher'])], 'user_ids' => 'required|array|min:1', 'user_ids.*' => 'integer|distinct|exists:users,id']); abort_unless(User::whereIn('id', $data['user_ids'])->whereRaw('LOWER(role) IN (?, ?)', ['staff', 'manager'])->count() === count($data['user_ids']), 422, 'All selected assignees must be staff members.'); $posts = SocialPost::where('social_content_plan_id', $plan->id)->whereIn('id', $data['post_ids'])->get(); abort_unless($posts->count() === count($data['post_ids']), 422, 'One or more selected posts do not belong to this plan.'); foreach ($posts as $post) { foreach ($data['user_ids'] as $id) SocialPostAssignment::firstOrCreate(['social_post_id' => $post->id, 'user_id' => $id, 'assignment_role' => $data['role']]); if ($post->status === 'planned') $this->transition($post, 'assigned', 'Bulk assigned from monthly content plan.'); } return $this->ok(['assigned_posts' => $posts->count()]); }
    public function bulkDestroyPlanPosts(Request $request, SocialContentPlan $plan): JsonResponse { $this->staff($request, 'delete_social_media_posts'); $data = $request->validate(['post_ids' => 'required|array|min:1', 'post_ids.*' => 'integer|distinct|exists:social_posts,id']); $query = SocialPost::where('social_content_plan_id', $plan->id)->whereIn('id', $data['post_ids']); abort_unless($query->count() === count($data['post_ids']), 422, 'One or more selected posts do not belong to this plan.'); return $this->ok(['deleted' => $query->delete()]); }
    public function festivals(Request $request): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $events = SocialFestivalEvent::with('client')->when($request->filled('year'), fn ($q) => $q->whereYear('event_date', $request->year))->orderBy('event_date')->paginate($this->perPage($request)); return $this->paginated($events, fn ($event) => $this->festivalData($event)); }
    public function showFestival(Request $request, SocialFestivalEvent $festival): JsonResponse { $this->staff($request, 'manage_social_festival_events'); return $this->ok($this->festivalData($festival->load('client'))); }
    public function storeFestival(Request $request): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $festival = SocialFestivalEvent::create($this->validateFestival($request)); return $this->ok($this->festivalData($festival->load('client')), [], 201); }
    public function updateFestival(Request $request, SocialFestivalEvent $festival): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $festival->update($this->validateFestival($request)); return $this->ok($this->festivalData($festival->fresh()->load('client'))); }
    public function destroyFestival(Request $request, SocialFestivalEvent $festival): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $festival->delete(); return $this->ok(['id' => $festival->id, 'deleted' => true]); }
    public function importFestivals(Request $request): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $data = $request->validate(['year' => 'required|integer|between:2000,2100']); return $this->ok(['year' => $data['year'], 'count' => app(\App\Services\FestivalCalendarService::class)->seedYear($data['year'])]); }
    public function bulkDestroyFestivals(Request $request): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $data = $request->validate(['festival_ids' => 'required|array|min:1', 'festival_ids.*' => 'integer|exists:social_festival_events,id']); return $this->ok(['deleted' => SocialFestivalEvent::whereIn('id', $data['festival_ids'])->delete()]); }
    public function destroyFestivalsByYear(Request $request): JsonResponse { $this->staff($request, 'manage_social_festival_events'); $data = $request->validate(['year' => 'required|integer|between:2000,2100']); return $this->ok(['deleted' => SocialFestivalEvent::whereYear('event_date', $data['year'])->delete(), 'year' => $data['year']]); }
    private function postData(SocialPost $p,bool $detail=false):array{$data=['id'=>$p->id,'title'=>$p->title,'status'=>$p->status,'priority'=>$p->priority,'post_type'=>$p->post_type,'content_category'=>$p->content_category,'planned_at'=>$p->planned_at,'publishing_scheduled_at'=>$p->publishing_scheduled_at,'client'=>$p->client?->only(['id','first_name','last_name','email']),'platforms'=>$p->platforms?->pluck('platform')->values()??[],'assignments'=>$p->assignments?->map(fn($a)=>['user_id'=>$a->user_id,'role'=>$a->assignment_role,'user'=>$a->user?->only(['id','first_name','last_name','email'])])->values()??[]];if($detail)$data+=['creative_brief'=>$p->creative_brief,'caption'=>$p->caption,'hashtags'=>$p->hashtags,'reference_links'=>$p->reference_links,'versions'=>$p->versions,'comments'=>$p->comments,'history'=>$p->histories,'publishing_records'=>$p->publishingRecords];return $data;} private function ok($data,array $extra=[],int $status=200):JsonResponse{return response()->json(array_merge(['success'=>true,'data'=>$data],$extra),$status);}
}
