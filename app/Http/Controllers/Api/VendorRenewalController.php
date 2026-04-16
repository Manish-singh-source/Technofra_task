<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorRenewalController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'plan_type' => 'nullable|string|max:100',
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

        $vendorService = VendorService::create([
            'vendor_id' => $request->vendor_id,
            'service_name' => $request->service_name,
            'service_details' => $request->service_details,
            'plan_type' => $request->plan_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'billing_date' => $request->billing_date,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor renewal created successfully',
            'data' => $vendorService,
        ]);
    }

    public function index()
    {
        $vendorServices = VendorService::with(['vendor:id,name,email,phone'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'vendor_name' => $service->vendor->name ?? null,
                    'email_id' => $service->vendor->email ?? null,
                    'contact_no' => $service->vendor->phone ?? null,
                    'status' => $service->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $vendorServices,
        ]);
    }

    public function show($id)
    {
        $service = VendorService::with(['vendor:id,name,email,phone,address'])->find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor renewal not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'vendor_id' => $service->vendor_id,
                'vendor_name' => $service->vendor->name ?? null,
                'email_id' => $service->vendor->email ?? null,
                'contact_no' => $service->vendor->phone ?? null,
                'service_name' => $service->service_name,
                'service_details' => $service->service_details,
                'plan_type' => $service->plan_type,
                'start_date' => $service->start_date?->toDateString(),
                'end_date' => $service->end_date?->toDateString(),
                'billing_date' => $service->billing_date?->toDateString(),
                'status' => $service->status,
                'created_at' => $service->created_at?->toDateTimeString(),
                'last_updated' => $service->updated_at?->toDateTimeString(),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $service = VendorService::find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor renewal not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'plan_type' => 'nullable|string|max:100',
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

        $service->update([
            'service_name' => $request->service_name,
            'service_details' => $request->service_details,
            'plan_type' => $request->plan_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'billing_date' => $request->billing_date,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor renewal updated successfully',
            'data' => $service,
        ]);
    }

    public function updateVendor(Request $request, $id)
    {
        $service = VendorService::find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor renewal not found',
            ], 404);
        }

        $vendor = Vendor::find($service->vendor_id);

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $vendor->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully',
            'data' => $vendor,
        ]);
    }

    public function destroy($id)
    {
        $service = VendorService::find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor renewal not found',
            ], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor renewal deleted successfully',
        ]);
    }

    public function destroyAll()
    {
        VendorService::query()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All vendor renewals deleted successfully',
        ]);
    }

    public function forceDeleteAll()
    {
        VendorService::withTrashed()->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'All vendor renewals permanently deleted',
        ]);
    }
}
