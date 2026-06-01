<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\VendorService\VendorServiceData;
use App\DTOs\VendorService\VendorServiceFilterData;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VendorRenewal\IndexVendorRenewalRequest;
use App\Http\Requests\Api\VendorRenewal\StoreVendorRenewalRequest;
use App\Http\Requests\Api\VendorRenewal\UpdateVendorRenewalRequest;
use App\Http\Resources\Api\V1\VendorRenewalResource;
use App\Models\Vendor;
use App\Services\Vendor\VendorServiceManagementService;

class VendorRenewalController extends Controller
{
    public function __construct(private VendorServiceManagementService $vendorServiceManagementService) {}

    public function apiFormOptions()
    {
        return ApiResponse::success([
            'vendors' => Vendor::query()
                ->select('id', 'name')
                ->where('status', '1')
                ->orderBy('name')
                ->get(),
            'plan_types' => ['yearly', 'quarterly', 'monthly'],
            'status_options' => ['active', 'inactive'],
            'remark_colors' => ['yellow', 'red', 'green', 'blue', 'gray'],
        ], 'Vendor renewal form options found');
    }

    public function index(IndexVendorRenewalRequest $request)
    {
        $filters = VendorServiceFilterData::fromArray($request->validated());
        $renewals = $this->vendorServiceManagementService->listForApi($filters);
        $renewals->setCollection(
            $renewals->getCollection()->map(
                fn ($renewal) => (new VendorRenewalResource($renewal))->resolve()
            )
        );

        return ApiResponse::success($renewals, 'Vendor renewals found');
    }

    public function show(int $id)
    {
        $renewal = $this->vendorServiceManagementService->findForApiOrFail($id);

        return ApiResponse::success(new VendorRenewalResource($renewal), 'Vendor renewal found.');
    }

    public function store(StoreVendorRenewalRequest $request)
    {
        $renewal = $this->vendorServiceManagementService->create(VendorServiceData::fromArray($request->validated()));

        return ApiResponse::success(new VendorRenewalResource($renewal->load('vendor')), 'Vendor renewal created successfully', 201);
    }

    public function update(UpdateVendorRenewalRequest $request, int $id)
    {
        $renewal = $this->vendorServiceManagementService->findForApiOrFail($id);
        $updated = $this->vendorServiceManagementService->update($renewal, VendorServiceData::fromArray($request->validated()));

        return ApiResponse::success(new VendorRenewalResource($updated->load('vendor')), 'Vendor renewal updated successfully');
    }

    public function destroy(int $id)
    {
        $renewal = $this->vendorServiceManagementService->findForApiOrFail($id);
        $this->vendorServiceManagementService->delete($renewal);

        return ApiResponse::success(null, 'Vendor renewal deleted successfully');
    }
}
