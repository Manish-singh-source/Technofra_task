<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\RenewalStatusHelper;
use App\Http\Controllers\Controller;
use App\Models\AmcService;
use App\Models\AmcServiceDetail;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientRenewalController extends Controller
{
    private const AVAILABLE_TABS = ['all', 'upcoming', 'active', 'inactive', 'pending', 'expired'];

    private const PLAN_TYPES = [
        'monthly',
        'yearly',
        'quarterly',
        'half_year',
    ];

    private function canViewAll($user): bool
    {
        return $user && ($user->hasRole('admin') || $user->hasRole('super_admin2') || $user->hasRole('super_admin'));
    }

    private function scopedQuery($user)
    {
        $query = Service::query()->whereNotNull('client_id');

        if (! $this->canViewAll($user)) {
            $query->where('client_id', $user->id);
        }

        return $query;
    }

    public function apiFormOptions()
    {
        return ApiResponse::success([
            'amc_visit_statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'completed', 'label' => 'Completed'],
            ],
            'plan_types' => array_map(
                fn (string $planType) => [
                    'value' => $planType,
                    'label' => ucwords(str_replace('_', ' ', $planType)),
                ],
                self::PLAN_TYPES
            ),
            'clients' => User::with('companies:id,user_id,client_type,company_name,industry,website')
                ->where('role', 'client')
                ->select('id', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn ($client) => [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'company_names' => $client->companies
                        ->pluck('company_name')
                        ->filter()
                        ->values(),
                    'companies' => $client->companies
                        ->map(fn ($company) => [
                            'id' => $company->id,
                            'client_type' => $company->client_type,
                            'company_name' => $company->company_name,
                            'industry' => $company->industry,
                            'website' => $company->website,
                        ])
                        ->values(),
                ]),
            'vendors' => Vendor::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request)
    {
        RenewalStatusHelper::markExpiredClientRenewals();
        $user = auth()->user();

        $activeTab = $this->resolveActiveTab($request->input('tab'));
        $today = Carbon::today()->toDateString();
        $upcomingUntil = Carbon::today()->addDays(7)->toDateString();

        $services = $this->scopedQuery($user)
            ->with(['client.businessDetail', 'vendor', 'amcService.amcServiceDetails'])
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('end_date', '>=', $request->input('from_date'));
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('end_date', '<=', $request->input('to_date'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('service_name', 'like', '%' . $search . '%')
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', (string) $request->input('status'));
            })
            ->when($activeTab !== 'all', function ($query) use ($activeTab, $today, $upcomingUntil) {
                $this->applyTabFilter($query, $activeTab, $today, $upcomingUntil);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(function ($service) {
                return [
                    'id' => $service->id,
                    'client_name' => $service->client?->cname,
                    'company_name' => optional($service->client?->businessDetail)->company_name,
                    'vendor_name' => $service->vendor?->name,
                    'service_name' => $service->service_name,
                    'plan_type' => $service->plan_type,
                    'remark' => $service->remark_text,
                    'start_date' => optional($service->start_date)->toDateString(),
                    'end_date' => optional($service->end_date)->toDateString(),
                    'billing_date' => optional($service->billing_date)->toDateString(),
                    'status' => $service->getEffectiveStatusAttribute(),
                    'amc_service' => $this->formatAmcService($service->amcService),
                ];
            });

        if (!$services) {
            return ApiResponse::error('No client renewals found');
        }

        return ApiResponse::success($services, 'Client renewals found');
    }

    private function resolveActiveTab(?string $activeTab): string
    {
        return in_array($activeTab, self::AVAILABLE_TABS, true) ? $activeTab : 'all';
    }

    private function applyTabFilter($query, string $activeTab, string $today, string $upcomingUntil): void
    {
        match ($activeTab) {
            'upcoming' => $query
                ->where('status', 'active')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $upcomingUntil),
            'active' => $query
                ->where('status', 'active')
                ->where(function ($nested) use ($upcomingUntil) {
                    $nested->whereNull('end_date')
                        ->orWhereDate('end_date', '>', $upcomingUntil);
                }),
            'expired' => $query->where('status', 'expired'),
            'inactive' => $query->where('status', 'inactive'),
            'pending' => $query->where('status', 'pending'),
        };
    }

    public function show($id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)
            ->with(['client.businessDetail', 'vendor', 'amcService.amcServiceDetails'])
            ->findOrFail($id);

        $startDate = $service->start_date;
        $endDate = $service->end_date;
        $duration = null;
        if ($startDate && $endDate) {
            $duration = $startDate->diffInDays($endDate);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_id' => $service->id,
                'client_name' => $service->client->cname ?? null,
                'company_name' => optional($service->client?->businessDetail)->company_name,
                'client_email' => $service->client->email ?? null,
                'vendor_name' => $service->vendor->name ?? null,
                'vendor_email' => $service->vendor->email ?? null,
                'service_name' => $service->service_name,
                'service_details' => $service->service_details,
                'plan_type' => $service->plan_type,
                'remark' => $service->remark_text,
                'start_date' => $service->start_date?->toDateString(),
                'end_date' => $service->end_date?->toDateString(),
                'duration' => $duration,
                'billing_date' => $service->billing_date?->toDateString(),
                'status' => $service->getEffectiveStatusAttribute(),
                'created_at' => $service->created_at?->toDateTimeString(),
                'last_updated' => $service->updated_at?->toDateTimeString(),
                'amc_service' => $this->formatAmcService($service->amcService),
            ],
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:users,id',
            'client_business_detail_id' => 'required|exists:client_business_details,id',
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'plan_type' => 'required|in:monthly,yearly,quarterly,half_year',
            'is_amc' => 'nullable|boolean',
            'amc_total_visits' => 'nullable|integer|min:1|required_if:is_amc,1',
            'amc_start_date' => 'nullable|date|required_if:is_amc,1',
            'amc_end_date' => 'nullable|date|after_or_equal:amc_start_date|required_if:is_amc,1',
            'remark_text' => 'nullable|string|max:100',
            'remark_color' => 'nullable|in:yellow,red,green,blue,gray',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,expired,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

        $service = DB::transaction(function () use ($request) {
            $service = Service::create([
                'client_id' => $request->client_id,
                'client_business_detail_id' => $request->client_business_detail_id,
                'vendor_id' => $request->vendor_id,
                'service_name' => $request->service_name,
                'service_details' => $request->service_details,
                'plan_type' => $request->plan_type,
                'remark_text' => $request->remark_text,
                'remark_color' => $request->remark_color,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'billing_date' => $request->billing_date,
                'status' => $request->status,
            ]);

            if ($request->boolean('is_amc')) {
                $this->syncAmcPackage($service, $request->only([
                    'amc_total_visits',
                    'amc_start_date',
                    'amc_end_date',
                ]));
            }

            return $service;
        });

        return response()->json([
            'success' => true,
            'message' => 'Client renewal created successfully',
            'data' => $this->scopedQuery(auth()->user())
                ->with(['client.businessDetail', 'vendor', 'amcService.amcServiceDetails'])
                ->find($service->id),
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:users,id',
            'client_business_detail_id' => 'required|exists:client_business_details,id',
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'plan_type' => 'required|in:monthly,yearly,quarterly,half_year',
            'is_amc' => 'nullable|boolean',
            'amc_total_visits' => 'nullable|integer|min:1|required_if:is_amc,1',
            'amc_start_date' => 'nullable|date|required_if:is_amc,1',
            'amc_end_date' => 'nullable|date|after_or_equal:amc_start_date|required_if:is_amc,1',
            'remark_text' => 'nullable|string|max:100',
            'remark_color' => 'nullable|in:yellow,red,green,blue,gray',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,expired,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $service) {
            $service->update([
                'client_id' => $request->client_id,
                'client_business_detail_id' => $request->client_business_detail_id,
                'vendor_id' => $request->vendor_id,
                'service_name' => $request->service_name,
                'service_details' => $request->service_details,
                'plan_type' => $request->plan_type,
                'remark_text' => $request->remark_text,
                'remark_color' => $request->remark_color,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'billing_date' => $request->billing_date,
                'status' => $request->status,
            ]);

            if ($request->boolean('is_amc')) {
                $this->syncAmcPackage($service, $request->only([
                    'amc_total_visits',
                    'amc_start_date',
                    'amc_end_date',
                ]));
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Client renewal updated successfully',
            'data' => $this->scopedQuery($user)
                ->with(['client.businessDetail', 'vendor', 'amcService.amcServiceDetails'])
                ->find($service->id),
        ]);
    }

    public function updateAmcVisit(Request $request, $id, $detailId)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->with('amcService.amcServiceDetails')->findOrFail($id);
        $amcDetail = AmcServiceDetail::query()
            ->whereHas('amcService', fn ($query) => $query->where('service_id', $service->id))
            ->findOrFail($detailId);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $amcDetail->update([
            'status' => $request->status,
            'details' => $request->details,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'AMC visit updated successfully',
            'data' => $this->scopedQuery($user)
                ->with(['client.businessDetail', 'vendor', 'amcService.amcServiceDetails'])
                ->find($service->id),
        ]);
    }


    public function destroy($id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->findOrFail($id);

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client renewal deleted successfully',
        ]);
    }

    public function clientList()
    {
        $clients = User::
        where('role', 'client')
        ->where('status', 1)
            ->select('id', 'cname', 'coname', 'email', 'phone')
            ->orderBy('cname')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    public function vendorList()
    {
        $vendors = Vendor::where('status', 1)
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vendors,
        ]);
    }

    private function syncAmcPackage(Service $service, array $amcData): void
    {
        $totalVisits = (int) ($amcData['amc_total_visits'] ?? 0);

        if ($totalVisits < 1) {
            return;
        }

        $amcStartDate = Carbon::parse($amcData['amc_start_date']);
        $amcEndDate = Carbon::parse($amcData['amc_end_date']);

        $amcService = $service->amcService()->first();

        if (! $amcService) {
            $amcService = AmcService::create([
                'service_id' => $service->id,
                'total_visits' => $totalVisits,
                'amc_start_date' => $amcStartDate->toDateString(),
                'amc_end_date' => $amcEndDate->toDateString(),
            ]);
        } else {
            $amcService->update([
                'total_visits' => $totalVisits,
                'amc_start_date' => $amcStartDate->toDateString(),
                'amc_end_date' => $amcEndDate->toDateString(),
            ]);
            $amcService->amcServiceDetails()->delete();
        }

        foreach ($this->buildAmcVisitDates($amcStartDate, $amcEndDate, $totalVisits) as $index => $visitDate) {
            AmcServiceDetail::create([
                'amc_service_id' => $amcService->id,
                'visit_number' => $index + 1,
                'visit_date' => $visitDate->toDateString(),
                'status' => 'pending',
                'details' => null,
            ]);
        }
    }

    /**
     * @return array<int, Carbon>
     */
    private function buildAmcVisitDates(Carbon $startDate, Carbon $endDate, int $totalVisits): array
    {
        if ($totalVisits < 1) {
            return [];
        }

        if ($startDate->greaterThanOrEqualTo($endDate) || $totalVisits === 1) {
            return [$endDate->copy()];
        }

        $daysDiff = max($startDate->diffInDays($endDate), 0);
        $dates = [];

        for ($index = 0; $index < $totalVisits; $index++) {
            $offsetDays = (int) round(($daysDiff * ($index + 1)) / $totalVisits);
            $visitDate = $startDate->copy()->addDays($offsetDays);

            if ($visitDate->gt($endDate)) {
                $visitDate = $endDate->copy();
            }

            $dates[] = $visitDate;
        }

        return $dates;
    }

    private function formatAmcService(?AmcService $amcService): ?array
    {
        if (! $amcService) {
            return null;
        }

        $details = $amcService->amcServiceDetails ?? collect();

        return [
            'id' => $amcService->id,
            'total_visits' => $amcService->total_visits,
            'amc_start_date' => $amcService->amc_start_date?->toDateString(),
            'amc_end_date' => $amcService->amc_end_date?->toDateString(),
            'completed_visits' => $details->where('status', 'completed')->count(),
            'pending_visits' => $details->where('status', 'pending')->count(),
            'details' => $details->map(fn (AmcServiceDetail $detail) => [
                'id' => $detail->id,
                'visit_number' => $detail->visit_number,
                'visit_date' => $detail->visit_date?->toDateString(),
                'status' => $detail->status,
                'details' => $detail->details,
                'completed_at' => $detail->completed_at?->toDateTimeString(),
            ])->values(),
        ];
    }
}
