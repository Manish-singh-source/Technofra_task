<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Service;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientRenewalController extends Controller
{

    public function index()
    {
        $services = Service::with(['client', 'vendor'])
            ->whereNotNull('client_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'client_name' => $service->client->cname ?? null,
                    'vendor_name' => $service->vendor->name ?? null,
                    'service_name' => $service->service_name,
                    'remark' => $service->remark_text,
                    'start_date' => $service->start_date?->toDateString(),
                    'end_date' => $service->end_date?->toDateString(),
                    'billing_date' => $service->billing_date?->toDateString(),
                    'status' => $service->status,
                ];
            });

        if(!$services) {
            return ApiResponse::error('No client renewals found');
        }
        
        return ApiResponse::success($services, 'Client renewals found');
    }

    public function show($id)
    {
        $service = Service::with(['client', 'vendor'])->whereNotNull('client_id')->find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

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
                'client_email' => $service->client->email ?? null,
                'vendor_name' => $service->vendor->name ?? null,
                'vendor_email' => $service->vendor->email ?? null,
                'service_name' => $service->service_name,
                'service_details' => $service->service_details,
                'remark' => $service->remark_text,
                'start_date' => $service->start_date?->toDateString(),
                'end_date' => $service->end_date?->toDateString(),
                'duration' => $duration,
                'billing_date' => $service->billing_date?->toDateString(),
                'status' => $service->status,
                'created_at' => $service->created_at?->toDateTimeString(),
                'last_updated' => $service->updated_at?->toDateTimeString(),
            ],
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
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

        $service = Service::create([
            'client_id' => $request->client_id,
            'vendor_id' => $request->vendor_id,
            'service_name' => $request->service_name,
            'service_details' => $request->service_details,
            'remark_text' => $request->remark_text,
            'remark_color' => $request->remark_color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'billing_date' => $request->billing_date,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client renewal created successfully',
            'data' => $service,
        ]);
    }


    public function update(Request $request, $id)
    {
        $service = Service::whereNotNull('client_id')->find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
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

        $service->update([
            'client_id' => $request->client_id,
            'vendor_id' => $request->vendor_id,
            'service_name' => $request->service_name,
            'service_details' => $request->service_details,
            'remark_text' => $request->remark_text,
            'remark_color' => $request->remark_color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'billing_date' => $request->billing_date,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client renewal updated successfully',
            'data' => $service,
        ]);
    }


    public function destroy($id)
    {
        $service = Service::whereNotNull('client_id')->find($id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client renewal deleted successfully',
        ]);
    }

    public function clientList()
    {
        $clients = Client::where('status', 1)
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

}
