<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\LeadManagement\LogWebLeadFollowupRequest;
use App\Http\Requests\LeadManagement\AddLeadNoteRequest;
use App\Http\Requests\LeadManagement\AddLeadReminderRequest;
use App\Http\Requests\LeadManagement\AssignLeadRequest;
use App\Http\Requests\LeadManagement\BulkAssignLeadRequest;
use App\Http\Requests\LeadManagement\ConvertLeadRequest;
use App\Http\Requests\LeadManagement\EscalateLeadRequest;
use App\Http\Requests\LeadManagement\UpdateLeadStatusRequest;
use App\Actions\LeadManagement\UpdateLeadStatusAction;
use App\DTOs\LeadManagement\StatusUpdateData;
use App\Models\AssignedLead;
use App\Models\DigitalMarketingLead;
use App\Models\GoogleLead;
use App\Models\ContactForm;
use App\Models\EnquiryForm;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadConversion;
use App\Models\LeadEscalation;
use App\Models\LeadFollowup;
use App\Models\LeadNote;
use App\Models\LeadReminder;
use App\Models\StaffLeadStat;
use App\Models\MetaLead;
use App\Models\User;
use App\Models\WebappLead;
use App\Services\LeadManagement\LeadPipelineService;
use App\Services\LeadManagement\LeadMobileNotificationService;
use App\Services\LeadManagement\LeadClientConversionService;
use App\Services\LeadManagement\LeadStatusService;
use App\Services\LeadManagement\LeadWorklistService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeadManagementController extends \App\Http\Controllers\Controller
{
    public function __construct(
        private readonly LeadPipelineService $pipelineService,
        private readonly LeadStatusService $leadStatusService,
        private readonly LeadClientConversionService $leadClientConversionService,
        private readonly UpdateLeadStatusAction $updateLeadStatusAction
    ) {}

    private const STATUSES = ['new', 'attempted_contact', 'contacted', 'qualified', 'demo_scheduled', 'proposal_sent', 'negotiation', 'converted', 'lost', 'junk'];
    private const SOURCE_LEAD = 'lead';
    private const SOURCE_DIGITAL_MARKETING = 'digital_marketing';
    private const SOURCE_WEBAPP = 'webapp';
    private const SOURCE_META = 'meta';
    private const SOURCE_GOOGLE = 'google';
    private const SOURCE_CONTACTFORM = 'contactform';
    private const SOURCE_ENQUIRYFORM = 'enquiryform';
    private const SOURCE_INDIAMART = 'indiamart';
    private const SOURCE_JUSTDIAL = 'justdial';
    private const SOURCE_PIPELINE = 'lead';

    private ?Collection $journeyLeads = null;
    private ?Collection $sourceLinks = null;

    public function index(Request $request): View
    {
        abort_unless($this->canViewLeadManagement(), 403);

        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $sourceFilter = trim((string) $request->query('source', ''));
        $workFilter = (string) $request->query('work', '');
        if (! in_array($workFilter, ['overdue', 'today', 'upcoming', 'unscheduled', 'closed'], true)) {
            $workFilter = '';
        }
        $sourceLabels = [
            self::SOURCE_LEAD => 'Leads',
            self::SOURCE_CONTACTFORM => 'Contact Form',
            self::SOURCE_ENQUIRYFORM => 'Enquiry Form',
            self::SOURCE_META => 'Meta',
            self::SOURCE_DIGITAL_MARKETING => 'Digital Marketing',
            self::SOURCE_WEBAPP => 'Web App',
            self::SOURCE_GOOGLE => 'Google',
            self::SOURCE_JUSTDIAL => 'JustDial',
            self::SOURCE_INDIAMART => 'IndiaMart',
        ];
        if (! array_key_exists($sourceFilter, $sourceLabels)) {
            $sourceFilter = '';
        }

        $filteredBySearch = $this->withJourneyDetails($this->mergedLeads())
            ->when($search !== '', function (Collection $items) use ($search) {
                return $items->filter(function (array $row) use ($search) {
                    $needle = mb_strtolower($search);
                    $haystack = mb_strtolower(implode(' ', [
                        (string) ($row['name'] ?? ''),
                        (string) ($row['email'] ?? ''),
                        (string) ($row['number'] ?? ''),
                        (string) ($row['company'] ?? ''),
                        (string) ($row['source'] ?? ''),
                    ]));

                    return str_contains($haystack, $needle);
                });
            });

        $tabCounts = ['all' => $filteredBySearch->count()];
        foreach (array_keys($sourceLabels) as $sourceKey) {
            $tabCounts[$sourceKey] = $filteredBySearch
                ->where('source_type', $sourceKey)
                ->count();
        }

        $merged = $filteredBySearch
            ->when($sourceFilter !== '', fn (Collection $items) => $items->where('source_type', $sourceFilter))
            ->when($statusFilter !== '', function (Collection $items) use ($statusFilter) {
                return $items->filter(fn(array $row) => (string) ($row['status'] ?? '') === $statusFilter);
            })
            ->values();
        $worklist = app(LeadWorklistService::class);
        $workSummary = $worklist->summary($merged);
        $merged = $merged
            ->filter(fn (array $row) => $worklist->matchesWorkFilter($row, $workFilter))
            ->sortBy(fn (array $row) => [
                ['high' => 0, 'medium' => 1, 'low' => 2][$row['priority'] ?? 'medium'] ?? 1,
                ['overdue' => 0, 'today' => 1, 'unscheduled' => 2, 'upcoming' => 3, 'closed' => 4][$row['followup_state']],
                $row['next_followup_at']?->timestamp ?? -$row['created_at_ts'],
            ])
            ->values();

        // Lead management uses client-side DataTable features, so provide all filtered rows.
        $perPage = max($merged->count(), 1);
        $page = 1;
        $paginated = new LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('lead-management.index', [
            'leads' => $paginated,
            'staff' => $staff,
            'filters' => [
                'search' => $search,
                'source' => $sourceFilter,
                'status' => $statusFilter,
                'work' => $workFilter,
            ],
            'sources' => $sourceLabels,
            'tabCounts' => $tabCounts,
            'workSummary' => $workSummary,
            'statusOptions' => config('lead_statuses', []),
            'canManageLeadAssignments' => $this->canManageLeadAssignments(),
        ]);
    }

    public function show(string $source, int $id): View
    {
        abort_unless($this->canViewLeadManagement(), 403);
        $lead = $this->findNormalizedLeadOrFail($source, $id);
        $sourceRecord = $this->resolveSourceRecord($lead['source_type'], (int) $lead['source_id']);
        $leadModel = $this->resolveLeadEntityForPipeline($lead)->load('statusUpdatedBy');

        $timeline = $leadModel->activities()->with('user')->latest()->limit(100)->get();
        $followups = $leadModel->followups()->with('staff')->where('followup_date', '<=', now())->latest('followup_date')->latest('id')->get();
        $notes = $leadModel->notes()->where(function ($query) {
            $query->where('is_private', false)->orWhere('user_id', auth()->id());
            if ($this->hasAdminLevelLeadAccess()) {
                $query->orWhere('is_private', true);
            }
        })->latest()->get();
        $statusHistory = $leadModel->statusHistories()->with('changedBy')->latest()->limit(100)->get();
        $staff = User::staffMembers()->orderBy('first_name')->orderBy('last_name')->get();
        // History carries the original remarks, which the generic activity description omits.
        $timeline = $timeline->reject(fn (LeadActivity $activity) => in_array($activity->activity_type, ['status_changed', 'followup_added'], true))
            ->concat($followups->map(function (LeadFollowup $followup) {
                $activity = new LeadActivity([
                    'activity_type' => 'followup_added',
                    'description' => ucwords(str_replace('_', ' ', $followup->followup_type)) . ': '
                        . ucwords(str_replace('_', ' ', $followup->outcome ?: 'Activity logged'))
                        . ($followup->discussion_notes ? '. ' . $followup->discussion_notes : '.'),
                    'metadata' => ['next_followup_at' => $followup->next_followup_date?->toDateTimeString()],
                ]);
                $activity->created_at = $followup->followup_date;
                $activity->setRelation('user', $followup->staff);
                return $activity;
            }))
            ->concat($statusHistory->map(function ($history) {
                $activity = new LeadActivity([
                    'activity_type' => 'status_changed',
                    'description' => 'Stage changed from ' . ucwords(str_replace('_', ' ', $history->old_status ?: 'new'))
                        . ' to ' . ucwords(str_replace('_', ' ', $history->new_status))
                        . ($history->remarks ? '. ' . $history->remarks : '.'),
                ]);
                $activity->created_at = $history->changed_at ?? $history->created_at;
                $activity->setRelation('user', $history->changedBy);
                return $activity;
            }))->sortByDesc('created_at')->take(100)->values();
        $statusOptions = config('lead_statuses', []);
        $followupState = app(LeadWorklistService::class)->followupState($leadModel->next_followup_at, (string) $leadModel->status);
        $allowedStatuses = collect($statusOptions)->filter(function (array $option) use ($leadModel) {
            if (in_array((string) $leadModel->status, LeadWorklistService::CLOSED, true)) {
                return $option['slug'] === $leadModel->status;
            }
            try {
                $this->leadStatusService->validateTransition((string) $leadModel->status, $option['slug']);
                return true;
            } catch (ValidationException $exception) {
                return false;
            }
        })->values();

        return view('lead-management.show', compact(
            'lead',
            'timeline',
            'followups',
            'notes',
            'staff',
            'leadModel',
            'statusOptions',
            'sourceRecord',
            'followupState',
            'allowedStatuses'
        ));
    }

    public function updatePriority(Request $request, string $source, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->can('edit_leads'), 403);

        $validated = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $leadModel->forceFill(['priority' => $validated['priority']])->save();

        return back()->with('success', 'Lead priority updated successfully.');
    }

    public function assign(AssignLeadRequest $request, string $source, int $id): RedirectResponse
    {
        abort_unless($this->canManageLeadAssignments(), 403);

        $validated = $request->validated();

        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $existingAssignment = AssignedLead::query()
            ->where('lead_model', $normalized['source_type'])
            ->where('lead_id', (int) $normalized['source_id'])
            ->first();

        $existingStaffIds = collect($existingAssignment?->staff_ids ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        $assigned = $existingStaffIds
            ->map(fn($value) => (int) $value)
            ->merge(collect($validated['assigned_user_ids'])->map(fn($value) => (int) $value))
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($normalized, $assigned, $validated) {
            AssignedLead::updateOrCreate(
                [
                    'lead_model' => $normalized['source_type'],
                    'lead_id' => (int) $normalized['source_id'],
                ],
                [
                    'staff_ids' => $assigned,
                ]
            );

            $leadModel = $this->resolveLeadEntityForPipeline($normalized);
            // Keep staff assignment in sync for the pipeline mirror record in `leads`,
            // since analytics KPIs/charts rely on pipeline fields (status/followups/etc).
            $this->syncPipelineAssignment($leadModel, $assigned);
            if (! empty($assigned)) {
                $this->pipelineService->assignLead(
                    $leadModel,
                    (int) $assigned[0],
                    auth()->id(),
                    $validated['assignment_note'] ?? null
                );
            }
        });

        $newlyAssigned = collect($assigned)->diff($existingStaffIds)->values()->all();
        if (! empty($newlyAssigned)) {
            $leadModel = $this->resolveLeadEntityForPipeline($normalized);
            app(LeadMobileNotificationService::class)->notifyLeadAssignedToStaff($leadModel, $newlyAssigned);
        }

        return redirect()
            ->route('lead-management.index')
            ->with('success', 'Lead assigned successfully.');
    }

    public function bulkAssign(BulkAssignLeadRequest $request): RedirectResponse
    {
        abort_unless($this->canManageLeadAssignments(), 403);

        $validated = $request->validated();

        $selectedStaffIds = collect($validated['assigned_user_ids'])
            ->map(fn($value) => (int) $value)
            ->unique()
            ->values();
        $assignedCount = 0;

        foreach ($validated['selected_leads'] as $selectedLead) {
            $source = (string) $selectedLead['source'];
            $id = (int) $selectedLead['id'];

            // Ensure the selected pair is a valid visible lead source/id.
            $normalized = $this->findNormalizedLeadOrFail($source, $id);

            $existingAssignment = AssignedLead::query()
                ->where('lead_model', $source)
                ->where('lead_id', $id)
                ->first();

            $assigned = collect($existingAssignment?->staff_ids ?? [])
                ->map(fn($value) => (int) $value)
                ->merge($selectedStaffIds)
                ->unique()
                ->values()
                ->all();

            $existingStaffIds = collect($existingAssignment?->staff_ids ?? [])
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->unique()
                ->values();

            AssignedLead::updateOrCreate(
                [
                    'lead_model' => $source,
                    'lead_id' => $id,
                ],
                [
                    'staff_ids' => $assigned,
                ]
            );

            $leadModel = $this->resolveLeadEntityForPipeline($normalized);
            $this->syncPipelineAssignment($leadModel, $assigned);
            if (! empty($assigned)) {
                $this->pipelineService->assignLead($leadModel, (int) $assigned[0], auth()->id(), 'Bulk assignment');
            }

            $newlyAssigned = collect($assigned)->diff($existingStaffIds)->values()->all();
            if (! empty($newlyAssigned)) {
                app(LeadMobileNotificationService::class)->notifyLeadAssignedToStaff($leadModel, $newlyAssigned);
            }

            $assignedCount++;
        }

        return redirect()
            ->route('lead-management.index')
            ->with('success', $assignedCount . ' lead(s) assigned successfully.');
    }

    public function deleteSelected(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('delete_leads'), 403);

        $validated = $request->validate([
            'selected_leads' => ['required', 'array', 'min:1'],
            'selected_leads.*.source' => [
                'required',
                'string',
                Rule::in([
                    self::SOURCE_LEAD,
                    self::SOURCE_DIGITAL_MARKETING,
                    self::SOURCE_WEBAPP,
                    self::SOURCE_META,
                    self::SOURCE_GOOGLE,
                    self::SOURCE_CONTACTFORM,
                    self::SOURCE_ENQUIRYFORM,
                ]),
            ],
            'selected_leads.*.id' => ['required', 'integer', 'min:1'],
        ]);

        $selectedLeads = collect($validated['selected_leads'])
            ->unique(fn(array $lead) => $lead['source'] . ':' . (int) $lead['id'])
            ->values();

        DB::transaction(function () use ($selectedLeads) {
            foreach ($selectedLeads as $selectedLead) {
                $source = (string) $selectedLead['source'];
                $id = (int) $selectedLead['id'];

                $this->findNormalizedLeadOrFail($source, $id);
                $this->deleteBySource($source, $id);
            }
        });

        return redirect()
            ->route('lead-management.index')
            ->with('success', $selectedLeads->count() . ' lead(s) deleted successfully.');
    }

    public function destroy(string $source, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->can('delete_leads'), 403);

        $this->findNormalizedLeadOrFail($source, $id);
        $this->deleteBySource($source, $id);

        return redirect()
            ->route('lead-management.index')
            ->with('success', 'Lead deleted successfully.');
    }

    public function addFollowup(LogWebLeadFollowupRequest $request, string $source, int $id): RedirectResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validated();

        if (in_array((string) $leadModel->status, LeadWorklistService::CLOSED, true)) {
            throw ValidationException::withMessages(['followup_date' => 'This lead is closed. Its activity history is available below.']);
        }
        $nextStatus = $validated['lead_status_after_followup'] ?? $leadModel->status;
        if (($validated['outcome'] === 'converted' && $nextStatus !== 'converted')
            || ($validated['outcome'] === 'lost' && $nextStatus !== 'lost')) {
            throw ValidationException::withMessages(['lead_status_after_followup' => 'Choose the matching lead status for a won or lost outcome.']);
        }
        if (in_array($validated['outcome'], ['callback_later', 'meeting_scheduled'], true)
            && empty($validated['next_followup_date']) && ! in_array($nextStatus, LeadWorklistService::CLOSED, true)) {
            throw ValidationException::withMessages(['next_followup_date' => 'Choose the next follow-up time for a callback or meeting.']);
        }
        $this->validateWebStatusChange($leadModel, (string) $nextStatus);

        DB::transaction(function () use ($leadModel, $validated, $normalized) {
            $sourceType = strtolower(trim((string) ($normalized['source_type'] ?? self::SOURCE_LEAD)));
            $sourceId = (int) ($normalized['source_id'] ?? 0);

            $followup = LeadFollowup::query()->create([
                'lead_id' => $leadModel->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId > 0 ? $sourceId : null,
                'staff_id' => auth()->id(),
                'followup_date' => Carbon::parse($validated['followup_date']),
                'followup_type' => $validated['followup_type'],
                'outcome' => $validated['outcome'] ?? null,
                'discussion_notes' => $validated['discussion_notes'] ?? null,
                'next_followup_date' => ! empty($validated['next_followup_date']) ? Carbon::parse($validated['next_followup_date']) : null,
                'lead_status_after_followup' => $validated['lead_status_after_followup'] ?? null,
                'reminder_sent' => false,
            ]);

            if (in_array($validated['lead_status_after_followup'] ?? '', LeadWorklistService::CLOSED, true)) {
                $leadModel->next_followup_at = null;
            } elseif (! empty($validated['next_followup_date'])) {
                $leadModel->next_followup_at = Carbon::parse($validated['next_followup_date']);
            } elseif (! empty($validated['complete_scheduled_followup'])) {
                $leadModel->next_followup_at = null;
            }
            if (! empty($validated['lead_status_after_followup']) && $validated['lead_status_after_followup'] !== $leadModel->status) {
                $sourceRecord = $this->resolveSourceRecord($normalized['source_type'], (int) $normalized['source_id']);
                $this->updateLeadStatusAction->handle($sourceRecord, $leadModel, new StatusUpdateData(
                    status: $validated['lead_status_after_followup'],
                    remarks: $validated['discussion_notes'],
                    lostReason: $validated['lost_reason'] ?? null,
                    wonValue: isset($validated['won_value']) ? (float) $validated['won_value'] : null,
                    actorId: auth()->id()
                ));
            }
            $leadModel->save();

            $this->pipelineService->logActivity(
                $leadModel->id,
                auth()->id(),
                'followup_added',
                ucwords(str_replace('_', ' ', $validated['followup_type'])) . ': ' . str_replace('_', ' ', $validated['outcome']) . '. ' . $validated['discussion_notes'],
                [
                    'followup_id' => $followup->id,
                    'followup_type' => $followup->followup_type,
                    'source_type' => $followup->source_type,
                    'source_id' => $followup->source_id,
                    'next_followup_at' => $leadModel->next_followup_at?->toDateTimeString(),
                ]
            );

        });

        $staffIds = $this->resolveLeadStaffIdsForNotification($normalized, $leadModel);
        if (! empty($staffIds) && ! in_array((string) $leadModel->status, LeadWorklistService::CLOSED, true)) {
            $followupAt = Carbon::parse($validated['followup_date']);
            app(LeadMobileNotificationService::class)->notifyFollowupCreatedToStaff($leadModel, $staffIds, $followupAt);

            if (! empty($validated['next_followup_date']) && config('queue.default') !== 'sync') {
                app(LeadMobileNotificationService::class)->scheduleFollowupReminderPushes(
                    $leadModel,
                    $staffIds,
                    Carbon::parse($validated['next_followup_date'])
                );
            }
        }

        return back()->with('success', 'Followup added successfully.');
    }

    public function followupHistory(string $source, int $id): View
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $followups = $leadModel->followups()->with('staff')->where('followup_date', '<=', now())->latest('followup_date')->latest('id')->paginate(20);

        return view('lead-management.followups', compact('leadModel', 'followups', 'normalized'));
    }

    public function scheduleFollowup(Request $request, string $source, int $id): RedirectResponse
    {
        abort_unless($request->user()?->can('edit_leads'), 403);
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validate([
            'next_followup_at' => ['required', 'date', 'after:now'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        if (in_array((string) $leadModel->status, LeadWorklistService::CLOSED, true)) {
            throw ValidationException::withMessages(['next_followup_at' => 'A closed lead cannot have a pending follow-up.']);
        }
        DB::transaction(function () use ($leadModel, $validated) {
            $previous = $leadModel->next_followup_at?->format('d M Y h:i A');
            $leadModel->next_followup_at = Carbon::parse($validated['next_followup_at']);
            $leadModel->save();
            $this->pipelineService->logActivity($leadModel->id, auth()->id(), 'followup_scheduled',
                ($previous ? 'Rescheduled from ' . $previous . '. ' : '') . $validated['reason'], [
                    'previous_followup_at' => $previous,
                    'next_followup_at' => $leadModel->next_followup_at->toDateTimeString(),
                ]);
        });

        return back()->with('success', 'Next follow-up scheduled.');
    }

    public function addNote(AddLeadNoteRequest $request, string $source, int $id): RedirectResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validated();

        $note = LeadNote::query()->create([
            'lead_id' => $leadModel->id,
            'user_id' => auth()->id(),
            'note' => $validated['note'],
            'is_private' => (bool) ($validated['is_private'] ?? false),
        ]);

        $this->pipelineService->logActivity($leadModel->id, auth()->id(), 'note_added', 'Lead note added.', ['note_id' => $note->id]);

        return back()->with('success', 'Note added successfully.');
    }

    public function addReminder(AddLeadReminderRequest $request, string $source, int $id): RedirectResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validated();

        $this->pipelineService->createReminder(
            $leadModel->id,
            auth()->id(),
            Carbon::parse($validated['remind_at']),
            $validated['reminder_type']
        );

        return back()->with('success', 'Reminder created successfully.');
    }

    public function activityTimeline(string $source, int $id): View
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $activities = $leadModel->activities()->latest()->paginate(50);

        return view('lead-management.timeline', compact('leadModel', 'activities', 'normalized'));
    }

    public function convertLead(ConvertLeadRequest $request, string $source, int $id): RedirectResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validated();

        DB::transaction(function () use ($leadModel, $validated) {
            $clientUser = $this->leadClientConversionService->ensureClientFromLead($leadModel);
            LeadConversion::query()->create([
                'lead_id' => $leadModel->id,
                'client_id' => $validated['client_id'] ?? $clientUser->id,
                'converted_by' => auth()->id(),
                'conversion_value' => $validated['conversion_value'] ?? null,
                'converted_at' => now(),
            ]);

            $oldStatus = (string) ($leadModel->status ?? '');
            $leadModel->status = 'converted';
            $leadModel->converted_at = now();
            $leadModel->save();

            $this->pipelineService->logStatusChange($leadModel, $oldStatus, 'converted', auth()->id(), 'Lead converted');
            $this->pipelineService->logActivity($leadModel->id, auth()->id(), 'lead_converted', 'Lead converted successfully.');
        });

        return back()->with('success', 'Lead converted successfully.');
    }

    public function escalateLead(EscalateLeadRequest $request, string $source, int $id): RedirectResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validated();

        $escalation = LeadEscalation::query()->create([
            'lead_id' => $leadModel->id,
            'escalated_from' => auth()->id(),
            'escalated_to' => (int) $validated['escalated_to'],
            'reason' => $validated['reason'] ?? null,
            'escalated_at' => now(),
        ]);

        $this->pipelineService->logActivity($leadModel->id, auth()->id(), 'lead_escalated', 'Lead escalated.', ['escalation_id' => $escalation->id]);

        return back()->with('success', 'Lead escalated successfully.');
    }

    public function performanceStats(): View
    {
        abort_unless($this->canViewLeadManagement(), 403);

        $totalLeads = Lead::query()->count();
        $convertedLeads = Lead::query()->whereIn('status', ['converted', 'won'])->count();
        $lostLeads = Lead::query()->whereIn('status', ['lost', 'junk'])->count();
        $pendingFollowups = Lead::query()->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now())->count();
        $todayFollowups = LeadFollowup::query()->whereDate('followup_date', now()->toDateString())->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;
        $lostRate = $totalLeads > 0 ? round(($lostLeads / $totalLeads) * 100, 2) : 0;

        $staffStats = StaffLeadStat::query()->with('staff')->orderByDesc('conversion_rate')->get();
        $leadsPerStatus = Lead::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $monthlyWonLeads = Lead::query()->whereIn('status', ['converted', 'won'])->whereYear('updated_at', now()->year)->count();
        $monthlyLostLeads = Lead::query()->where('status', 'lost')->whereYear('updated_at', now()->year)->count();

        return view('lead-management.performance', compact(
            'totalLeads',
            'convertedLeads',
            'lostLeads',
            'pendingFollowups',
            'todayFollowups',
            'conversionRate',
            'lostRate',
            'staffStats',
            'leadsPerStatus',
            'monthlyWonLeads',
            'monthlyLostLeads'
        ));
    }

    public function updateStatus(UpdateLeadStatusRequest $request, string $source, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $normalized = $this->findNormalizedLeadOrFail($source, $id);

        $lead = match ($source) {
            self::SOURCE_LEAD => Lead::findOrFail($id),
            self::SOURCE_DIGITAL_MARKETING => DigitalMarketingLead::findOrFail($id),
            self::SOURCE_WEBAPP => WebappLead::findOrFail($id),
            self::SOURCE_META => MetaLead::findOrFail($id),
            self::SOURCE_GOOGLE => GoogleLead::findOrFail($id),
            self::SOURCE_CONTACTFORM => ContactForm::query()->where('id', $id)->whereNull('deleted_at')->firstOrFail(),
            self::SOURCE_ENQUIRYFORM => EnquiryForm::findOrFail($id),
            default => abort(404),
        };

        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $this->validateWebStatusChange($leadModel, (string) $validated['status']);
        if ($validated['status'] === $leadModel->status) {
            return back()->with('success', 'This lead is already in the selected stage.');
        }

        $result = $this->updateLeadStatusAction->handle(
            $lead,
            $leadModel,
            new StatusUpdateData(
                status: (string) $validated['status'],
                remarks: $validated['remarks'] ?? null,
                lostReason: $validated['lost_reason'] ?? null,
                wonValue: isset($validated['won_value']) ? (float) $validated['won_value'] : null,
                actorId: auth()->id()
            )
        );

        if (in_array((string) $leadModel->status, LeadWorklistService::CLOSED, true)) {
            $leadModel->forceFill(['next_followup_at' => null])->save();
        }

        if (($result['status'] ?? null) === 'converted' && !empty($result['client_user_id'])) {
            return redirect()
                ->route('client.edit', $result['client_user_id'])
                ->with('success', 'Lead converted and client created successfully. Please complete client details.');
        }

        return redirect()->route('lead-management.show', ['source' => $source, 'id' => $id])->with('success', 'Lead status updated and journey recorded.');
    }

    private function resolveSourceRecord(string $source, int $id): ?Model
    {
        return match ($source) {
            self::SOURCE_LEAD => Lead::find($id),
            self::SOURCE_DIGITAL_MARKETING => DigitalMarketingLead::find($id),
            self::SOURCE_WEBAPP => WebappLead::find($id),
            self::SOURCE_META => MetaLead::find($id),
            self::SOURCE_GOOGLE => GoogleLead::find($id),
            self::SOURCE_CONTACTFORM => ContactForm::query()->where('id', $id)->whereNull('deleted_at')->first(),
            self::SOURCE_ENQUIRYFORM => EnquiryForm::find($id),
            default => null,
        };
    }
    private function resolveLeadEntityForPipeline(array $normalized): Lead
    {
        if ($normalized['source_type'] === self::SOURCE_LEAD) {
            return Lead::query()->findOrFail((int) $normalized['source_id']);
        }

        $existing = $this->findJourneyLead($normalized);
        if ($existing) {
            return $existing;
        }
        $email = trim((string) ($normalized['email'] ?? ''));
        $phone = trim((string) ($normalized['number'] ?? ''));
        $created = Lead::query()->create([
            'name' => $normalized['name'] ?? 'Lead',
            'email' => $email !== '-' ? $email : null,
            'phone' => $phone !== '-' ? $phone : null,
            'company' => ($normalized['company'] ?? '-') !== '-' ? $normalized['company'] : null,
            'company_name' => ($normalized['company'] ?? '-') !== '-' ? $normalized['company'] : null,
            'source' => $normalized['source'] ?? 'Leads',
            'status' => $normalized['status'] ?? 'new',
            'created_by' => auth()->id(),
        ]);
        $link = $this->pipelineService->logActivity($created->id, auth()->id(), 'source_linked', 'Lead journey started.', [
            'source_type' => $normalized['source_type'],
            'source_id' => (int) $normalized['source_id'],
        ]);
        $this->journeyLeads?->put($created->id, $created);
        $this->sourceLinks?->put($normalized['source_type'] . ':' . $normalized['source_id'], $link->lead_id);

        return $created;
    }

    private function findJourneyLead(array $row): ?Lead
    {
        $this->journeyLeads ??= Lead::query()->get()->keyBy('id');
        $this->sourceLinks ??= LeadActivity::query()->where('activity_type', 'source_linked')->orderBy('id')->get()
            ->mapWithKeys(fn (LeadActivity $activity) => [
                ($activity->metadata['source_type'] ?? '') . ':' . ($activity->metadata['source_id'] ?? '') => $activity->lead_id,
            ]);
        if ($row['source_type'] === self::SOURCE_LEAD) {
            return $this->journeyLeads->get((int) $row['source_id']);
        }
        $linkedId = $this->sourceLinks->get($row['source_type'] . ':' . $row['source_id']);
        if ($linkedId) {
            return $this->journeyLeads->get((int) $linkedId);
        }
        $email = $this->normalizeContact((string) ($row['email'] ?? ''));
        $phone = $this->normalizeContact((string) ($row['number'] ?? ''));
        if ($email === '' && $phone === '') {
            return null;
        }
        $matches = $this->journeyLeads->filter(function (Lead $lead) use ($row, $email, $phone) {
            $sameSource = in_array(strtolower((string) $lead->source), [strtolower((string) $row['source']), strtolower($row['source_type'])], true);
            return $sameSource
                && ($email === '' || $this->normalizeContact((string) $lead->email) === $email)
                && ($phone === '' || $this->normalizeContact((string) $lead->phone) === $phone);
        });

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function withJourneyDetails(Collection $rows): Collection
    {
        $models = $rows->mapWithKeys(fn (array $row) => [$row['source_type'] . ':' . $row['source_id'] => $this->findJourneyLead($row)]);
        $ids = $models->filter()->pluck('id')->unique()->all();
        $latestFollowups = LeadFollowup::query()->with('staff')->whereIn('lead_id', $ids)->where('followup_date', '<=', now())
            ->orderByDesc('followup_date')->orderByDesc('id')->get()->unique('lead_id')->keyBy('lead_id');

        return $rows->map(function (array $row) use ($models, $latestFollowups) {
            $model = $models->get($row['source_type'] . ':' . $row['source_id']);
            $last = $model ? $latestFollowups->get($model->id) : null;
            $row['status'] = $model?->status ?: $row['status'];
            $row['next_followup_at'] = $model?->next_followup_at;
            $row['followup_state'] = app(LeadWorklistService::class)->followupState($row['next_followup_at'], $row['status']);
            $row['last_followup'] = $last;
            $row['lost_reason'] = $model?->lost_reason;
            $row['priority'] = $model?->priority ?: 'medium';

            return $row;
        });
    }

    private function validateWebStatusChange(Lead $lead, string $status): void
    {
        if (in_array((string) $lead->status, LeadWorklistService::CLOSED, true) && $status !== $lead->status) {
            throw ValidationException::withMessages(['status' => 'This lead is already closed. Its existing journey is preserved.']);
        }
        $this->leadStatusService->validateTransition((string) $lead->status, $status);
        if ($status === 'converted' && filled($lead->email)) {
            $existing = User::query()->where('email', $lead->email)->first();
            if ($existing && strtolower((string) $existing->getRawOriginal('role')) !== 'client') {
                throw ValidationException::withMessages(['status' => 'This email belongs to an existing team account. Use a customer email before converting this lead.']);
            }
        }
    }

    private function syncPipelineAssignment(Lead $leadModel, array $staffIds): void
    {
        $staffIds = collect($staffIds)
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        AssignedLead::updateOrCreate(
            [
                'lead_model' => self::SOURCE_PIPELINE,
                'lead_id' => (int) $leadModel->id,
            ],
            [
                'staff_ids' => $staffIds,
            ]
        );
    }

    private function mergedLeads(): Collection
    {
        $digital = DigitalMarketingLead::query()->get()->map(function (DigitalMarketingLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_DIGITAL_MARKETING,
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->company,
                'Digital Marketing',
                $lead->created_at,
                $lead->status
            );
        });

        $webapp = WebappLead::query()->get()->map(function (WebappLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_WEBAPP,
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->company,
                'Web App',
                $lead->created_at,
                $lead->status
            );
        });

        $meta = $this->visibleMetaLeadQuery()->get()->map(function (MetaLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_META,
                $lead->id,
                $lead->full_name,
                $lead->email,
                $lead->phone,
                null,
                'Meta',
                $lead->created_time,
                $lead->status
            );
        });

        $google = GoogleLead::query()->get()->map(function (GoogleLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_GOOGLE,
                $lead->id,
                $lead->full_name,
                $lead->email,
                $lead->phone,
                $lead->company,
                'Google',
                $lead->created_at ?? $lead->lead_submit_time,
                $lead->status
            );
        });

        $contactForm = ContactForm::query()
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($contact) {
                $name = trim(implode(' ', array_filter([
                    (string) ($contact->fname ?? ''),
                    (string) ($contact->lname ?? ''),
                ])));

                return $this->normalizeRow(
                    self::SOURCE_CONTACTFORM,
                    (int) $contact->id,
                    $name !== '' ? $name : null,
                    $contact->email ?? null,
                    $contact->contact ?? null,
                    null,
                    'Contact Form',
                    $contact->created_at ?? null,
                    $contact->status ?? 'new'
                );
            });

        $enquiryForm = EnquiryForm::query()
            ->get()
            ->map(function (EnquiryForm $enquiry) {
                return $this->normalizeRow(
                    self::SOURCE_ENQUIRYFORM,
                    (int) $enquiry->id,
                    $enquiry->name ?? null,
                    $enquiry->email ?? null,
                    $enquiry->contact ?? null,
                    $enquiry->company ?? null,
                    'Enquiry Form',
                    $enquiry->created_at ?? null,
                    $enquiry->status ?? 'new'
                );
            });

        // Staff assignment for non-Lead sources creates helper rows in `leads`.
        // Exclude those mirror rows from the listing so entries don't appear doubled.
        $digitalContactKeys = $this->buildContactKeysFromRows($digital);
        $webappContactKeys = $this->buildContactKeysFromRows($webapp);
        $metaContactKeys = $this->buildContactKeysFromRows($meta);
        $googleContactKeys = $this->buildContactKeysFromRows($google);
        $contactFormContactKeys = $this->buildContactKeysFromRows($contactForm);
        $enquiryFormContactKeys = $this->buildContactKeysFromRows($enquiryForm);

        $leads = Lead::query()->get()
            ->reject(function (Lead $lead) use (
                $digitalContactKeys,
                $webappContactKeys,
                $metaContactKeys,
                $googleContactKeys,
                $contactFormContactKeys,
                $enquiryFormContactKeys
            ) {
                $source = trim((string) ($lead->source ?? ''));
                $emailKey = $this->normalizeContact((string) ($lead->email ?? ''));
                $phoneKey = $this->normalizeContact((string) ($lead->phone ?? ''));

                return match ($source) {
                    'Digital Marketing' => $this->hasAnyContactMatch($emailKey, $phoneKey, $digitalContactKeys),
                    'Web App' => $this->hasAnyContactMatch($emailKey, $phoneKey, $webappContactKeys),
                    'Meta' => $this->hasAnyContactMatch($emailKey, $phoneKey, $metaContactKeys),
                    'Google' => $this->hasAnyContactMatch($emailKey, $phoneKey, $googleContactKeys),
                    'Contact Form' => $this->hasAnyContactMatch($emailKey, $phoneKey, $contactFormContactKeys),
                    'Enquiry Form' => $this->hasAnyContactMatch($emailKey, $phoneKey, $enquiryFormContactKeys),
                    default => false,
                };
            })
            ->values()
            ->map(function (Lead $lead) {
                return $this->normalizeRow(
                    self::SOURCE_LEAD,
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->company,
                    $lead->source ?: 'Leads',
                    $lead->created_at,
                    $lead->status
                );
            });

        return $leads
            ->concat($digital)
            ->concat($webapp)
            ->concat($meta)
            ->concat($google)
            ->concat($contactForm)
            ->concat($enquiryForm)
            ->filter(fn(array $row) => $this->canAccessNormalizedRow($row))
            ->values();
    }

    private function buildContactKeysFromRows(Collection $rows): array
    {
        $emails = $rows
            ->pluck('email')
            ->map(fn($value) => $this->normalizeContact((string) $value))
            ->filter()
            ->values()
            ->all();

        $phones = $rows
            ->pluck('number')
            ->map(fn($value) => $this->normalizeContact((string) $value))
            ->filter()
            ->values()
            ->all();

        return [
            'emails' => array_fill_keys($emails, true),
            'phones' => array_fill_keys($phones, true),
        ];
    }

    private function hasAnyContactMatch(string $emailKey, string $phoneKey, array $contactKeys): bool
    {
        return ($emailKey !== '' && isset($contactKeys['emails'][$emailKey]))
            || ($phoneKey !== '' && isset($contactKeys['phones'][$phoneKey]));
    }

    private function normalizeContact(string $value): string
    {
        $value = trim($value);

        if ($value === '' || $value === '-') {
            return '';
        }

        return mb_strtolower($value);
    }

    private function normalizeRow(
        string $sourceType,
        int $id,
        ?string $name,
        ?string $email,
        ?string $number,
        ?string $company,
        ?string $source,
        mixed $createdAt,
        ?string $status = null
    ): array {
        $date = $createdAt ? Carbon::parse($createdAt) : null;

        return [
            'source_type' => $sourceType,
            'source_id' => $id,
            'name' => $name ?: '-',
            'email' => $email ?: '-',
            'number' => $number ?: '-',
            'company' => $company ?: '-',
            'source' => $source ?: '-',
            'assigned_staff' => $assignedStaff = $this->resolveAssignedStaffNames($sourceType, $id),
            'assigned_to' => !empty($assignedStaff) ? implode(', ', $assignedStaff) : '-',
            'created_at' => $date?->format('d M Y h:i A') ?: '-',
            'created_at_ts' => $date?->timestamp ?: 0,
            'status' => $status ?: 'new',
            'priority' => 'medium',
        ];
    }

    private function resolveAssignedStaffNames(string $sourceType, int $id): array
    {
        $assignment = AssignedLead::query()
            ->where('lead_model', $sourceType)
            ->where('lead_id', $id)
            ->first();

        if (! $assignment || empty($assignment->staff_ids) || ! is_array($assignment->staff_ids)) {
            return [];
        }

        return User::staffMembers()
            ->whereIn('id', $assignment->staff_ids)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn(User $user) => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveAssignedTo(string $sourceType, int $id, ?string $email, ?string $number): string
    {
        $names = $this->resolveAssignedStaffNames($sourceType, $id);

        return !empty($names) ? implode(', ', $names) : '-';
    }

    private function findNormalizedLeadOrFail(string $source, int $id): array
    {
        $matched = $this->mergedLeads()->first(function (array $row) use ($source, $id) {
            return $row['source_type'] === $source && (int) $row['source_id'] === $id;
        });

        abort_unless((bool) $matched, 404);

        return $matched;
    }

    private function resolveLeadStaffIdsForNotification(array $normalized, Lead $leadModel): array
    {
        $sourceAssigned = AssignedLead::query()
            ->where('lead_model', (string) ($normalized['source_type'] ?? 'lead'))
            ->where('lead_id', (int) ($normalized['source_id'] ?? 0))
            ->first();

        $sourceStaffIds = collect($sourceAssigned?->staff_ids ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0);

        $pipelineAssigned = AssignedLead::query()
            ->where('lead_model', 'lead')
            ->where('lead_id', (int) $leadModel->id)
            ->first();

        $pipelineStaffIds = collect($pipelineAssigned?->staff_ids ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0);

        return $sourceStaffIds
            ->merge($pipelineStaffIds)
            ->unique()
            ->values()
            ->all();
    }

    private function canAccessNormalizedRow(array $row): bool
    {
        $sourceType = (string) ($row['source_type'] ?? '');
        $sourceId = (int) ($row['source_id'] ?? 0);
        $userId = (int) (auth()->id() ?? 0);

        if ($sourceId <= 0 || $userId <= 0) {
            return false;
        }

        if ($sourceType === self::SOURCE_META) {
            return $this->canAccessMetaLeadRow($sourceId, $userId);
        }

        if ($this->hasAdminLevelLeadAccess()) {
            return true;
        }

        if ($this->isStaffUser()) {
            return $this->hasAssignedLeadAccess($sourceType, $sourceId, $userId);
        }

        if ($this->isClientUser()) {
            return false;
        }

        if ($sourceType === self::SOURCE_LEAD) {
            return Lead::query()
                ->where('id', $sourceId)
                ->where('created_by', $userId)
                ->exists();
        }

        return false;
    }

    private function visibleMetaLeadQuery()
    {
        $userId = (int) (auth()->id() ?? 0);
        $query = MetaLead::query();

        if ($this->isStaffUser() && ! $this->hasAdminLevelLeadAccess()) {
            return $query->whereExists(function ($subQuery) use ($userId) {
                $subQuery->selectRaw('1')
                    ->from('assigned_leads')
                    ->where('assigned_leads.lead_model', self::SOURCE_META)
                    ->whereColumn('assigned_leads.lead_id', 'meta_leads.id')
                    ->where(function ($jsonQuery) use ($userId) {
                        $jsonQuery
                            ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                            ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                    });
            });
        }

        return $query->whereHas('users', fn($userQuery) => $userQuery->where('users.id', $userId));
    }

    private function canAccessMetaLeadRow(int $sourceId, int $userId): bool
    {
        if ($this->isStaffUser() && ! $this->hasAdminLevelLeadAccess()) {
            return $this->hasAssignedLeadAccess(self::SOURCE_META, $sourceId, $userId);
        }

        return $this->hasMetaLeadPivotAccess($sourceId, $userId);
    }

    private function hasMetaLeadPivotAccess(int $sourceId, int $userId): bool
    {
        return DB::table('meta_lead_user')
            ->where('user_id', $userId)
            ->where('meta_lead_id', $sourceId)
            ->exists();
    }

    private function visiblePipelineLeadQuery()
    {
        $query = Lead::query();
        $userId = (int) (auth()->id() ?? 0);

        if ($this->hasAdminLevelLeadAccess()) {
            return $query;
        }

        if ($this->isStaffUser()) {
            return $query->whereExists(function ($subQuery) use ($userId) {
                $subQuery->selectRaw('1')
                    ->from('assigned_leads')
                    ->where('assigned_leads.lead_model', self::SOURCE_LEAD)
                    ->whereColumn('assigned_leads.lead_id', 'leads.id')
                    ->where(function ($jsonQuery) use ($userId) {
                        $jsonQuery
                            ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                            ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                    });
            });
        }

        if ($this->isClientUser()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('created_by', $userId);
    }

    private function hasAssignedLeadAccess(string $sourceType, int $sourceId, int $userId): bool
    {
        return AssignedLead::query()
            ->where('lead_model', $sourceType)
            ->where('lead_id', $sourceId)
            ->where(function ($jsonQuery) use ($userId) {
                $jsonQuery
                    ->whereRaw('JSON_CONTAINS(staff_ids, ?, "$")', [(string) $userId])
                    ->orWhereRaw('JSON_CONTAINS(staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
            })
            ->exists();
    }

    private function isStaffUser(): bool
    {
        $role = strtolower((string) (auth()->user()?->getRawOriginal('role') ?? auth()->user()?->role ?? ''));

        return $role === 'staff';
    }

    private function isClientUser(): bool
    {
        $role = strtolower((string) (auth()->user()?->getRawOriginal('role') ?? auth()->user()?->role ?? ''));

        return $role === 'client';
    }

    private function canViewLeadManagement(): bool
    {
        return $this->hasAdminLevelLeadAccess()
            || (bool) auth()->user()?->can('view_leads_management');
    }

    private function canManageLeadAssignments(): bool
    {
        return $this->hasAdminLevelLeadAccess()
            || (! $this->isStaffUser() && ! $this->isClientUser() && (bool) auth()->user()?->can('edit_leads'));
    }

    private function hasAdminLevelLeadAccess(): bool
    {
        $user = auth()->user();
        $role = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));
        $adminRoles = ['admin', 'super-admin', 'super_admin', 'super_admin2'];

        if (in_array($role, $adminRoles, true)) {
            return true;
        }

        return method_exists($user, 'hasAnyRole') && $user->hasAnyRole($adminRoles);
    }

    private function deleteBySource(string $source, int $id): void
    {
        match ($source) {
            self::SOURCE_LEAD => Lead::findOrFail($id)->delete(),
            self::SOURCE_DIGITAL_MARKETING => DigitalMarketingLead::findOrFail($id)->delete(),
            self::SOURCE_WEBAPP => WebappLead::findOrFail($id)->delete(),
            self::SOURCE_META => MetaLead::findOrFail($id)->delete(),
            self::SOURCE_GOOGLE => GoogleLead::findOrFail($id)->delete(),
            self::SOURCE_CONTACTFORM => ContactForm::query()->where('id', $id)->whereNull('deleted_at')->delete(),
            self::SOURCE_ENQUIRYFORM => EnquiryForm::findOrFail($id)->delete(),
            default => abort(404),
        };
    }
}
