<?php

namespace App\Http\Controllers;

use App\Helpers\FileUpload;
use App\Exports\LeadsExport;
use App\Models\ClientBusinessDetail;
use App\Models\AssignedLead;
use App\Models\Lead;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\LeadManagement\LeadMobileNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    private const SOURCES = ['website', 'referral', 'social_media', 'cold_call', 'email_campaign', 'other'];
    private const TAGS = ['hot', 'warm', 'cold', 'urgent', 'follow-up', 'nurture'];

    /**
     * Display a listing of all leads.
     */
    public function index()
    {
        $leads = $this->visibleLeadQuery()->get();
        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');

        $allLeads = $leads->count();
        $newLeads = $leads->where('status', 'new')->count();
        $contactedLeads = $leads->where('status', 'contacted')->count();
        $qualifiedLeads = $leads->where('status', 'qualified')->count();
        $convertedLeads = $leads->where('status', 'converted')->count();
        $lostLeads = $leads->where('status', 'lost')->count();

        return view('leads', compact('leads', 'staff', 'allLeads', 'newLeads', 'contactedLeads', 'qualifiedLeads', 'convertedLeads', 'lostLeads'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        return view('add-lead', compact('staff'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validateLeadData($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $lead = null;
        DB::transaction(function () use ($request, &$lead) {
            $lead = Lead::create($this->buildLeadPayload($request));
            $this->syncAssignedLeads($lead, $request->assigned ?? []);
        });

        if ($lead) {
            app(LeadMobileNotificationService::class)->notifyLeadCreatedToAdmins($lead);
        }

        return redirect()->route('lead-management.index')->with('success', 'Lead created successfully!');
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit($id)
    {
        $lead = $this->findEditableLeadOrFail((int) $id);
        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        return view('edit-lead', compact('lead', 'staff'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, $id)
    {
        $lead = $this->findEditableLeadOrFail((int) $id);

        $validator = $this->validateLeadData($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($lead, $request) {
            $payload = $this->buildLeadPayload($request);
            unset($payload['created_by']);

            $lead->update($payload);
            $this->syncAssignedLeads($lead, $request->assigned ?? []);
        });

        if ($request->status == 'converted') {

            $client = User::where('email', $lead->email)->first();

            if (!$client) {
                $profileImagePath = basename(FileUpload::generateAvatar(
                    (string) ($lead->name ?? 'Client'),
                    'uploads/client/'
                ));

                $client = User::create([
                    'profile_image' => $profileImagePath,
                    'first_name' => $lead->name ?? '',
                    'last_name' => '',
                    'email' => $lead->email ?? '',
                    'phone' => $lead->phone ?? '',
                    'role' => 'client',
                    'password' => Hash::make('123456789'),
                    'status' => 'active',
                ]);

                if ($client) {
                    $address = UserAddress::create([
                        'user_id' => $client->id,
                        'address_line_1' => $lead->address ?? '',
                        'address_line_2' => '',
                        'city' => $lead->city ?? '',
                        'state' => $lead->state ?? '',
                        'country' => $lead->country ?? '',
                        'pincode' => $lead->zipCode ?? '',
                    ]);

                    $businessDetail = ClientBusinessDetail::create([
                        'user_id' => $client->id,
                        'client_type' => '',
                        'company_name' => $lead->company ?? '',
                        'industry' => '',
                        'website' => $lead->website ?? '',
                    ]);
                }
            }
        }


        return redirect()->route('lead-management.index')->with('success', 'Lead updated successfully!');
    }

    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        $lead = $this->findVisibleLeadOrFail((int) $id);
        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');
        return view('view-lead', compact('lead', 'staff'));
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy($id)
    {
        $lead = $this->findVisibleLeadOrFail((int) $id);
        $lead->delete();

        return redirect()->route('leads')->with('success', 'Lead deleted successfully!');
    }

    /**
     * Toggle lead status.
     */
    public function toggleStatus(Request $request)
    {
        $lead = $this->findVisibleLeadOrFail((int) $request->id);
        $lead->status = $request->status;
        $lead->save();

        return response()->json(['success' => true]);
    }

    /**
     * Delete selected leads.
     */
    public function deleteSelected(Request $request)
    {
        $leadIds = $request->ids;
        $this->visibleLeadQuery()->whereIn('id', $leadIds)->delete();

        return response()->json(['success' => true, 'message' => 'Selected leads deleted successfully!']);
    }

    /**
     * Export leads to Excel.
     */
    public function export()
    {
        return Excel::download(new LeadsExport((int) auth()->id()), 'leads_' . date('Y-m-d') . '.xlsx');
    }


    private function visibleLeadQuery()
    {
        $query = Lead::query();
        $userId = (int) (auth()->id() ?? 0);

        if ($this->isStaffUser()) {
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

        return $query->where('created_by', $userId);
    }

    private function findVisibleLeadOrFail(int $id): Lead
    {
        return $this->visibleLeadQuery()->where('id', $id)->firstOrFail();
    }

    private function findEditableLeadOrFail(int $id): Lead
    {
        $user = auth()->user();
        $role = strtolower((string) data_get($user, 'role', ''));
        $adminRoles = ['admin', 'super-admin', 'super_admin', 'super_admin2'];
        $hasAdminAccess = in_array($role, $adminRoles, true)
            || ($user && method_exists($user, 'hasAnyRole') && call_user_func([$user, 'hasAnyRole'], $adminRoles));
        $canEditAllLeads = ! $this->isStaffUser()
            && $role !== 'client'
            && $user
            && Gate::forUser($user)->allows('edit_leads');

        if ($hasAdminAccess || $canEditAllLeads) {
            return Lead::query()->findOrFail($id);
        }

        return $this->findVisibleLeadOrFail($id);
    }

    private function isStaffUser(): bool
    {
        $role = strtolower((string) data_get(auth()->user(), 'role', ''));

        return $role === 'staff';
    }
    private function validateLeadData(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zipCode' => 'nullable|string|max:20',
            'lead_value' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:100',
            'assigned' => 'nullable|array',
            'assigned.*' => [
                Rule::exists('users', 'id')->where(fn($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::in(collect(config('lead_statuses', []))->pluck('slug')->all())],
        ]);
    }

    private function buildLeadPayload(Request $request): array
    {
        return [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'position' => $request->position,
            'website' => $request->website,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'zipCode' => $request->zipCode,
            'lead_value' => $request->lead_value,
            'source' => $request->source,
            'tags' => $request->tags ?? [],
            'description' => $request->description,
            'status' => $request->status ?? 'new',
            'created_by' => auth()->id(),
        ];
    }

    private function syncAssignedLeads(Lead $lead, array $staffIds): void
    {
        $staffIds = collect($staffIds)
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();

        AssignedLead::updateOrCreate(
            ['lead_model' => 'lead', 'lead_id' => (int) $lead->id],
            ['staff_ids' => $staffIds]
        );
    }

    private function formatLeadResource(Lead $lead): array
    {
        $assignedStaff = User::staffMembers()
            ->whereIn('id', $lead->assigned ?? [])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn(User $member) => [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'full_name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                'email' => $member->email,
            ])
            ->values();

        return [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'position' => $lead->position,
            'website' => $lead->website,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country,
            'zipCode' => $lead->zipCode,
            'lead_value' => $lead->lead_value,
            'source' => $lead->source,
            'assigned' => $lead->assigned ?? [],
            'assigned_staff' => $assignedStaff,
            'tags' => $lead->tags ?? [],
            'description' => $lead->description,
            'status' => $lead->status,
            'created_at' => optional($lead->created_at)?->toISOString(),
            'updated_at' => optional($lead->updated_at)?->toISOString(),
            'links' => [
                'web' => [
                    'view' => route('lead.show', $lead->id),
                    'edit' => route('lead.edit', $lead->id),
                    'delete' => route('lead.destroy', $lead->id),
                ],
                'api' => [
                    'show' => url('/api/v1/leads/' . $lead->id),
                    'update' => url('/api/v1/leads/' . $lead->id),
                    'delete' => url('/api/v1/leads/' . $lead->id),
                ],
            ],
        ];
    }
}
