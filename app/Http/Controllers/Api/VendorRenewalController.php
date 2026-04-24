<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorRenewalController extends Controller
{

    public function index()
    {
        $vendorServices = VendorService::with(['vendor:id,name,email,phone'])
            ->select('id', 'vendor_id', 'service_name', 'start_date', 'end_date', 'billing_date', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        if (!$vendorServices) {
            return ApiResponse::error('No vendor renewals found');
        }

        return ApiResponse::success($vendorServices, 'Vendor renewals found');
    }

    public function show($id)
    {
        $vendorServices = VendorService::with('vendor')
            ->orderBy('created_at', 'desc')
            ->find($id);

        if (! $vendorServices) {
            return ApiResponse::error('Vendor Renewals not found.');
        }

        return ApiResponse::success($vendorServices, 'Vendor renewal found.');
    }

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
}
