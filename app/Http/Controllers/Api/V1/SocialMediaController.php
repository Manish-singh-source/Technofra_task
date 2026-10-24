<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
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

class SocialMediaController extends Controller
{
    public function options(): JsonResponse
    {
        return $this->ok([
            'clients' => User::whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email'])->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
            'staff' => User::whereRaw('LOWER(role) IN (?, ?)', ['staff', 'manager'])->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email'])->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
            'projects' => Project::orderBy('project_name')->get(['id', 'project_name'])->map(fn ($p) => ['id' => $p->id, 'name' => $p->project_name]),
            'statuses' => SocialPost::STATUSES,
            'post_types' => ['image', 'carousel', 'reel', 'video', 'story', 'other'],
            'priorities' => ['low', 'medium', 'high', 'urgent'],
            'assignment_roles' => ['designer', 'writer', 'reviewer', 'publisher'],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $posts = $this->visible($request)->with(['client:id,first_name,last_name,email', 'platforms', 'assignments.user:id,first_name,last_name,email'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))
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
        $data['created_by'] = $request->user()->id;
        $data['status'] = $request->boolean('save_as_draft') || !collect($request->input('assignments', []))->contains(fn ($a) => !empty($a['user_id'])) ? 'planned' : 'assigned';
        $post = DB::transaction(function () use ($data, $request) { $post = SocialPost::create($data); $this->sync($post, $request); $this->history($post, null, $post->status, 'Post created'); return $post; });
        return $this->ok($this->postData($post->load(['client', 'platforms', 'assignments.user'])), [], 201);
    }

    public function update(Request $request, SocialPost $post): JsonResponse
    {
        $this->guardPost($request, $post); $this->staff($request, 'edit_social_media_posts');
        $post->update($this->validatePost($request)); $this->sync($post, $request);
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
        $this->guardPost($request, $post); $data=$request->validate(['comment'=>'required|string|max:3000','is_internal'=>'nullable|boolean','parent_id'=>'nullable|exists:social_post_comments,id']);
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

    private function validatePost(Request $r): array { return $r->validate(['client_id'=>'required|exists:users,id','project_id'=>'nullable|exists:projects,id','title'=>'required|string|max:255','content_category'=>'nullable|string|max:100','post_type'=>['required',Rule::in(['image','carousel','reel','video','story','other'])],'creative_brief'=>'nullable|string','caption'=>'nullable|string','hashtags'=>'nullable|string','reference_links'=>'nullable|string','priority'=>['required',Rule::in(['low','medium','high','urgent'])],'planned_at'=>'nullable|date','internal_deadline_at'=>'nullable|date','client_approval_deadline_at'=>'nullable|date','platforms'=>'nullable|array','platforms.*'=>'string|max:60','assignments'=>'nullable|array','assignments.*.user_id'=>'nullable|exists:users,id','assignments.*.role'=>['nullable',Rule::in(['designer','writer','reviewer','publisher'])]]); }
    private function sync(SocialPost $post, Request $r): void { $platforms=collect($r->input('platforms',[]))->flatMap(fn($v)=>preg_split('/[,\n]+/',(string)$v))->map(fn($v)=>trim($v))->filter()->unique()->values(); $post->platforms()->whereNotIn('platform',$platforms)->delete(); foreach($platforms as $p)$post->platforms()->firstOrCreate(['platform'=>$p]); $post->assignments()->delete(); foreach($r->input('assignments',[]) as $a)if(!empty($a['user_id'])&&!empty($a['role']))SocialPostAssignment::create(['social_post_id'=>$post->id,'user_id'=>$a['user_id'],'assignment_role'=>$a['role']]); }
    private function transition(SocialPost $post,string $status,string $comment):void{$from=$post->status;$post->update(['status'=>$status]);$this->history($post,$from,$status,$comment);} private function history(SocialPost $p,?string $from,string $to,string $comment):void{SocialPostStatusHistory::create(['social_post_id'=>$p->id,'user_id'=>auth()->id(),'from_status'=>$from,'to_status'=>$to,'action'=>str_replace('_',' ',$to),'comment'=>$comment]);}
    private function client(Request $r):bool{return in_array(strtolower((string)$r->user()->getRawOriginal('role')),['client','customer']);} private function manager(Request $r):bool{return $r->user()->hasRole(['super_admin','super-admin','superadmin','super_admin2','admin','Admin','Super Admin']);} private function visible(Request $r){$q=SocialPost::query();if($this->client($r))return $q->where('client_id',$r->user()->id);if(!$this->manager($r)&&!$r->user()->checkPermissionTo('view_social_media_posts'))$q->whereHas('assignments',fn($a)=>$a->where('user_id',$r->user()->id));return $q;} private function guardPost(Request $r,SocialPost $p):void{abort_unless($this->visible($r)->whereKey($p->id)->exists(),403);} private function staff(Request $r,string $permission):void{abort_unless(!$this->client($r)&&($this->manager($r)||$r->user()->checkPermissionTo($permission)),403);}
    private function postData(SocialPost $p,bool $detail=false):array{$data=['id'=>$p->id,'title'=>$p->title,'status'=>$p->status,'priority'=>$p->priority,'post_type'=>$p->post_type,'content_category'=>$p->content_category,'planned_at'=>$p->planned_at,'publishing_scheduled_at'=>$p->publishing_scheduled_at,'client'=>$p->client?->only(['id','first_name','last_name','email']),'platforms'=>$p->platforms?->pluck('platform')->values()??[],'assignments'=>$p->assignments?->map(fn($a)=>['user_id'=>$a->user_id,'role'=>$a->assignment_role,'user'=>$a->user?->only(['id','first_name','last_name','email'])])->values()??[]];if($detail)$data+=['creative_brief'=>$p->creative_brief,'caption'=>$p->caption,'hashtags'=>$p->hashtags,'reference_links'=>$p->reference_links,'versions'=>$p->versions,'comments'=>$p->comments,'history'=>$p->histories,'publishing_records'=>$p->publishingRecords];return $data;} private function ok($data,array $extra=[],int $status=200):JsonResponse{return response()->json(array_merge(['success'=>true,'data'=>$data],$extra),$status);}
}
