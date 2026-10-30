<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{SeoActivity, SeoBacklinkActivity, SeoCampaign, SeoCompetitor, SeoKeyword, SeoKeywordRanking, SeoMonthlyCycle, SeoOnPageOptimization, SeoPerformanceRecord, SeoReport, SeoTechnicalIssue, SeoAudit, SeoAuditFinding, SeoBaseline};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SeoController extends Controller
{
    public function campaigns(Request $request)
    {
        $query = SeoCampaign::with(['client', 'members.user'])->latest();

        if ($request->boolean('archived')) {
            $this->requireAdmin();
            $query->onlyTrashed();
        } elseif (! $this->isAdmin()) {
            $query->where(fn ($q) => $q
                ->where('client_id', auth()->id())
                ->orWhereHas('members', fn ($m) => $m->where('user_id', auth()->id())->whereNull('unassigned_at')));
        }

        $campaigns = $query->paginate(min((int) $request->integer('per_page', 20), 100));

        return $this->ok($campaigns);
    }

    public function storeCampaign(Request $request)
    {
        $this->requireAdmin();
        $data = $this->campaignData($request);
        $members = $data['members'] ?? [];
        unset($data['members']);

        abort_unless(collect($members)->where('campaign_role', 'manager')->isNotEmpty(), 422, 'Assign at least one SEO Manager.');
        $data['target_locations'] = filled($data['target_locations'] ?? null)
            ? collect(explode(',', $data['target_locations']))->map(fn ($location) => trim($location))->filter()->values()->all()
            : null;

        $campaign = DB::transaction(function () use ($data, $members) {
            $campaign = SeoCampaign::create($data + ['created_by' => auth()->id()]);
            foreach ($members as $member) {
                $campaign->members()->create([
                    'user_id' => $member['user_id'],
                    'campaign_role' => $member['campaign_role'],
                ]);
            }
            return $campaign;
        });

        return $this->created($campaign->load(['client', 'members.user']), 'SEO campaign created.');
    }

    public function showCampaign(SeoCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        return $this->ok($campaign->load([
            'client', 'members.user', 'audit.findings.assignee', 'baseline.metrics',
        ]));
    }

    public function archiveCampaign(SeoCampaign $campaign)
    {
        $this->requireAdmin();
        $campaign->delete();
        return $this->message('SEO campaign archived.');
    }

    public function restoreCampaign(int $campaignId)
    {
        $this->requireAdmin();
        $campaign = SeoCampaign::onlyTrashed()->findOrFail($campaignId);
        $campaign->restore();
        return $this->ok($campaign, 'SEO campaign restored.');
    }

    public function saveAudit(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['summary' => 'nullable|string|max:10000']);
        $audit = $campaign->audit()->firstOrCreate([], ['prepared_by' => auth()->id()]);
        abort_if($audit->status === 'approved', 422, 'An approved audit cannot be edited.');
        $audit->update($data + ['prepared_by' => auth()->id()]);
        return $this->ok($audit->fresh('findings'), 'Audit draft saved.');
    }

    public function addFinding(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $audit = $campaign->audit()->firstOrCreate([], ['prepared_by' => auth()->id()]);
        abort_if($audit->status === 'approved', 422, 'An approved audit cannot be edited.');
        $data = $request->validate([
            'category' => ['required', Rule::in(['technical', 'on_page', 'performance', 'other'])],
            'title' => 'required|string|max:255', 'affected_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string|max:5000',
            'severity' => ['required', Rule::in(['critical', 'high', 'medium', 'low'])],
            'recommended_solution' => 'nullable|string|max:5000',
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
        ]);
        $this->ensureMember($campaign, $data['assigned_to'] ?? null);
        $finding = $audit->findings()->create($data);
        return $this->created($finding->load('assignee'), 'Audit finding added.');
    }

    public function updateFinding(Request $request, SeoCampaign $campaign, SeoAuditFinding $finding)
    {
        $this->authorize('contribute', $campaign);
        abort_unless($finding->audit?->seo_campaign_id === $campaign->id, 404);
        if (! auth()->user()->can('manage', $campaign) && $finding->assigned_to && (int) $finding->assigned_to !== (int) auth()->id()) {
            abort(403, 'Only the assigned executive or a manager can update this finding.');
        }
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'verified', 'closed'])],
            'resolution_notes' => 'nullable|string|max:5000', 'evidence_url' => 'nullable|url|max:2048',
        ]);
        if (in_array($data['status'], ['verified', 'closed'], true)) $this->authorize('manage', $campaign);
        $finding->update($data + (in_array($data['status'], ['resolved', 'verified', 'closed'], true) ? ['resolved_at' => now()] : []));
        return $this->ok($finding->fresh('assignee'), 'Audit finding updated.');
    }

    public function submitAudit(SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $audit = $campaign->audit;
        abort_unless($audit && $audit->findings()->exists(), 422, 'Add at least one audit finding before submitting the audit for review.');
        $audit->update(['status' => 'submitted', 'submitted_at' => now()]);
        return $this->ok($audit, 'Audit submitted for manager review.');
    }

    public function reviewAudit(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('manage', $campaign);
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_required'])], 'feedback' => 'nullable|string|max:5000']);
        $audit = $campaign->audit;
        abort_unless($audit && $audit->status === 'submitted', 422, 'Only submitted audits can be reviewed.');
        $audit->update(['status' => $data['decision'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'review_feedback' => $data['feedback'] ?? null]);
        return $this->ok($audit, ucfirst(str_replace('_', ' ', $data['decision'])) . '.');
    }

    public function saveBaseline(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        abort_unless(optional($campaign->audit)->status === 'approved', 422, 'Approve the initial audit before saving the baseline.');
        $baseline = $campaign->baseline;
        abort_if($baseline?->status === 'approved', 422, 'The approved baseline is immutable.');
        $data = $request->validate([
            'captured_on' => 'required|date', 'notes' => 'nullable|string|max:5000',
            'metrics' => 'required|array|min:1', 'metrics.*.key' => 'required|string|max:100',
            'metrics.*.value' => 'nullable|numeric', 'metrics.*.unit' => 'nullable|string|max:30',
            'metrics.*.source' => 'nullable|string|max:100',
        ]);

        $baseline = DB::transaction(function () use ($campaign, $baseline, $data) {
            $baseline ??= SeoBaseline::create([
                'seo_campaign_id' => $campaign->id, 'seo_audit_id' => $campaign->audit->id,
                'captured_by' => auth()->id(), 'status' => 'draft', 'captured_on' => $data['captured_on'],
            ]);
            abort_if($baseline->status === 'approved', 422, 'The approved baseline is immutable.');
            $baseline->update(['captured_on' => $data['captured_on'], 'notes' => $data['notes'] ?? null, 'captured_by' => auth()->id()]);
            $baseline->metrics()->delete();
            foreach ($data['metrics'] as $metric) $baseline->metrics()->create([
                'metric_key' => $metric['key'], 'metric_value' => $metric['value'] ?? null,
                'unit' => $metric['unit'] ?? null, 'source' => $metric['source'] ?? null,
            ]);
            return $baseline;
        });

        return $this->ok($baseline->load('metrics'), 'Baseline draft saved.');
    }

    public function approveBaseline(SeoCampaign $campaign)
    {
        $this->authorize('manage', $campaign);
        $baseline = $campaign->baseline;
        abort_unless($baseline && $baseline->metrics()->exists(), 422, 'Add baseline metrics before approval.');
        abort_if($baseline->status === 'approved', 422, 'Baseline is already approved.');
        $baseline->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return $this->ok($baseline->load('metrics'), 'Baseline approved and permanently locked.');
    }

    public function dashboard(SeoCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        $keywords = $campaign->keywords()->with('latestRanking')->get();
        return $this->ok([
            'campaign' => $campaign, 'keywords_tracked' => $keywords->count(),
            'top_10_keywords' => $keywords->filter(fn ($keyword) => $keyword->latestRanking?->rank_position && $keyword->latestRanking->rank_position <= 10)->count(),
            'latest_keywords' => $keywords,
        ]);
    }

    public function workspace(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        $isClient = $this->isClient();
        $reports = $campaign->reports()->latest('period_end');
        if ($isClient) $reports->where('status', 'published');
        $latestPeriod = $campaign->performanceRecords()->max('period_end');
        $current = $latestPeriod ? $campaign->performanceRecords()->where('period_end', $latestPeriod)->pluck('metric_value', 'metric_key') : collect();
        $keywords = $campaign->keywords()->with('latestRanking')->get();

        return $this->ok([
            'campaign' => $campaign,
            'tab' => $isClient ? 'reports' : $request->string('tab', 'activities')->toString(),
            'is_client' => $isClient,
            'cycles' => $campaign->cycles()->latest('month')->get(),
            'activities' => $campaign->activities()->with('assignee')->latest()->get(),
            'technical_issues' => SeoTechnicalIssue::where('seo_campaign_id', $campaign->id)->with('assignee')->latest()->get(),
            'on_page_optimizations' => SeoOnPageOptimization::where('seo_campaign_id', $campaign->id)->latest('optimized_on')->get(),
            'backlinks' => SeoBacklinkActivity::where('seo_campaign_id', $campaign->id)->latest()->get(),
            'performance' => $campaign->performanceRecords()->latest('period_end')->get(),
            'reports' => $reports->get(),
            'client_overview' => [
                'baseline' => $campaign->baseline?->metrics()->pluck('metric_value', 'metric_key') ?? collect(),
                'current' => $current, 'period' => $latestPeriod,
                'keywords' => $keywords->count(),
                'top10' => $keywords->filter(fn ($keyword) => $keyword->latestRanking?->rank_position && $keyword->latestRanking->rank_position <= 10)->count(),
                'completed_activities' => $campaign->activities()->whereIn('status', ['approved', 'completed'])->count(),
            ],
        ]);
    }

    public function keywords(SeoCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        return $this->ok($campaign->keywords()->with(['rankings' => fn ($q) => $q->latest('checked_on')])->latest()->get());
    }

    public function storeKeyword(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate([
            'keyword' => 'required|string|max:255', 'target_page_url' => 'nullable|url|max:2048',
            'category' => 'nullable|string|max:100', 'search_intent' => ['nullable', Rule::in(['informational', 'navigational', 'commercial', 'transactional', 'local', 'other'])],
            'location' => 'nullable|string|max:150', 'device' => ['required', Rule::in(['desktop', 'mobile'])],
            'language' => 'nullable|string|max:20', 'search_engine' => 'nullable|string|max:50',
            'search_volume' => 'nullable|integer|min:0', 'keyword_difficulty' => 'nullable|numeric|min:0|max:100',
            'priority' => ['nullable', Rule::in(['high', 'medium', 'low'])], 'remarks' => 'nullable|string|max:5000',
            'initial_rank_position' => 'nullable|integer|min:1|max:10000', 'initial_checked_on' => 'nullable|date',
        ]);
        $initialRank = $data['initial_rank_position'] ?? null;
        $initialDate = $data['initial_checked_on'] ?? now()->toDateString();
        unset($data['initial_rank_position'], $data['initial_checked_on']);
        $data += ['normalized_keyword' => mb_strtolower(trim(preg_replace('/\s+/', ' ', $data['keyword']))), 'language' => 'en', 'search_engine' => 'google', 'priority' => 'medium', 'created_by' => auth()->id()];
        $existing = $campaign->keywords()->withTrashed()->where(['normalized_keyword' => $data['normalized_keyword'], 'location' => $data['location'] ?? null, 'device' => $data['device'], 'search_engine' => $data['search_engine']])->first();
        if ($existing && ! $existing->trashed()) return $this->error('This keyword already exists for the selected location, device and search engine.', 422);
        if ($existing) $existing->forceDelete();
        $keyword = $campaign->keywords()->create($data);
        if (! is_null($initialRank)) $keyword->rankings()->create(['checked_on' => $initialDate, 'rank_position' => $initialRank, 'source' => 'manual', 'recorded_by' => auth()->id()]);
        return $this->created($keyword->load('rankings'), 'Keyword saved.');
    }

    public function deleteKeyword(SeoCampaign $campaign, SeoKeyword $keyword)
    {
        $this->authorize('contribute', $campaign);
        abort_unless($keyword->seo_campaign_id === $campaign->id, 404);
        $keyword->forceDelete();
        return $this->message('Keyword permanently deleted.');
    }

    public function addRanking(Request $request, SeoCampaign $campaign, SeoKeyword $keyword)
    {
        $this->authorize('contribute', $campaign);
        abort_unless($keyword->seo_campaign_id === $campaign->id, 404);
        $data = $request->validate(['checked_on' => 'required|date', 'rank_position' => 'nullable|integer|min:1|max:10000', 'source' => 'required|string|max:100', 'remarks' => 'nullable|string|max:2000']);
        $ranking = $keyword->rankings()->updateOrCreate(['checked_on' => $data['checked_on'], 'source' => $data['source']], $data + ['recorded_by' => auth()->id()]);
        return $this->ok($ranking, 'Ranking snapshot saved.');
    }

    public function competitors(SeoCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        return $this->ok($campaign->competitors()->latest()->get());
    }

    public function storeCompetitor(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['name' => 'required|string|max:255', 'website_url' => 'required|url|max:512', 'services' => 'nullable|string|max:3000', 'strengths' => 'nullable|string|max:3000', 'weaknesses' => 'nullable|string|max:3000', 'opportunities' => 'nullable|string|max:3000', 'notes' => 'nullable|string|max:3000', 'data_source' => 'nullable|string|max:100']);
        return $this->created($campaign->competitors()->create($data + ['created_by' => auth()->id()]), 'Competitor added.');
    }

    public function addCycle(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('manage', $campaign);
        $data = $request->validate(['month' => 'required|date', 'objectives' => 'nullable|string|max:5000', 'status' => ['nullable', Rule::in(['draft', 'active', 'completed', 'closed'])]]);
        $data['month'] = date('Y-m-01', strtotime($data['month']));
        $cycle = $campaign->cycles()->updateOrCreate(['month' => $data['month']], $data + ['created_by' => auth()->id()]);
        return $this->ok($cycle, 'Monthly SEO cycle saved.');
    }

    public function addActivity(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['seo_monthly_cycle_id' => 'nullable|exists:seo_monthly_cycles,id', 'category' => ['required', Rule::in(['on_page', 'technical', 'content', 'off_page', 'local', 'analytics', 'reporting', 'other'])], 'title' => 'required|string|max:255', 'target_url' => 'nullable|url|max:2048', 'description' => 'nullable|string|max:5000', 'assigned_to' => 'nullable|exists:users,id', 'start_date' => 'nullable|date', 'deadline' => 'nullable|date', 'priority' => ['required', Rule::in(['high', 'medium', 'low'])]]);
        $this->ensureCycle($campaign, $data['seo_monthly_cycle_id'] ?? null);
        $this->ensureMember($campaign, $data['assigned_to'] ?? null);
        return $this->created($campaign->activities()->create($data), 'SEO activity assigned.');
    }

    public function updateActivity(Request $request, SeoCampaign $campaign, SeoActivity $activity)
    {
        $this->authorize('contribute', $campaign);
        abort_unless($activity->seo_campaign_id === $campaign->id, 404);
        $this->ensureOwner($campaign, $activity->assigned_to);
        abort_if(in_array($activity->status, ['approved', 'completed'], true), 422, 'Approved activities are locked.');
        $data = $request->validate(['status' => ['required', Rule::in(['todo', 'in_progress', 'submitted'])], 'work_notes' => 'nullable|string|max:5000', 'evidence_url' => 'nullable|url|max:2048']);
        $activity->update($data);
        return $this->ok($activity->fresh('assignee'), 'Activity progress saved.');
    }

    public function reviewActivity(Request $request, SeoCampaign $campaign, SeoActivity $activity)
    {
        $this->authorize('manage', $campaign);
        abort_unless($activity->seo_campaign_id === $campaign->id && $activity->status === 'submitted', 422, 'Only submitted activities can be reviewed.');
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_required'])], 'feedback' => 'nullable|string|max:5000']);
        $activity->update(['status' => $data['decision'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'review_feedback' => $data['feedback'] ?? null, 'completed_at' => $data['decision'] === 'approved' ? now() : null]);
        return $this->ok($activity, 'Activity review saved.');
    }

    public function addTechnical(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['title' => 'required|string|max:255', 'issue_type' => ['required', Rule::in(['crawl', 'indexing', 'sitemap', 'redirect', 'broken_link', 'canonical', 'speed', 'mobile', 'schema', 'core_web_vitals', 'other'])], 'affected_url' => 'nullable|url|max:2048', 'description' => 'nullable|string|max:5000', 'severity' => ['required', Rule::in(['critical', 'high', 'medium', 'low'])], 'assigned_to' => 'nullable|exists:users,id']);
        $this->ensureMember($campaign, $data['assigned_to'] ?? null);
        $issue = SeoTechnicalIssue::create($data + ['seo_campaign_id' => $campaign->id, 'status' => empty($data['assigned_to']) ? 'identified' : 'assigned']);
        return $this->created($issue->load('assignee'), 'Technical issue logged.');
    }

    public function updateTechnical(Request $request, SeoCampaign $campaign, SeoTechnicalIssue $technicalIssue)
    {
        $this->authorize('contribute', $campaign);
        abort_unless($technicalIssue->seo_campaign_id === $campaign->id, 404);
        $this->ensureOwner($campaign, $technicalIssue->assigned_to);
        $data = $request->validate(['status' => ['required', Rule::in(['identified', 'assigned', 'fix_in_progress', 'implemented', 'verification_pending', 'verified_closed', 'reopened'])], 'resolution_notes' => 'nullable|string|max:5000', 'evidence_url' => 'nullable|url|max:2048']);
        if ($data['status'] === 'verified_closed') $this->authorize('manage', $campaign);
        $technicalIssue->update($data + ($data['status'] === 'verified_closed' ? ['verified_at' => now(), 'verified_by' => auth()->id()] : []));
        return $this->ok($technicalIssue->fresh('assignee'), 'Technical issue progress updated.');
    }

    public function addOnPage(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['seo_activity_id' => 'nullable|exists:seo_activities,id', 'page_url' => 'required|url|max:2048', 'target_keyword' => 'nullable|string|max:255', 'old_meta_title' => 'nullable|string|max:1000', 'new_meta_title' => 'nullable|string|max:1000', 'old_meta_description' => 'nullable|string|max:2000', 'new_meta_description' => 'nullable|string|max:2000', 'h1_changes' => 'nullable|string|max:3000', 'content_changes' => 'nullable|string|max:5000', 'alt_tag_changes' => 'nullable|string|max:3000', 'internal_link_changes' => 'nullable|string|max:3000', 'evidence_url' => 'nullable|url|max:2048', 'optimized_on' => 'required|date']);
        if (! empty($data['seo_activity_id'])) abort_unless($campaign->activities()->whereKey($data['seo_activity_id'])->exists(), 422, 'Selected activity does not belong to this campaign.');
        return $this->created(SeoOnPageOptimization::create($data + ['seo_campaign_id' => $campaign->id, 'created_by' => auth()->id()]), 'On-page optimisation log saved.');
    }

    public function addBacklink(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['seo_monthly_cycle_id' => 'nullable|exists:seo_monthly_cycles,id', 'activity_type' => 'required|string|max:255', 'target_website' => 'nullable|url|max:2048', 'target_url' => 'nullable|url|max:2048', 'published_url' => 'nullable|url|max:2048', 'anchor_text' => 'nullable|string|max:255', 'link_type' => ['required', Rule::in(['dofollow', 'nofollow', 'sponsored', 'ugc', 'unknown'])], 'verification_status' => ['required', Rule::in(['submitted', 'published', 'verified', 'lost', 'rejected'])], 'submitted_on' => 'nullable|date', 'live_on' => 'nullable|date', 'remarks' => 'nullable|string|max:5000']);
        $this->ensureCycle($campaign, $data['seo_monthly_cycle_id'] ?? null);
        return $this->created(SeoBacklinkActivity::create($data + ['seo_campaign_id' => $campaign->id, 'assigned_to' => auth()->id()]), 'Off-page activity recorded.');
    }

    public function addPerformance(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['period_start' => 'required|date', 'period_end' => 'required|date|after_or_equal:period_start', 'source' => ['required', Rule::in(['manual', 'ga4', 'search_console', 'import'])], 'metric_key' => 'required|string|max:100', 'metric_value' => 'nullable|numeric', 'dimensions' => 'nullable|array']);
        $record = SeoPerformanceRecord::updateOrCreate(['seo_campaign_id' => $campaign->id, 'period_start' => $data['period_start'], 'period_end' => $data['period_end'], 'source' => $data['source'], 'metric_key' => $data['metric_key']], $data + ['recorded_by' => auth()->id(), 'recorded_at' => now()]);
        return $this->ok($record, 'Performance metric saved.');
    }

    public function createReport(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $data = $request->validate(['seo_monthly_cycle_id' => 'nullable|exists:seo_monthly_cycles,id', 'title' => 'required|string|max:255', 'period_start' => 'required|date', 'period_end' => 'required|date|after_or_equal:period_start', 'observations' => 'nullable|string|max:10000', 'attachments' => 'nullable|array|max:10', 'attachments.*' => 'file|mimes:xlsx,xls,psd|max:51200']);
        $this->ensureCycle($campaign, $data['seo_monthly_cycle_id'] ?? null);
        $files = $request->file('attachments', []);
        unset($data['attachments']);
        $report = $campaign->reports()->create($data + ['prepared_by' => auth()->id(), 'snapshot' => $this->snapshot($campaign, $data['period_start'], $data['period_end'])]);
        $attachments = [];
        foreach ($files as $file) {
            $path = $file->store("seo-reports/{$campaign->id}/{$report->id}", 'public');
            $attachments[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'type' => $file->getClientOriginalExtension(), 'size' => $file->getSize()];
        }
        $report->update(['attachments' => $attachments]);
        return $this->created($report->fresh(), 'SEO report draft created.');
    }

    public function submitReport(SeoCampaign $campaign, SeoReport $report)
    {
        $this->authorize('contribute', $campaign);
        abort_unless($report->seo_campaign_id === $campaign->id && in_array($report->status, ['draft', 'changes_required'], true), 422, 'Only draft or changes-required reports can be submitted.');
        $report->update(['status' => 'submitted']);
        return $this->ok($report, 'Report submitted for review.');
    }

    public function reviewReport(Request $request, SeoCampaign $campaign, SeoReport $report)
    {
        $this->authorize('manage', $campaign);
        abort_unless($report->seo_campaign_id === $campaign->id, 404);
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_required', 'published'])], 'feedback' => 'nullable|string|max:5000']);
        abort_if($data['decision'] === 'approved' && $report->status !== 'submitted', 422, 'Only submitted reports can be approved.');
        abort_if($data['decision'] === 'published' && $report->status !== 'approved', 422, 'Approve the report before publishing it.');
        $report->update(['status' => $data['decision'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'review_feedback' => $data['feedback'] ?? null]);
        return $this->ok($report, ucfirst(str_replace('_', ' ', $data['decision'])) . ' report saved.');
    }

    public function keywordTemplate(SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->fromArray(['Keyword', 'Target URL', 'Location', 'Initial Rank', 'Checked On'], null, 'A1');
        $sheet->fromArray([['Example keyword', 'https://example.com/page', 'Mumbai', 24, now()->toDateString()]], null, 'A2');
        return response()->streamDownload(function () use ($sheet) { (new Xlsx($sheet->getParent()))->save('php://output'); }, 'seo-keyword-import-template.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function importKeywords(Request $request, SeoCampaign $campaign)
    {
        $this->authorize('contribute', $campaign);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);
        try { $rows = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet()->toArray(null, true, true, false); }
        catch (\Throwable) { return $this->error('The uploaded Excel file could not be read.', 422); }
        $headers = array_map(fn ($value) => strtolower(trim((string) $value)), $rows[0] ?? []);
        if ($headers !== ['keyword', 'target url', 'location', 'initial rank', 'checked on']) return $this->error('Invalid template headers. Download and use the provided Excel template.', 422);
        $created = 0; $skipped = 0; $errors = [];
        DB::transaction(function () use ($rows, $campaign, &$created, &$skipped, &$errors) {
            foreach (array_slice($rows, 1) as $index => $row) {
                $line = $index + 2; $keyword = trim((string) ($row[0] ?? '')); if ($keyword === '') continue;
                $targetUrl = trim((string) ($row[1] ?? '')); $location = trim((string) ($row[2] ?? '')); $rank = ($row[3] ?? '') === '' ? null : $row[3]; $checkedOn = $row[4] ?? null;
                if ($targetUrl !== '' && ! filter_var($targetUrl, FILTER_VALIDATE_URL)) { $errors[] = "Row {$line}: Target URL is invalid."; continue; }
                if (! is_null($rank) && (! is_numeric($rank) || (int) $rank < 1)) { $errors[] = "Row {$line}: Initial Rank must be positive."; continue; }
                $checkedOn = $checkedOn
                    ? (is_numeric($checkedOn)
                        ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($checkedOn)->format('Y-m-d')
                        : date('Y-m-d', strtotime((string) $checkedOn)))
                    : now()->toDateString();
                $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $keyword)));
                $existing = $campaign->keywords()->withTrashed()->where(['normalized_keyword' => $normalized, 'location' => $location ?: null, 'device' => 'desktop', 'search_engine' => 'google'])->first();
                if ($existing && ! $existing->trashed()) { $skipped++; continue; }
                if ($existing) $existing->forceDelete();
                $newKeyword = $campaign->keywords()->create(['keyword' => $keyword, 'normalized_keyword' => $normalized, 'target_page_url' => $targetUrl ?: null, 'location' => $location ?: null, 'device' => 'desktop', 'priority' => 'medium', 'language' => 'en', 'search_engine' => 'google', 'created_by' => auth()->id()]);
                if (! is_null($rank)) $newKeyword->rankings()->create(['checked_on' => $checkedOn, 'rank_position' => (int) $rank, 'source' => 'Excel import', 'recorded_by' => auth()->id()]);
                $created++;
            }
        });
        return $this->ok(['created' => $created, 'skipped' => $skipped, 'errors' => array_slice($errors, 0, 20)], 'Keyword import completed.');
    }

    private function snapshot(SeoCampaign $campaign, string $start, string $end): array
    {
        $baseline = $campaign->baseline?->metrics()->pluck('metric_value', 'metric_key') ?? collect();
        $metrics = $campaign->performanceRecords()->whereBetween('period_end', [$start, $end])->orderBy('period_end')->get()->groupBy('metric_key')->map(fn ($rows) => $rows->last()->metric_value);
        $ranks = $campaign->keywords()->with('rankings')->get()->map(function ($keyword) {
            $rows = $keyword->rankings->sortBy('checked_on')->values(); $current = $rows->last()?->rank_position; $previous = $rows->count() > 1 ? $rows->slice(-2, 1)->first()?->rank_position : null;
            return ['keyword' => $keyword->keyword, 'current' => $current, 'change' => ! is_null($current) && ! is_null($previous) ? $previous - $current : null];
        });
        return ['generated_at' => now()->toIso8601String(), 'metrics' => $metrics, 'baseline' => $baseline, 'metric_change' => $metrics->map(fn ($v, $k) => is_numeric($v) && is_numeric($baseline->get($k)) ? $v - $baseline->get($k) : null), 'keywords_tracked' => $ranks->count(), 'top_10_keywords' => $ranks->filter(fn ($row) => $row['current'] && $row['current'] <= 10)->count(), 'gainers' => $ranks->filter(fn ($row) => ($row['change'] ?? 0) > 0)->sortByDesc('change')->take(5)->values(), 'decliners' => $ranks->filter(fn ($row) => ($row['change'] ?? 0) < 0)->sortBy('change')->take(5)->values(), 'activities_completed' => $campaign->activities()->whereIn('status', ['approved', 'completed'])->whereBetween('completed_at', [$start, $end . ' 23:59:59'])->count(), 'technical_closed' => SeoTechnicalIssue::where('seo_campaign_id', $campaign->id)->where('status', 'verified_closed')->whereBetween('verified_at', [$start, $end . ' 23:59:59'])->count(), 'backlinks_verified' => SeoBacklinkActivity::where('seo_campaign_id', $campaign->id)->where('verification_status', 'verified')->whereBetween('live_on', [$start, $end])->count()];
    }

    private function campaignData(Request $request): array
    {
        return $request->validate(['client_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['client', 'customer']))], 'name' => 'required|string|max:255', 'website_url' => 'required|url|max:2048', 'industry' => 'nullable|string|max:255', 'target_country' => 'nullable|string|max:100', 'target_locations' => 'nullable|string|max:2000', 'target_language' => 'required|string|max:20', 'search_engine' => 'required|string|max:50', 'devices' => 'nullable|array', 'devices.*' => Rule::in(['desktop', 'mobile']), 'start_date' => 'required|date', 'goals' => 'nullable|string|max:5000', 'reporting_frequency' => ['required', Rule::in(['weekly', 'monthly', 'quarterly'])], 'status' => ['required', Rule::in(['draft', 'active', 'on_hold', 'completed', 'cancelled'])], 'members' => 'required|array|min:1', 'members.*.user_id' => ['required', 'distinct', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'staff'))], 'members.*.campaign_role' => ['required', Rule::in(['manager', 'executive'])]]);
    }

    private function ensureMember(SeoCampaign $campaign, $userId): void { if ($userId) abort_unless($campaign->members()->where('user_id', $userId)->whereNull('unassigned_at')->exists(), 422, 'Assignee must be an active campaign member.'); }
    private function ensureCycle(SeoCampaign $campaign, $cycleId): void { if ($cycleId) abort_unless($campaign->cycles()->whereKey($cycleId)->exists(), 422, 'Selected month does not belong to this campaign.'); }
    private function ensureOwner(SeoCampaign $campaign, $assignedTo): void { if (! auth()->user()->can('manage', $campaign) && $assignedTo && (int) $assignedTo !== (int) auth()->id()) abort(403, 'Only the assigned executive or manager can update this work.'); }
    private function isClient(): bool { return in_array(strtolower((string) (auth()->user()?->getRawOriginal('role') ?? '')), ['client', 'customer'], true); }
    private function isAdmin(): bool
    {
        $user = auth()->user();
        if (! $user) return false;

        // Keep this in sync with SeoCampaignController so the API and web
        // campaign lists expose the same records to administrators.
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'super_admin', 'super admin', 'super_admin2', 'admin'])) {
            return true;
        }

        return in_array(strtolower(trim((string) ($user->getRawOriginal('role') ?? ''))), ['admin', 'super-admin', 'super_admin', 'super admin', 'super_admin2'], true);
    }
    private function requireAdmin(): void { abort_unless($this->isAdmin(), 403, 'Only administrators can manage SEO campaigns.'); }
    private function ok($data, string $message = null) { return response()->json(['success' => true, 'message' => $message, 'data' => $data]); }
    private function created($data, string $message) { return response()->json(['success' => true, 'message' => $message, 'data' => $data], 201); }
    private function message(string $message) { return response()->json(['success' => true, 'message' => $message]); }
    private function error(string $message, int $status) { return response()->json(['success' => false, 'message' => $message], $status); }
}
