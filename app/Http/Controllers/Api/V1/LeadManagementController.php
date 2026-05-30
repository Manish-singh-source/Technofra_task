<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeadManagement\AddFollowupRequest;
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
use App\Models\Lead;
use App\Models\LeadConversion;
use App\Models\LeadEscalation;
use App\Models\LeadFollowup;
use App\Models\LeadNote;
use App\Models\StaffLeadStat;
use App\Models\MetaLead;
use App\Models\User;
use App\Models\WebappLead;
use App\Services\LeadManagement\LeadPipelineService;
use App\Services\LeadManagement\LeadMobileNotificationService;
use App\Services\LeadManagement\LeadClientConversionService;
use App\Services\LeadManagement\LeadStatusService;
use App\Http\Resources\Api\V1\LeadManagement\LeadSummaryResource;
use App\Http\Resources\Api\V1\LeadManagement\LeadDetailResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeadManagementController extends \App\Http\Controllers\Controller
{
    public function __construct(
        private readonly LeadPipelineService $pipelineService,
        private readonly LeadStatusService $leadStatusService,
        private readonly LeadClientConversionService $leadClientConversionService,
        private readonly UpdateLeadStatusAction $updateLeadStatusAction
    ) {
    }

    private const SOURCE_LEAD = 'lead';
    private const SOURCE_DIGITAL_MARKETING = 'digital_marketing';
    private const SOURCE_WEBAPP = 'webapp';
    private const SOURCE_META = 'meta';
    private const SOURCE_GOOGLE = 'google';
    private const SOURCE_CONTACTFORM = 'contactform';
    private const SOURCE_INDIAMART = 'indiamart';
    private const SOURCE_JUSTDIAL = 'justdial';
    private const SOURCE_PIPELINE = 'lead';

    public function index(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('view_leads'), 403);

        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $sourceLabels = [
            self::SOURCE_LEAD => 'Leads',
            self::SOURCE_DIGITAL_MARKETING => 'Digital Marketing',
            self::SOURCE_WEBAPP => 'Web App',
            self::SOURCE_META => 'Meta',
            self::SOURCE_GOOGLE => 'Google',
            self::SOURCE_CONTACTFORM => 'Contact Form',
            self::SOURCE_INDIAMART => 'IndiaMart',
            self::SOURCE_JUSTDIAL => 'JustDial',
        ];

        $filteredBySearch = $this->mergedLeads()
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
            ->when($statusFilter !== '', function (Collection $items) use ($statusFilter) {
                return $items->filter(fn (array $row) => (string) ($row['status'] ?? '') === $statusFilter);
            })
            ->sortByDesc('created_at_ts')
            ->values();

        $perPage = max($merged->count(), 1);
        $paginated = new LengthAwarePaginator(
            $merged->forPage(1, $perPage)->values(),
            $merged->count(),
            $perPage,
            1,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $staff = User::staffMembers()->orderBy('first_name')->orderBy('last_name')->get();

        return ApiResponse::success([
            'leads' => $paginated,
            'lead_items' => LeadSummaryResource::collection(collect($paginated->items())),
            'pagination' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
            'staff' => $staff,
            'filters' => [
                'search' => $search,
                'source' => '',
                'status' => $statusFilter,
            ],
            'sources' => $sourceLabels,
            'tabCounts' => $tabCounts,
            'statusOptions' => config('lead_statuses', []),
        ], 'Lead list fetched successfully.');
    }

    public function show(string $source, int $id): JsonResponse
    {
        abort_unless(auth()->user()?->can('view_leads'), 403);

        $lead = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($lead)->load('statusUpdatedBy');

        return ApiResponse::success([
            'lead' => $lead,
            'leadModel' => $leadModel,
            'lead_resource' => new LeadDetailResource($leadModel),
            'timeline' => $leadModel->activities()->latest()->limit(100)->get(),
            'followups' => $leadModel->followups()->latest('followup_date')->get(),
            'notes' => $leadModel->notes()->latest()->get(),
            'reminders' => $leadModel->reminders()->latest('remind_at')->get(),
            'assignments' => $leadModel->assignments()->latest('assigned_at')->get(),
            'statusHistory' => $leadModel->statusHistories()->latest()->get(),
            'staff' => User::staffMembers()->orderBy('first_name')->orderBy('last_name')->get(),
            'statusOptions' => config('lead_statuses', []),
        ], 'Lead details fetched successfully.');
    }

    public function listAssignments(string $source, int $id): JsonResponse
    {
        abort_unless(auth()->user()?->can('view_leads'), 403);

        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $assignments = $leadModel->assignments()->latest('assigned_at')->get();

        return ApiResponse::success([
            'lead' => $normalized,
            'lead_id' => $leadModel->id,
            'assignments' => $assignments,
        ], 'Lead assignments fetched successfully.');
    }


    public function assign(AssignLeadRequest $request, string $source, int $id): JsonResponse
    {
        abort_unless(auth()->user()?->can('edit_leads'), 403);

        $validated = $request->validated();
        $normalized = $this->findNormalizedLeadOrFail($source, $id);

        $existingAssignment = AssignedLead::query()
            ->where('lead_model', $normalized['source_type'])
            ->where('lead_id', (int) $normalized['source_id'])
            ->first();

        $existingStaffIds = collect($existingAssignment?->staff_ids ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values();

        $assigned = $existingStaffIds
            ->map(fn ($value) => (int) $value)
            ->merge(collect($validated['assigned_user_ids'])->map(fn ($value) => (int) $value))
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

        return ApiResponse::success(['assigned_user_ids' => $assigned], 'Lead assigned successfully.');
    }

    public function bulkAssign(BulkAssignLeadRequest $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('edit_leads'), 403);

        $validated = $request->validated();

        $selectedStaffIds = collect($validated['assigned_user_ids'])
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $assignedCount = 0;

        foreach ($validated['selected_leads'] as $selectedLead) {
            $source = (string) $selectedLead['source'];
            $id = (int) $selectedLead['id'];
            $this->findNormalizedLeadOrFail($source, $id);

            $existingAssignment = AssignedLead::query()
                ->where('lead_model', $source)
                ->where('lead_id', $id)
                ->first();

            $assigned = collect($existingAssignment?->staff_ids ?? [])
                ->map(fn ($value) => (int) $value)
                ->merge($selectedStaffIds)
                ->unique()
                ->values()
                ->all();

            $existingStaffIds = collect($existingAssignment?->staff_ids ?? [])
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0)
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

            $leadModel = $this->resolveLeadEntityForPipeline([
                'source_type' => $source,
                'source_id' => $id,
                'name' => null,
                'email' => null,
                'number' => null,
                'company' => null,
                'source' => ucfirst(str_replace('_', ' ', $source)),
                'status' => 'new',
            ]);

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

        return ApiResponse::success(['assigned_count' => $assignedCount], $assignedCount . ' lead(s) assigned successfully.');
    }

    public function statusHistory(string $source, int $id): JsonResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $statusHistory = $leadModel->statusHistories()->latest()->get();

        return ApiResponse::success([
            'lead' => $normalized,
            'lead_id' => $leadModel->id,
            'status_history' => $statusHistory,
        ], 'Lead status history fetched successfully.');
    }

    public function updateStatus(UpdateLeadStatusRequest $request, string $source, int $id): JsonResponse
    {
        $validated = $request->validated();

        $lead = match ($source) {
            self::SOURCE_LEAD => Lead::findOrFail($id),
            self::SOURCE_DIGITAL_MARKETING => DigitalMarketingLead::findOrFail($id),
            self::SOURCE_WEBAPP => WebappLead::findOrFail($id),
            self::SOURCE_META => MetaLead::findOrFail($id),
            self::SOURCE_GOOGLE => GoogleLead::findOrFail($id),
            self::SOURCE_CONTACTFORM => ContactForm::query()->where('id', $id)->whereNull('deleted_at')->firstOrFail(),
            default => abort(404),
        };

        $leadModel = $this->resolveLeadEntityForPipeline([
            'source_type' => $source,
            'source_id' => $id,
            'name' => $lead->name ?? $lead->full_name ?? null,
            'email' => $lead->email ?? null,
            'number' => $lead->phone ?? null,
            'company' => $lead->company ?? null,
            'source' => $source,
        ]);

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

        return ApiResponse::success([
            'status' => $result['status'],
            'client_user_id' => $result['client_user_id'],
        ], 'Lead status updated successfully.');
    }

    public function addFollowup(AddFollowupRequest $request, string $source, int $id): JsonResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $validated = $request->validated();

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

            if (! empty($validated['next_followup_date'])) {
                $leadModel->next_followup_at = Carbon::parse($validated['next_followup_date']);
            }
            if (! empty($validated['lead_status_after_followup'])) {
                $oldStatus = (string) ($leadModel->status ?? '');
                $leadModel->status = $validated['lead_status_after_followup'];
                $this->pipelineService->logStatusChange($leadModel, $oldStatus, $validated['lead_status_after_followup'], auth()->id(), 'Updated via followup');
            }
            $leadModel->save();

            $this->pipelineService->logActivity(
                $leadModel->id,
                auth()->id(),
                'followup_added',
                'Followup added for lead.',
                [
                    'followup_id' => $followup->id,
                    'followup_type' => $followup->followup_type,
                    'source_type' => $followup->source_type,
                    'source_id' => $followup->source_id,
                ]
            );

            if (! empty($validated['create_reminder']) && ! empty($validated['next_followup_date'])) {
                $this->pipelineService->createReminder(
                    $leadModel->id,
                    auth()->id(),
                    Carbon::parse($validated['next_followup_date']),
                    $validated['reminder_type'] ?? 'dashboard'
                );
            }
        });

        $staffIds = $this->resolveLeadStaffIdsForNotification($normalized, $leadModel);
        if (! empty($staffIds)) {
            $followupAt = Carbon::parse($validated['followup_date']);
            app(LeadMobileNotificationService::class)->notifyFollowupCreatedToStaff($leadModel, $staffIds, $followupAt);

            if (! empty($validated['next_followup_date'])) {
                app(LeadMobileNotificationService::class)->scheduleFollowupReminderPushes(
                    $leadModel,
                    $staffIds,
                    Carbon::parse($validated['next_followup_date'])
                );
            }
        }

        return ApiResponse::success(null, 'Followup added successfully.');
    }

    public function followupHistory(string $source, int $id): JsonResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $followups = $leadModel->followups()->latest('followup_date')->paginate(20);

        return ApiResponse::success([
            'lead' => $normalized,
            'lead_id' => $leadModel->id,
            'followups' => $followups,
        ], 'Followup history fetched successfully.');
    }

    public function listNotes(string $source, int $id): JsonResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $notes = $leadModel->notes()->latest()->paginate(20);

        return ApiResponse::success([
            'lead' => $normalized,
            'lead_id' => $leadModel->id,
            'notes' => $notes,
        ], 'Lead notes fetched successfully.');
    }

    public function addNote(AddLeadNoteRequest $request, string $source, int $id): JsonResponse
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

        return ApiResponse::success(['note' => $note], 'Note added successfully.');
    }

    public function listReminders(string $source, int $id): JsonResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $reminders = $leadModel->reminders()->latest('remind_at')->paginate(20);

        return ApiResponse::success([
            'lead' => $normalized,
            'lead_id' => $leadModel->id,
            'reminders' => $reminders,
        ], 'Lead reminders fetched successfully.');
    }

    public function addReminder(AddLeadReminderRequest $request, string $source, int $id): JsonResponse
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

        return ApiResponse::success(null, 'Reminder created successfully.');
    }

    public function activityTimeline(string $source, int $id): JsonResponse
    {
        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $leadModel = $this->resolveLeadEntityForPipeline($normalized);
        $activities = $leadModel->activities()->latest()->paginate(50);

        return ApiResponse::success([
            'lead' => $normalized,
            'lead_id' => $leadModel->id,
            'activities' => $activities,
        ], 'Activity timeline fetched successfully.');
    }

    public function convertLead(ConvertLeadRequest $request, string $source, int $id): JsonResponse
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

        return ApiResponse::success(['lead_id' => $leadModel->id, 'status' => 'converted'], 'Lead converted successfully.');
    }

    public function escalateLead(EscalateLeadRequest $request, string $source, int $id): JsonResponse
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

        return ApiResponse::success(['escalation' => $escalation], 'Lead escalated successfully.');
    }

    public function performanceStats(): JsonResponse
    {
        abort_unless(auth()->user()?->can('view_leads'), 403);

        $totalLeads = Lead::query()->count();
        $convertedLeads = Lead::query()->whereIn('status', ['converted', 'won'])->count();
        $lostLeads = Lead::query()->whereIn('status', ['lost', 'junk'])->count();
        $pendingFollowups = Lead::query()->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now())->count();
        $todayFollowups = LeadFollowup::query()->whereDate('followup_date', now()->toDateString())->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;
        $lostRate = $totalLeads > 0 ? round(($lostLeads / $totalLeads) * 100, 2) : 0;

        return ApiResponse::success([
            'totalLeads' => $totalLeads,
            'convertedLeads' => $convertedLeads,
            'lostLeads' => $lostLeads,
            'pendingFollowups' => $pendingFollowups,
            'todayFollowups' => $todayFollowups,
            'conversionRate' => $conversionRate,
            'lostRate' => $lostRate,
            'staffStats' => StaffLeadStat::query()->with('staff')->orderByDesc('conversion_rate')->get(),
            'leadsPerStatus' => Lead::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'monthlyWonLeads' => Lead::query()->whereIn('status', ['converted', 'won'])->whereYear('updated_at', now()->year)->count(),
            'monthlyLostLeads' => Lead::query()->where('status', 'lost')->whereYear('updated_at', now()->year)->count(),
        ], 'Performance stats fetched successfully.');
    }

    public function destroy(string $source, int $id): JsonResponse
    {
        abort_unless(auth()->user()?->can('delete_leads'), 403);

        $this->deleteBySource($source, $id);

        return ApiResponse::success(['source' => $source, 'id' => $id], 'Lead deleted successfully.');
    }

    private function resolveLeadEntityForPipeline(array $normalized): Lead
    {
        if ($normalized['source_type'] === self::SOURCE_LEAD) {
            return Lead::query()->findOrFail((int) $normalized['source_id']);
        }

        $email = trim((string) ($normalized['email'] ?? ''));
        $phone = trim((string) ($normalized['number'] ?? ''));

        $existing = Lead::query()
            ->when($email !== '' && $email !== '-', fn ($q) => $q->where('email', $email))
            ->when($phone !== '' && $phone !== '-', fn ($q) => $q->orWhere('phone', $phone))
            ->first();

        if ($existing) {
            return $existing;
        }

        return Lead::query()->create([
            'name' => $normalized['name'] ?? 'Lead',
            'email' => $email !== '-' ? $email : null,
            'phone' => $phone !== '-' ? $phone : null,
            'company' => ($normalized['company'] ?? '-') !== '-' ? $normalized['company'] : null,
            'company_name' => ($normalized['company'] ?? '-') !== '-' ? $normalized['company'] : null,
            'source' => $normalized['source'] ?? 'Leads',
            'status' => $normalized['status'] ?? 'new',
            'created_by' => auth()->id(),
        ]);
    }

    private function syncPipelineAssignment(Lead $leadModel, array $staffIds): void
    {
        $staffIds = collect($staffIds)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
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

        $meta = MetaLead::query()->get()->map(function (MetaLead $lead) {
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
                    null
                );
            });

        $digitalContactKeys = $this->buildContactKeysFromRows($digital);
        $webappContactKeys = $this->buildContactKeysFromRows($webapp);
        $metaContactKeys = $this->buildContactKeysFromRows($meta);
        $googleContactKeys = $this->buildContactKeysFromRows($google);
        $contactFormContactKeys = $this->buildContactKeysFromRows($contactForm);

        $leads = Lead::query()->get()
            ->reject(function (Lead $lead) use (
                $digitalContactKeys,
                $webappContactKeys,
                $metaContactKeys,
                $googleContactKeys,
                $contactFormContactKeys
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

        return $leads->concat($digital)->concat($webapp)->concat($meta)->concat($google)->concat($contactForm);
    }

    private function buildContactKeysFromRows(Collection $rows): array
    {
        $emails = $rows->pluck('email')
            ->map(fn ($value) => $this->normalizeContact((string) $value))
            ->filter()
            ->values()
            ->all();

        $phones = $rows->pluck('number')
            ->map(fn ($value) => $this->normalizeContact((string) $value))
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
            'assigned_to' => $this->resolveAssignedTo($sourceType, $id),
            'created_at' => $date?->format('d M Y h:i A') ?: '-',
            'created_at_ts' => $date?->timestamp ?: 0,
            'status' => $status ?: 'new',
        ];
    }

    private function resolveAssignedTo(string $sourceType, int $id): string
    {
        $assignment = AssignedLead::query()
            ->where('lead_model', $sourceType)
            ->where('lead_id', $id)
            ->first();

        if (! $assignment || empty($assignment->staff_ids) || ! is_array($assignment->staff_ids)) {
            return '-';
        }

        $names = User::staffMembers()
            ->whereIn('id', $assignment->staff_ids)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user) => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')))
            ->filter()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : '-';
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
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0);

        $pipelineAssigned = AssignedLead::query()
            ->where('lead_model', 'lead')
            ->where('lead_id', (int) $leadModel->id)
            ->first();

        $pipelineStaffIds = collect($pipelineAssigned?->staff_ids ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0);

        return $sourceStaffIds
            ->merge($pipelineStaffIds)
            ->unique()
            ->values()
            ->all();
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
            default => abort(404),
        };
    }
}

