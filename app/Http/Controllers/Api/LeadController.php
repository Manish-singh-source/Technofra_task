<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileUpload;
use App\Http\Controllers\Controller;
use App\Models\BookCall;
use App\Models\ClientBusinessDetail;
use App\Models\DigitalMarketingLead;
use App\Models\Lead;
use App\Models\MetaLead;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\WebappLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    //
    private const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'lost'];
    private const SOURCES = ['website', 'referral', 'social_media', 'cold_call', 'email_campaign', 'other'];
    private const TAGS = ['hot', 'warm', 'cold', 'urgent', 'follow-up', 'nurture'];

    /**
     * 
     * API: Get leads of todays only
     */
    public function dashboard()
    {
        // Leads
        $leads = Lead::select('id', 'name', 'email', 'phone', 'created_at')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get()
            ->each(function ($lead) {
                $lead->setAttribute('lead_type', 'leads');
            });

        $allLeads = Lead::count();

        // Book A Call
        // $bookCalls = BookCall::select('id', 'name', 'email', 'phone', 'created_at')
        //     ->whereDate('created_at', today())
        //     ->orderByDesc('created_at')
        //     ->get()
        //     ->each(function ($bookCall) {
        //         $bookCall->setAttribute('lead_type', 'book_call');
        //     });

        // $allBookCalls = BookCall::count();

        // Digital Marketing Leads
        $digitalMarketingLeads = DigitalMarketingLead::select('id', 'name', 'email', 'phone', 'created_at')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get()
            ->each(function ($digitalMarketingLead) {
                $digitalMarketingLead->setAttribute('lead_type', 'digital_marketing_lead');
            });

        $allDigitalMarketingLeads = DigitalMarketingLead::count();

        // Web App Leads

        $webAppLeads = WebappLead::select('id', 'name', 'email', 'phone', 'created_at')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get()
            ->each(function ($webAppLead) {
                $webAppLead->setAttribute('lead_type', 'web_app_lead');
            });

        $allWebAppLeads = WebappLead::count();

        // Meta Leads 
        $metaLeads = MetaLead::select('id', 'full_name as name', 'email', 'phone', 'created_at')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get()
            ->each(function ($metaLead) {
                $metaLead->setAttribute('lead_type', 'meta_lead');
            });

        $allMetaLeads = MetaLead::count();

        $leadsCount = ['todaysLeadsCount' => $leads->count(), 'allLeadsCount' => $allLeads];
        // $bookCallsCount = ['todaysBookCallsCount' => $bookCalls->count(), 'allBookCallsCount' => $allBookCalls];
        $digitalMarketingLeadsCount = ['todaysDigitalMarketingLeadsCount' => $digitalMarketingLeads->count(), 'allDigitalMarketingLeadsCount' => $allDigitalMarketingLeads];
        $webAppLeadsCount = ['todaysWebAppLeadsCount' => $webAppLeads->count(), 'allWebAppLeadsCount' => $allWebAppLeads];
        $metaLeadsCount = ['todaysMetaLeadsCount' => $metaLeads->count(), 'allMetaLeadsCount' => $allMetaLeads];

        $combinedLeads = $leads
            // ->concat($bookCalls)
            ->concat($digitalMarketingLeads)
            ->concat($webAppLeads)
            ->concat($metaLeads)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $combinedLeads,
            'leadsCount' => $leadsCount,
            // 'bookCallsCount' => $bookCallsCount,
            'digitalMarketingLeadsCount' => $digitalMarketingLeadsCount,
            'webAppLeadsCount' => $webAppLeadsCount,
            'metaLeadsCount' => $metaLeadsCount,
        ]);
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
                'staff' => User::staffMembers()
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
                    ->values(),
            ],
        ]);
    }

    /**
     * API: Get all leads.
     */
    public function apiIndex(Request $request)
    {
        $leads = Lead::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leads->map(fn(Lead $lead) => $this->formatLeadResource($lead)),
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

        if ($request->status == 'converted') {


            $client = User::where('email', $lead->email)->first();

            if (!$client) {
                $profileImagePath = basename(FileUpload::generateAvatar(
                    (string) ($lead->name ?? 'Client'),
                    'uploads/client/'
                ));

                // Create a new client
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
            'assigned.*' => [
                Rule::exists('users', 'id')->where(fn($query) => $query->where('role', '!=', 'client')->whereNotNull('role')),
            ],
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
