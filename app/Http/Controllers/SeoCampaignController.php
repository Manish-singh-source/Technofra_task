<?php
namespace App\Http\Controllers;

use App\Models\{SeoAudit, SeoAuditFinding, SeoBaseline, SeoCampaign, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SeoCampaignController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived') || $request->routeIs('seo.campaigns.archived');
        if ($showArchived) $this->requireAdmin();
        $query = SeoCampaign::with(['client','members.user'])->latest();
        if ($showArchived) $query->onlyTrashed();
        if (! $this->isAdmin()) $query->where(function($q){ $q->where('client_id',auth()->id())->orWhereHas('members',fn($m)=>$m->where('user_id',auth()->id())->whereNull('unassigned_at')); });
        return view('seo.campaigns.index', ['campaigns'=>$query->paginate(20)->withQueryString(), 'canCreateCampaign'=>$this->isAdmin(), 'showArchived'=>$showArchived]);
    }
    public function create()
    {
        $this->requireAdmin();
        return view('seo.campaigns.create', $this->formData());
    }
    public function store(Request $request)
    {
        $this->requireAdmin(); $data=$this->campaignData($request); $members=$data['members']??[]; unset($data['members']);
        abort_unless(collect($members)->where('campaign_role', 'manager')->isNotEmpty(), 422, 'Assign at least one SEO Manager.');
        $data['target_locations'] = filled($data['target_locations'] ?? null) ? collect(explode(',', $data['target_locations']))->map(fn ($location) => trim($location))->filter()->values()->all() : null;
        $campaign=DB::transaction(function() use($data,$members){ $campaign=SeoCampaign::create($data+['created_by'=>auth()->id()]); foreach($members as $member){ $campaign->members()->create(['user_id'=>$member['user_id'],'campaign_role'=>$member['campaign_role']]); } return $campaign; });
        return redirect()->route('seo.campaigns.show',$campaign)->with('success','SEO campaign created. Begin the initial audit.');
    }
    public function show(SeoCampaign $campaign)
    {
        $this->authorize('view',$campaign);
        if (in_array(strtolower((string) (auth()->user()?->getRawOriginal('role') ?? '')), ['client', 'customer'], true)) return redirect()->route('seo.workspace', [$campaign, 'tab' => 'reports']);
        $campaign->load(['client','members.user','audit.findings.assignee','baseline.metrics']);
        return view('seo.campaigns.show', compact('campaign'));
    }
    public function destroy(SeoCampaign $campaign)
    {
        $this->requireAdmin();
        $campaign->delete();
        return redirect()->route('seo.campaigns.index')->with('success', 'SEO campaign archived. You can restore it from Archived campaigns.');
    }
    public function restore(int $campaignId)
    {
        $this->requireAdmin();
        $campaign = SeoCampaign::onlyTrashed()->findOrFail($campaignId);
        $campaign->restore();
        return redirect()->route('seo.campaigns.index', ['archived' => 1])->with('success', 'SEO campaign restored.');
    }
    public function saveAudit(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute',$campaign); $data=$request->validate(['summary'=>'nullable|string|max:10000']);
        $audit=$campaign->audit()->firstOrCreate([], ['prepared_by'=>auth()->id()]); abort_if($audit->status==='approved', 422, 'An approved audit cannot be edited.');
        $audit->update($data+['prepared_by'=>auth()->id()]); return back()->with('success','Audit draft saved.');
    }
    public function addFinding(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute',$campaign); $audit=$campaign->audit()->firstOrCreate([],['prepared_by'=>auth()->id()]); abort_if($audit->status==='approved',422,'An approved audit cannot be edited.');
        $data=$request->validate(['category'=>['required',Rule::in(['technical','on_page','performance','other'])],'title'=>'required|string|max:255','affected_url'=>'nullable|url|max:2048','description'=>'nullable|string|max:5000','severity'=>['required',Rule::in(['critical','high','medium','low'])],'recommended_solution'=>'nullable|string|max:5000','assigned_to'=>['nullable',Rule::exists('users','id')]]);
        $audit->findings()->create($data); return back()->with('success','Audit finding added.');
    }
    public function submitAudit(SeoCampaign $campaign)
    { $this->authorize('contribute',$campaign); $audit=$campaign->audit; if (! $audit || ! $audit->findings()->exists()) return back()->with('error','Add at least one audit finding before submitting the audit for review.'); $audit->update(['status'=>'submitted','submitted_at'=>now()]); return back()->with('success','Audit submitted for manager review.'); }
    public function reviewAudit(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('manage',$campaign); $data=$request->validate(['decision'=>['required',Rule::in(['approved','changes_required'])],'feedback'=>'nullable|string|max:5000']); $audit=$campaign->audit; abort_unless($audit && $audit->status==='submitted',422,'Only submitted audits can be reviewed.');
        $audit->update(['status'=>$data['decision'],'reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_feedback'=>$data['feedback']??null]);
        return back()->with('success',$data['decision']==='approved'?'Audit approved. Capture the immutable baseline.':'Changes requested from the SEO executive.');
    }
    public function saveBaseline(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute',$campaign); abort_unless(optional($campaign->audit)->status==='approved',422,'Approve the initial audit before saving the baseline.'); $baseline=$campaign->baseline; abort_if($baseline?->status==='approved',422,'The approved baseline is immutable.');
        $data=$request->validate(['captured_on'=>'required|date','notes'=>'nullable|string|max:5000','metrics'=>'required|array|min:1','metrics.*.key'=>'required|string|max:100','metrics.*.value'=>'nullable|numeric','metrics.*.unit'=>'nullable|string|max:30','metrics.*.source'=>'nullable|string|max:100']);
        DB::transaction(function() use($campaign,$baseline,$data){ $baseline=$baseline ?: SeoBaseline::create(['seo_campaign_id'=>$campaign->id,'seo_audit_id'=>$campaign->audit->id,'captured_by'=>auth()->id(),'status'=>'draft','captured_on'=>$data['captured_on']]); abort_if($baseline->status==='approved',422,'The approved baseline is immutable.'); $baseline->update(['captured_on'=>$data['captured_on'],'notes'=>$data['notes']??null,'captured_by'=>auth()->id()]); $baseline->metrics()->delete(); foreach($data['metrics'] as $metric){$baseline->metrics()->create(['metric_key'=>$metric['key'],'metric_value'=>$metric['value']??null,'unit'=>$metric['unit']??null,'source'=>$metric['source']??null]);} });
        return back()->with('success','Baseline draft saved.');
    }
    public function approveBaseline(SeoCampaign $campaign)
    { $this->authorize('manage',$campaign); $baseline=$campaign->baseline; abort_unless($baseline && $baseline->metrics()->exists(),422,'Add baseline metrics before approval.'); abort_if($baseline->status==='approved',422,'Baseline is already approved.'); $baseline->update(['status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now()]); return back()->with('success','Baseline approved and permanently locked.'); }
    private function campaignData(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['client', 'customer']))],
            'name' => 'required|string|max:255', 'website_url' => 'required|url|max:2048', 'industry' => 'nullable|string|max:255',
            'target_country' => 'nullable|string|max:100', 'target_locations' => 'nullable|string|max:2000', 'target_language' => 'required|string|max:20',
            'search_engine' => 'required|string|max:50', 'devices' => 'nullable|array', 'devices.*' => Rule::in(['desktop', 'mobile']),
            'start_date' => 'required|date', 'goals' => 'nullable|string|max:5000', 'reporting_frequency' => ['required', Rule::in(['weekly', 'monthly', 'quarterly'])],
            'status' => ['required', Rule::in(['draft', 'active', 'on_hold', 'completed', 'cancelled'])], 'members' => 'required|array|min:1',
            'members.*.user_id' => ['required', 'distinct', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'staff'))],
            'members.*.campaign_role' => ['required', Rule::in(['manager', 'executive'])],
        ]);
    }
    private function formData(): array { return ['clients'=>User::query()->whereIn('role',['client','customer'])->orderBy('first_name')->get(),'staff'=>User::staffMembers()->orderBy('first_name')->get()]; }
    private function isAdmin(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'super_admin', 'super admin', 'super_admin2', 'admin'])) return true;
        return in_array(strtolower(trim((string) ($user->getRawOriginal('role') ?? ''))), ['admin', 'super-admin', 'super_admin', 'super admin', 'super_admin2'], true);
    }
    private function requireAdmin(): void { abort_unless($this->isAdmin(),403,'Only administrators can create SEO campaigns.'); }
}
