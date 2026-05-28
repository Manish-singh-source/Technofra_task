<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Vendor\VendorData;
use App\DTOs\Vendor\VendorFilterData;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vendor\StoreVendorApiRequest;
use App\Http\Requests\Api\Vendor\UpdateVendorApiRequest;
use App\Http\Requests\Api\Vendor\VendorIndexApiRequest;
use App\Http\Resources\Api\V1\VendorCollection;
use App\Http\Resources\Api\V1\VendorResource;
use App\Services\Vendor\VendorService;

class VendorController extends Controller
{
    public function __construct(private VendorService $vendorService) {}

    public function index(VendorIndexApiRequest $request)
    {
        $filters = VendorFilterData::fromArray($request->validated());
        $vendors = $this->vendorService->listForApi(auth()->user(), $filters);

        return ApiResponse::success([
            'items' => VendorResource::collection($vendors->items()),
            'meta' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
        ], 'Vendors retrieved successfully');
    }

    public function show(int $id)
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);

        return ApiResponse::success(new VendorResource($vendor), 'Vendor retrieved successfully');
    }

    public function store(StoreVendorApiRequest $request)
    {
        $vendor = $this->vendorService->create(VendorData::fromArray($request->validated()));

        return ApiResponse::success(new VendorResource($vendor), 'Vendor created successfully', 201);
    }

    public function update(UpdateVendorApiRequest $request, int $id)
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);
        $vendor = $this->vendorService->update($vendor, VendorData::fromArray($request->validated()));

        return ApiResponse::success(new VendorResource($vendor), 'Vendor updated successfully');
    }

    public function destroy(int $id)
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);
        $this->vendorService->delete($vendor);

        return ApiResponse::success(null, 'Vendor deleted successfully');
    }
}

