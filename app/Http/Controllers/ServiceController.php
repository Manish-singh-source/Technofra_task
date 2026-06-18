<?php

namespace App\Http\Controllers;

use App\Helpers\RenewalStatusHelper;
use App\Models\AmcService;
use App\Models\AmcServiceDetail;
use App\Models\ClientBusinessDetail;
use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    private function canViewAll($user): bool
    {
        return $user && ($user->hasRole('admin') || $user->hasRole('super_admin2') || $user->hasRole('super_admin'));
    }

    private function scopedQuery($user)
    {
        $query = Service::query()->whereNotNull('client_id');

        if (! $this->canViewAll($user)) {
            $query->where('client_id', optional($user)->id);
        }

        return $query;
    }

    private function planTypes(): array
    {
        return [
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'quarterly' => 'Quarterly',
            'half_year' => 'Half Year',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteSelected(Request $request)
    {
        $user = auth()->user();
        $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
        $ids = array_map('intval', $ids);

        $this->scopedQuery($user)->whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Selected Service deleted successfully.');
    }

    public function index(Request $request)
    {
        RenewalStatusHelper::markExpiredClientRenewals();

        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $fiveDaysFromNow = Carbon::today()->addDays(5)->toDateString();
        $activeTab = $request->get('tab', 'all');
        $availableTabs = ['all', 'upcoming', 'active', 'inactive', 'pending', 'expired'];

        if (!in_array($activeTab, $availableTabs, true)) {
            $activeTab = 'all';
        }

        $query = $this->scopedQuery($user)->with(['company', 'client.businessDetail', 'vendor']);

        if ($request->filled('from_date')) {
            $query->where('end_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('end_date', '<=', $request->to_date);
        }

        $services = $query
            ->orderByRaw(
                "CASE
                    WHEN status = 'expired' OR (status = 'active' AND end_date < ?) THEN 0
                    WHEN status = 'active' AND end_date BETWEEN ? AND ? THEN 1
                    WHEN status = 'active' THEN 2
                    WHEN status = 'inactive' THEN 3
                    WHEN status = 'pending' THEN 4
                    ELSE 5
                 END",
                [$today, $today, $fiveDaysFromNow]
            )
            ->orderBy('end_date')
            ->latest('created_at')
            ->get();

        $tabCounts = [
            'all' => $services->count(),
            'upcoming' => $services->where('tab_key', 'upcoming')->count(),
            'active' => $services->where('tab_key', 'active')->count(),
            'inactive' => $services->where('tab_key', 'inactive')->count(),
            'pending' => $services->where('tab_key', 'pending')->count(),
            'expired' => $services->where('tab_key', 'expired')->count(),
        ];

        return view('services.index', compact('services', 'tabCounts', 'activeTab'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $clientCompanies = ClientBusinessDetail::query()
            ->with('user:id,first_name,last_name,email,role')
            ->where('company_name', '!=', '')
            ->whereHas('user', fn($query) => $query->where('role', 'client')->where('status', 'active'))
            ->orderBy('company_name')
            ->get();
        $vendors = Vendor::orderBy('name')->where('status', '1')->get();
        $selectedCompanyId = $request->get('client_business_detail_id');
        if (! $selectedCompanyId && $request->filled('client_id')) {
            $selectedCompanyId = ClientBusinessDetail::query()
                ->where('user_id', $request->get('client_id'))
                ->orderBy('id')
                ->value('id');
        }
        $selectedVendorId = $request->get('vendor_id');
        $planTypes = $this->planTypes();
        return view('services.create', compact('clientCompanies', 'vendors', 'selectedCompanyId', 'selectedVendorId', 'planTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_business_detail_id' => [
                'required',
                Rule::exists('client_business_details', 'id'),
            ],
            'services' => 'required|array|min:1',
            'services.*.vendor_id' => 'required|exists:vendors,id',
            'services.*.service_name' => 'required|string|max:255',
            'services.*.service_details' => 'nullable|string',
            'services.*.plan_type' => 'required|in:monthly,yearly,quarterly,half_year',
            'services.*.is_amc' => 'nullable|boolean',
            'services.*.amc_total_visits' => 'nullable|integer|min:1|required_if:services.*.is_amc,1',
            'services.*.amc_start_date' => 'nullable|date|required_if:services.*.is_amc,1',
            'services.*.amc_end_date' => 'nullable|date|after_or_equal:services.*.amc_start_date|required_if:services.*.is_amc,1',
            'services.*.remark_text' => 'nullable|string|max:100',
            'services.*.remark_color' => 'nullable|in:yellow,red,green,blue,gray|required_with:services.*.remark_text',
            'services.*.start_date' => 'required|date',
            'services.*.end_date' => 'required|date|after_or_equal:services.*.start_date',
            'services.*.billing_date' => 'required|date',
            'services.*.status' => 'required|in:active,inactive,expired,pending',
        ], [
            'client_business_detail_id.required' => 'Please select a company.',
            'client_business_detail_id.exists' => 'Selected company does not exist.',
            'services.required' => 'At least one service is required.',
            'services.*.vendor_id.required' => 'Please select a vendor for each service.',
            'services.*.vendor_id.exists' => 'Selected vendor does not exist.',
            'services.*.service_name.required' => 'Service name is required.',
            'services.*.plan_type.required' => 'Plan type is required.',
            'services.*.plan_type.in' => 'Selected plan type is invalid.',
            'services.*.amc_total_visits.required_if' => 'AMC total visits are required when AMC is enabled.',
            'services.*.amc_start_date.required_if' => 'AMC start date is required when AMC is enabled.',
            'services.*.amc_end_date.required_if' => 'AMC end date is required when AMC is enabled.',
            'services.*.amc_end_date.after_or_equal' => 'AMC end date must be after or equal to AMC start date.',
            'services.*.remark_color.required_with' => 'Please select a remark color when remark text is provided.',
            'services.*.start_date.required' => 'Start date is required.',
            'services.*.end_date.required' => 'End date is required.',
            'services.*.end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'services.*.billing_date.required' => 'Billing date is required.',
            'services.*.billing_date.date' => 'Billing date must be a valid date.',
            'services.*.status.required' => 'Status is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $clientCompany = ClientBusinessDetail::findOrFail($request->client_business_detail_id);

        DB::transaction(function () use ($request, $clientCompany) {
            foreach ($request->services as $index => $serviceData) {
                $service = Service::create([
                    'client_id' => $clientCompany->user_id,
                    'client_business_detail_id' => $clientCompany->id,
                    'vendor_id' => $serviceData['vendor_id'],
                    'service_name' => $serviceData['service_name'],
                    'service_details' => $serviceData['service_details'] ?? null,
                    'plan_type' => $serviceData['plan_type'],
                    'remark_text' => $serviceData['remark_text'] ?? null,
                    'remark_color' => $serviceData['remark_color'] ?? null,
                    'start_date' => $serviceData['start_date'],
                    'end_date' => $serviceData['end_date'],
                    'billing_date' => $serviceData['billing_date'],
                    'status' => $serviceData['status'],
                ]);

                if ($request->boolean("services.{$index}.is_amc")) {
                    $this->createAmcPackage($service, $serviceData);
                }
            }
        });

        return redirect()->route('services.index')
            ->with('success', 'Services created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->with(['company', 'client', 'vendor', 'amcService.amcServiceDetails'])->findOrFail($id);
        return view('services.show', compact('service'));
    }

    public function updateAmcVisit(Request $request, $serviceId, $detailId): RedirectResponse
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->with('amcService.amcServiceDetails')->findOrFail($serviceId);
        $amcDetail = AmcServiceDetail::query()
            ->whereHas('amcService', fn ($query) => $query->where('service_id', $service->id))
            ->findOrFail($detailId);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $amcDetail->update([
            'status' => $request->status,
            'details' => $request->details,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'AMC visit updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->with('amcService.amcServiceDetails')->findOrFail($id);
        $clientCompanies = ClientBusinessDetail::query()
            ->with('user:id,first_name,last_name,email,role')
            ->where('company_name', '!=', '')
            ->whereHas('user', fn($query) => $query->where('role', 'client')->where('status', 'active'))
            ->orderBy('company_name')
            ->get();
        $vendors = Vendor::orderBy('name')->where('status', '1')->get();
        $planTypes = $this->planTypes();
        return view('services.edit', compact('service', 'clientCompanies', 'vendors', 'planTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->with('amcService.amcServiceDetails')->findOrFail($id);
        $shouldSyncAmc = $request->boolean('is_amc');
        $existingAmcService = $service->amcService;
        $completedVisitNumbers = collect();

        if ($existingAmcService) {
            $completedVisitNumbers = $existingAmcService->amcServiceDetails
                ->where('status', 'completed')
                ->pluck('visit_number')
                ->map(fn ($visitNumber) => (int) $visitNumber)
                ->values();
        }

        $validator = Validator::make($request->all(), [
            'client_business_detail_id' => [
                'required',
                Rule::exists('client_business_details', 'id'),
            ],
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'plan_type' => 'required|in:monthly,yearly,quarterly,half_year',
            'is_amc' => 'nullable|boolean',
            'amc_total_visits' => 'nullable|integer|min:1|required_if:is_amc,1',
            'amc_start_date' => 'nullable|date|required_if:is_amc,1',
            'amc_end_date' => 'nullable|date|after_or_equal:amc_start_date|required_if:is_amc,1',
            'remark_text' => 'nullable|string|max:100',
            'remark_color' => 'nullable|in:yellow,red,green,blue,gray|required_with:remark_text',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,expired,pending',
        ], [
            'client_business_detail_id.required' => 'Please select a company.',
            'client_business_detail_id.exists' => 'Selected company does not exist.',
            'vendor_id.required' => 'Please select a vendor.',
            'service_name.required' => 'Service name is required.',
            'plan_type.required' => 'Plan type is required.',
            'plan_type.in' => 'Selected plan type is invalid.',
            'amc_total_visits.required_if' => 'AMC total visits are required when AMC is enabled.',
            'amc_start_date.required_if' => 'AMC start date is required when AMC is enabled.',
            'amc_end_date.required_if' => 'AMC end date is required when AMC is enabled.',
            'amc_end_date.after_or_equal' => 'AMC end date must be after or equal to AMC start date.',
            'remark_color.required_with' => 'Please select a remark color when remark text is provided.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'billing_date.required' => 'Billing date is required.',
            'billing_date.date' => 'Billing date must be a valid date.',
            'status.required' => 'Status is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($shouldSyncAmc && $existingAmcService) {
            $requestedTotalVisits = (int) $request->input('amc_total_visits', 0);
            $highestCompletedVisitNumber = (int) ($completedVisitNumbers->max() ?? 0);

            if ($requestedTotalVisits < $highestCompletedVisitNumber) {
                return redirect()->back()
                    ->withErrors([
                        'amc_total_visits' => sprintf(
                            'AMC total visits cannot be less than the highest completed visit number (%d).',
                            $highestCompletedVisitNumber
                        ),
                    ])
                    ->withInput();
            }
        }

        $clientCompany = ClientBusinessDetail::findOrFail($request->client_business_detail_id);

        DB::transaction(function () use ($request, $clientCompany, $service, $shouldSyncAmc) {
            if ($request->status != 'inactive') {
                $endDate = Carbon::parse($request->end_date);
                $computedStatus = $endDate->lt(Carbon::today()) ? 'expired' : 'active';
            } else {
                $computedStatus = $request->status;
            }

            $service->update([
                'client_id' => $clientCompany->user_id,
                'client_business_detail_id' => $clientCompany->id,
                'vendor_id' => $request->vendor_id,
                'service_name' => $request->service_name,
                'service_details' => $request->service_details,
                'plan_type' => $request->plan_type,
                'remark_text' => $request->remark_text,
                'remark_color' => $request->remark_color,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'billing_date' => $request->billing_date,
                'status' => $computedStatus,
            ]);

            if ($shouldSyncAmc) {
                $this->syncAmcPackage($service->fresh(['amcService.amcServiceDetails']), $request->all());
            }
        });

        return redirect()->route('services.index')
            ->with('success', 'Service updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $service = $this->scopedQuery($user)->findOrFail($id);
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully!');
    }

    private function createAmcPackage(Service $service, array $serviceData): void
    {
        $totalVisits = (int) ($serviceData['amc_total_visits'] ?? 0);
        $amcStartDate = Carbon::parse($serviceData['amc_start_date']);
        $amcEndDate = Carbon::parse($serviceData['amc_end_date']);

        if ($totalVisits < 1) {
            return;
        }

        $amcService = AmcService::create([
            'service_id' => $service->id,
            'total_visits' => $totalVisits,
            'amc_start_date' => $amcStartDate->toDateString(),
            'amc_end_date' => $amcEndDate->toDateString(),
        ]);

        $this->createAmcDetails($amcService, $amcStartDate, $amcEndDate, $totalVisits);
    }

    private function syncAmcPackage(Service $service, array $serviceData): void
    {
        if (! ($serviceData['is_amc'] ?? false)) {
            return;
        }

        $totalVisits = (int) ($serviceData['amc_total_visits'] ?? 0);
        if ($totalVisits < 1) {
            return;
        }

        $amcStartDate = Carbon::parse($serviceData['amc_start_date']);
        $amcEndDate = Carbon::parse($serviceData['amc_end_date']);
        $amcService = $service->amcService()->with('amcServiceDetails')->first();

        if (! $amcService) {
            $this->createAmcPackage($service, $serviceData);

            return;
        }

        $completedDetails = $amcService->amcServiceDetails->where('status', 'completed')->values();
        $completedVisitNumbers = $completedDetails
            ->pluck('visit_number')
            ->map(fn ($visitNumber) => (int) $visitNumber)
            ->values();

        $highestCompletedVisitNumber = (int) ($completedVisitNumbers->max() ?? 0);
        if ($totalVisits < $highestCompletedVisitNumber) {
            throw ValidationException::withMessages([
                'amc_total_visits' => sprintf(
                    'AMC total visits cannot be less than the highest completed visit number (%d).',
                    $highestCompletedVisitNumber
                ),
            ]);
        }

        $amcService->update([
            'total_visits' => $totalVisits,
            'amc_start_date' => $amcStartDate->toDateString(),
            'amc_end_date' => $amcEndDate->toDateString(),
        ]);

        if ($completedDetails->isEmpty()) {
            $amcService->amcServiceDetails()->delete();
            $this->createAmcDetails($amcService, $amcStartDate, $amcEndDate, $totalVisits);

            return;
        }

        $amcService->amcServiceDetails()
            ->where('status', 'pending')
            ->delete();

        $completedVisitLookup = $completedVisitNumbers->flip();
        foreach ($this->buildAmcVisitDates($amcStartDate, $amcEndDate, $totalVisits) as $index => $visitDate) {
            $visitNumber = $index + 1;

            if ($completedVisitLookup->has($visitNumber)) {
                continue;
            }

            AmcServiceDetail::create([
                'amc_service_id' => $amcService->id,
                'visit_number' => $visitNumber,
                'visit_date' => $visitDate->toDateString(),
                'status' => 'pending',
                'details' => null,
            ]);
        }
    }

    private function createAmcDetails(AmcService $amcService, Carbon $amcStartDate, Carbon $amcEndDate, int $totalVisits): void
    {
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
}
