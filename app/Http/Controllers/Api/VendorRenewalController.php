<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\RenewalStatusHelper;
use App\Http\Controllers\Controller;
use App\Models\VendorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorRenewalController extends Controller
{
    private const AVAILABLE_TABS = ['all', 'upcoming', 'active', 'inactive', 'pending', 'expired'];

    public function index(Request $request)
    {
        RenewalStatusHelper::markExpiredVendorRenewals();

        $activeTab = $this->resolveActiveTab($request->input('tab'));
        $today = Carbon::today()->toDateString();
        $upcomingUntil = Carbon::today()->addDays(7)->toDateString();

        $vendorServices = VendorService::with(['vendor:id,name,email,phone'])
            ->select('id', 'vendor_id', 'service_name', 'service_details', 'plan_type', 'start_date', 'end_date', 'billing_date', 'status', 'created_at')
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('billing_date', '>=', $request->input('from_date'));
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('billing_date', '<=', $request->input('to_date'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('service_name', 'like', '%' . $search . '%')
                        ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                            $vendorQuery->where('name', 'like', '%' . $search . '%')
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
            ->through(function (VendorService $service) {
                return [
                    'id' => $service->id,
                    'vendor_id' => $service->vendor_id,
                    'vendor_name' => $service->vendor?->name,
                    'vendor_email' => $service->vendor?->email,
                    'vendor_phone' => $service->vendor?->phone,
                    'service_name' => $service->service_name,
                    'service_details' => $service->service_details,
                    'plan_type' => $service->plan_type,
                    'start_date' => optional($service->start_date)->toDateString(),
                    'end_date' => optional($service->end_date)->toDateString(),
                    'billing_date' => optional($service->billing_date)->toDateString(),
                    'status' => $service->effective_status,
                ];
            });

        if (!$vendorServices) {
            return ApiResponse::error('No vendor renewals found');
        }

        return ApiResponse::success($vendorServices, 'Vendor renewals found');
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
        $vendorServices = VendorService::with('vendor')
            ->orderBy('created_at', 'desc')
            ->find($id);

        if (! $vendorServices) {
            return ApiResponse::error('Vendor Renewals not found.');
        }

        $vendorServices->status = $vendorServices->effective_status;

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
            return ApiResponse::error($validator->errors());
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

        if (!$vendorService) {
            return ApiResponse::error('Vendor renewal creation failed.');
        }

        return ApiResponse::success($vendorService, 'Vendor renewal created successfully');
    }

    public function update(Request $request, $id)
    {
        $service = VendorService::find($id);

        if (! $service) {
            return ApiResponse::error('Vendor renewal not found.');
        }

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
            return ApiResponse::error($validator->errors());
        }

        $service->update([
            'vendor_id' => $request->vendor_id,
            'service_name' => $request->service_name,
            'service_details' => $request->service_details,
            'plan_type' => $request->plan_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'billing_date' => $request->billing_date,
            'status' => $request->status,
        ]);

        return ApiResponse::success($service, 'Vendor renewal updated successfully');
    }

    public function destroy($id)
    {
        $service = VendorService::find($id);

        if (! $service) {
            return ApiResponse::error('Vendor renewal not found.');
        }

        $service->delete();
        return ApiResponse::success($service, 'Vendor renewal deleted successfully');
    }
}
