<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Staff;
use App\Exports\LeadsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'lost'];
    private const SOURCES = ['website', 'referral', 'social_media', 'cold_call', 'email_campaign', 'other'];
    private const TAGS = ['hot', 'warm', 'cold', 'urgent', 'follow-up', 'nurture'];

    /**
     * Display a listing of all leads.
     */
    public function index()
    {
        $leads = Lead::all();
        $staff = Staff::all()->keyBy('id');

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
        $staff = Staff::all();
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

        Lead::create($this->buildLeadPayload($request));

        return redirect()->route('leads')->with('success', 'Lead created successfully!');
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $staff = Staff::all();
        return view('edit-lead', compact('lead', 'staff'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = $this->validateLeadData($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $lead->update($this->buildLeadPayload($request));

        return redirect()->route('leads')->with('success', 'Lead updated successfully!');
    }

    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        $lead = Lead::findOrFail($id);
        $staff = Staff::all()->keyBy('id');
        return view('view-lead', compact('lead', 'staff'));
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads')->with('success', 'Lead deleted successfully!');
    }

    /**
     * Toggle lead status.
     */
    public function toggleStatus(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
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
        Lead::whereIn('id', $leadIds)->delete();

        return response()->json(['success' => true, 'message' => 'Selected leads deleted successfully!']);
    }

    /**
     * Export leads to Excel.
     */
    public function export()
    {
        return Excel::download(new LeadsExport, 'leads_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * API: Get lead form options.
     */
    public function apiFormOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'statuses' => self::STATUSES,
                // 'sources' => self::SOURCES,
                'tags' => self::TAGS,
                'staff' => Staff::query()
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get()
                    ->map(fn (Staff $member) => [
                        'id' => $member->id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'full_name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                        'email' => $member->email,
                    ])
                    ->values(),
            ],
        ]);
    }

    /**
     * API: Get all leads.
     */
    public function apiIndex()
    {
        $leads = Lead::query()->latest('id')->paginate();

        return response()->json([
            'success' => true,
            'data' => $leads->map(fn (Lead $lead) => $this->formatLeadResource($lead)),
        ]);
    }

    /**
     * API: Show a single lead.
     */
    public function apiShow($id)
    {
        $lead = Lead::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatLeadResource($lead),
        ]);
    }

    /**
     * API: Store a newly created lead.
     */
    public function apiStore(Request $request)
    {
        $validator = $this->validateLeadData($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $lead = Lead::create($this->buildLeadPayload($request));

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully.',
            'data' => $this->formatLeadResource($lead),
        ], 201);
    }

    /**
     * API: Update a lead.
     */
    public function apiUpdate(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $validator = $this->validateLeadData($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $lead->update($this->buildLeadPayload($request));

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully.',
            'data' => $this->formatLeadResource($lead->fresh()),
        ]);
    }

    /**
     * API: Delete a lead.
     */
    public function apiDestroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully.',
        ]);
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
            'assigned.*' => 'exists:staff,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'description' => 'nullable|string',
            'status' => 'nullable|in:' . implode(',', self::STATUSES),
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
            'assigned' => $request->assigned ?? [],
            'tags' => $request->tags ?? [],
            'description' => $request->description,
            'status' => $request->status ?? 'new',
        ];
    }

    private function formatLeadResource(Lead $lead): array
    {
        $assignedStaff = Staff::query()
            ->whereIn('id', $lead->assigned ?? [])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Staff $member) => [
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
