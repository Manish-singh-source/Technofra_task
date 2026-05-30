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
use App\Services\Vendor\VendorServiceManagementService;

class VendorRenewalController extends Controller
{
    public function __construct(private VendorServiceManagementService $vendorServiceManagementService) {}

    public function index(IndexVendorRenewalRequest $request)
    {
        $filters = VendorServiceFilterData::fromArray($request->validated());
        $renewals = $this->vendorServiceManagementService->listForApi($filters);

        return ApiResponse::success([
            'items' => VendorRenewalResource::collection($renewals->getCollection()),
            'meta' => [
                'current_page' => $renewals->currentPage(),
                'last_page' => $renewals->lastPage(),
                'per_page' => $renewals->perPage(),
                'total' => $renewals->total(),
                'from' => $renewals->firstItem(),
                'to' => $renewals->lastItem(),
                'next_page_url' => $renewals->nextPageUrl(),
                'prev_page_url' => $renewals->previousPageUrl(),
            ],
        ], 'Vendor renewals found');
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
