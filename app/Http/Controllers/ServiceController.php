<?php

namespace App\Http\Controllers;

use App\Helpers\RenewalStatusHelper;
use App\Models\ClientBusinessDetail;
use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            $query->where('billing_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('billing_date', '<=', $request->to_date);
        }

        $services = $query
            ->orderByRaw(
                'CASE WHEN end_date <= ? THEN 0 WHEN end_date BETWEEN ? AND ? THEN 1 ELSE 2 END',
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
        return view('services.create', compact('clientCompanies', 'vendors', 'selectedCompanyId', 'selectedVendorId'));
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

        foreach ($request->services as $serviceData) {
            Service::create([
                'client_id' => $clientCompany->user_id,
                'client_business_detail_id' => $clientCompany->id,
                'vendor_id' => $serviceData['vendor_id'],
                'service_name' => $serviceData['service_name'],
                'service_details' => $serviceData['service_details'] ?? null,
                'remark_text' => $serviceData['remark_text'] ?? null,
                'remark_color' => $serviceData['remark_color'] ?? null,
                'start_date' => $serviceData['start_date'],
                'end_date' => $serviceData['end_date'],
                'billing_date' => $serviceData['billing_date'],
                'status' => $serviceData['status'],
            ]);
        }

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
        $service = $this->scopedQuery($user)->with(['company', 'client', 'vendor'])->findOrFail($id);
        return view('services.show', compact('service'));
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
        $service = $this->scopedQuery($user)->findOrFail($id);
        $clientCompanies = ClientBusinessDetail::query()
            ->with('user:id,first_name,last_name,email,role')
            ->where('company_name', '!=', '')
            ->whereHas('user', fn($query) => $query->where('role', 'client')->where('status', 'active'))
            ->orderBy('company_name')
            ->get();
        $vendors = Vendor::orderBy('name')->where('status', '1')->get();
        return view('services.edit', compact('service', 'clientCompanies', 'vendors'));
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
        $service = $this->scopedQuery($user)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_business_detail_id' => [
                'required',
                Rule::exists('client_business_details', 'id'),
            ],
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
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

        $clientCompany = ClientBusinessDetail::findOrFail($request->client_business_detail_id);

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
            'remark_text' => $request->remark_text,
            'remark_color' => $request->remark_color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'billing_date' => $request->billing_date,
            'status' => $computedStatus,
        ]);

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
}
