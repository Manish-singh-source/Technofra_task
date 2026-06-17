<?php

namespace App\Http\Controllers\Web;

use App\DTOs\VendorService\VendorServiceData;
use App\DTOs\VendorService\VendorServiceFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\VendorService\StoreVendorServiceRequest;
use App\Http\Requests\Web\VendorService\UpdateVendorServiceRequest;
use App\Services\Vendor\VendorServiceManagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VendorServiceController extends Controller
{
    public function __construct(private VendorServiceManagementService $vendorServiceManagementService) {}

    private function planTypes(): array
    {
        return [
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'quarterly' => 'Quarterly',
            'half_year' => 'Half Year',
        ];
    }

    public function index(Request $request)
    {
        $filters = VendorServiceFilterData::fromArray($request->all());
        $result = $this->vendorServiceManagementService->listForWeb(auth()->user(), $filters);

        return view('vendor-services.index', [
            'services' => $result['services'],
            'tabCounts' => $result['tabCounts'],
            'activeTab' => $result['activeTab'],
        ]);
    }

    public function create(Request $request)
    {
        $vendors = $this->vendorServiceManagementService
            ->scopedVendorsQuery(auth()->user())
            ->orderBy('name')
            ->get();
        $selectedVendorId = $request->get('vendor_id');
        $planTypes = $this->planTypes();

        return view('vendor-services.create', compact('vendors', 'selectedVendorId', 'planTypes'));
    }

    public function store(StoreVendorServiceRequest $request)
    {
        $this->vendorServiceManagementService->ensureVendorAccess(auth()->user(), (int) $request->vendor_id);

        foreach ($request->validated('services') as $serviceData) {
            $payload = VendorServiceData::fromArray(array_merge($serviceData, ['vendor_id' => (int) $request->vendor_id]));
            $this->vendorServiceManagementService->create($payload);
        }

        return redirect()->route('vendor-services.index')
            ->with('success', 'Vendor Services created successfully!');
    }

    public function show(int $id)
    {
        $service = $this->vendorServiceManagementService->findForWebOrFail(auth()->user(), $id, ['vendor']);

        return view('vendor-services.show', compact('service'));
    }

    public function edit(int $id)
    {
        $service = $this->vendorServiceManagementService->findForWebOrFail(auth()->user(), $id);
        $vendors = $this->vendorServiceManagementService
            ->scopedVendorsQuery(auth()->user())
            ->orderBy('name')
            ->get();
        $planTypes = $this->planTypes();

        return view('vendor-services.edit', compact('service', 'vendors', 'planTypes'));
    }

    public function update(UpdateVendorServiceRequest $request, int $id)
    {
        $service = $this->vendorServiceManagementService->findForWebOrFail(auth()->user(), $id);
        $this->vendorServiceManagementService->ensureVendorAccess(auth()->user(), (int) $request->vendor_id);

        $validated = $request->validated();

        if ($validated['status'] != 'inactive') {
            $computedStatus = Carbon::parse($validated['end_date'])->lt(Carbon::today()) ? 'expired' : 'active';
            $validated['status'] = $computedStatus;
        }

        $updated = $this->vendorServiceManagementService->update($service, VendorServiceData::fromArray($validated));

        return redirect()->route('vendor-services.index', ['tab' => $updated->tab_key])
            ->with('success', 'Vendor Service updated successfully!');
    }

    public function destroy(int $id)
    {
        $service = $this->vendorServiceManagementService->findForWebOrFail(auth()->user(), $id);
        $this->vendorServiceManagementService->delete($service);

        return redirect()->route('vendor-services.index')
            ->with('success', 'Vendor Service deleted successfully!');
    }

    public function deleteSelected(Request $request)
    {
        $ids = is_array($request->ids) ? $request->ids : explode(',', (string) $request->ids);
        $this->vendorServiceManagementService->deleteSelected(auth()->user(), $ids);

        return redirect()->back()->with('success', 'Selected Vendor Service deleted successfully.');
    }
}
