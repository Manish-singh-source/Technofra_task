<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Service;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    private const AVAILABLE_TABS = ['all', 'upcoming', 'active', 'inactive', 'pending', 'expired'];

    private const AVAILABLE_STATUSES = ['active', 'inactive', 'expired', 'pending'];

    public function formOptions(): JsonResponse
    {
        return ApiResponse::success([
            'clients' => Client::query()
                ->orderBy('cname')
                ->get()
                ->map(fn (Client $client) => [
                    'id' => $client->id,
                    'name' => $client->cname,
                    'company_name' => $client->coname,
                    'email' => $client->email,
                ])
                ->values(),
            'vendors' => Vendor::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Vendor $vendor) => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                ])
                ->values(),
            'statuses' => self::AVAILABLE_STATUSES,
            'tabs' => self::AVAILABLE_TABS,
        ], 'Service form options retrieved successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $fiveDaysFromNow = Carbon::today()->addDays(5)->toDateString();
        $activeTab = $this->resolveActiveTab($request->input('tab'));

        $services = Service::query()
            ->with(['client', 'vendor'])
            ->whereNotNull('client_id')
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('billing_date', '>=', $request->input('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('billing_date', '<=', $request->input('to_date')))
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->integer('client_id')))
            ->when($request->filled('vendor_id'), fn ($query) => $query->where('vendor_id', $request->integer('vendor_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('service_name', 'like', '%' . $search . '%')
                        ->orWhere('service_details', 'like', '%' . $search . '%')
                        ->orWhere('remark_text', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw(
                'CASE WHEN end_date <= ? THEN 0 WHEN end_date BETWEEN ? AND ? THEN 1 ELSE 2 END',
                [$today, $today, $fiveDaysFromNow]
            )
            ->orderBy('end_date')
            ->latest('created_at')
            ->get();

        $tabCounts = $this->buildTabCounts($services);
        $filteredServices = $activeTab === 'all'
            ? $services
            : $services->where('tab_key', $activeTab)->values();

        return response()->json([
            'success' => true,
            'message' => 'Services retrieved successfully.',
            'data' => $filteredServices
                ->map(fn (Service $service) => $this->formatServiceResource($service))
                ->values(),
            'meta' => [
                'active_tab' => $activeTab,
                'counts' => $tabCounts,
                'filters' => [
                    'from_date' => $request->input('from_date'),
                    'to_date' => $request->input('to_date'),
                    'client_id' => $request->input('client_id'),
                    'vendor_id' => $request->input('vendor_id'),
                    'status' => $request->input('status'),
                    'search' => $request->input('search'),
                ],
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $service = Service::query()
            ->with(['client', 'vendor'])
            ->whereNotNull('client_id')
            ->find($id);

        if (! $service) {
            return ApiResponse::error('Service not found.', null, 404);
        }

        return ApiResponse::success($this->formatServiceResource($service), 'Service retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->storeRules(), $this->validationMessages());

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $createdServices = collect();

            foreach ($validated['services'] as $serviceData) {
                $createdServices->push(Service::create([
                    'client_id' => $validated['client_id'],
                    'vendor_id' => $serviceData['vendor_id'],
                    'service_name' => $serviceData['service_name'],
                    'service_details' => $serviceData['service_details'] ?? null,
                    'remark_text' => $serviceData['remark_text'] ?? null,
                    'remark_color' => $serviceData['remark_color'] ?? null,
                    'start_date' => $serviceData['start_date'],
                    'end_date' => $serviceData['end_date'],
                    'billing_date' => $serviceData['billing_date'],
                    'status' => $serviceData['status'],
                ]));
            }

            DB::commit();

            $createdServiceIds = $createdServices
                ->pluck('id')
                ->filter()
                ->values();

            $freshServices = Service::query()
                ->with(['client', 'vendor'])
                ->whereIn('id', $createdServiceIds)
                ->orderByRaw('FIELD(id, ' . $createdServiceIds->implode(',') . ')')
                ->get();

            return ApiResponse::success(
                $freshServices
                    ->map(fn (Service $service) => $this->formatServiceResource($service))
                    ->values(),
                'Services created successfully.',
                201
            );
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to create services.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $service = Service::query()
            ->whereNotNull('client_id')
            ->find($id);

        if (! $service) {
            return ApiResponse::error('Service not found.', null, 404);
        }

        $validator = Validator::make($request->all(), $this->updateRules(), $this->validationMessages());

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $service->update($validator->validated());

            return ApiResponse::success(
                $this->formatServiceResource($service->fresh(['client', 'vendor'])),
                'Service updated successfully.'
            );
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to update service.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $service = Service::query()
            ->whereNotNull('client_id')
            ->find($id);

        if (! $service) {
            return ApiResponse::error('Service not found.', null, 404);
        }

        try {
            $service->delete();

            return ApiResponse::success([
                'id' => $id,
            ], 'Service deleted successfully.');
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to delete service.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function deleteSelected(Request $request): JsonResponse
    {
        $ids = $this->normalizeIds($request->input('ids'));

        if ($ids === []) {
            return ApiResponse::error('Validation failed.', [
                'ids' => ['The ids field is required and must contain at least one valid service id.'],
            ], 422);
        }

        $matchedServices = Service::query()
            ->whereNotNull('client_id')
            ->whereIn('id', $ids)
            ->get(['id']);

        if ($matchedServices->isEmpty()) {
            return ApiResponse::error('No matching services found.', null, 404);
        }

        try {
            Service::query()
                ->whereNotNull('client_id')
                ->whereIn('id', $matchedServices->pluck('id'))
                ->delete();

            return ApiResponse::success([
                'deleted_count' => $matchedServices->count(),
                'deleted_ids' => $matchedServices->pluck('id')->values(),
            ], 'Selected services deleted successfully.');
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to delete selected services.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    private function storeRules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
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
        ];
    }

    private function updateRules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id',
            'service_name' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'remark_text' => 'nullable|string|max:100',
            'remark_color' => 'nullable|in:yellow,red,green,blue,gray|required_with:remark_text',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,expired,pending',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'Selected client does not exist.',
            'services.required' => 'At least one service is required.',
            'services.array' => 'The services field must be an array.',
            'services.min' => 'At least one service is required.',
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
            'vendor_id.required' => 'Please select a vendor.',
            'vendor_id.exists' => 'Selected vendor does not exist.',
            'service_name.required' => 'Service name is required.',
            'remark_color.required_with' => 'Please select a remark color when remark text is provided.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'billing_date.required' => 'Billing date is required.',
            'billing_date.date' => 'Billing date must be a valid date.',
            'status.required' => 'Status is required.',
        ];
    }

    private function resolveActiveTab(?string $activeTab): string
    {
        return in_array($activeTab, self::AVAILABLE_TABS, true) ? $activeTab : 'all';
    }

    private function buildTabCounts(Collection $services): array
    {
        return [
            'all' => $services->count(),
            'upcoming' => $services->where('tab_key', 'upcoming')->count(),
            'active' => $services->where('tab_key', 'active')->count(),
            'inactive' => $services->where('tab_key', 'inactive')->count(),
            'pending' => $services->where('tab_key', 'pending')->count(),
            'expired' => $services->where('tab_key', 'expired')->count(),
        ];
    }

    private function normalizeIds($ids): array
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (! is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function formatServiceResource(Service $service): array
    {
        $service->loadMissing(['client', 'vendor']);

        return [
            'id' => $service->id,
            'client_id' => $service->client_id,
            'client' => $service->client ? [
                'id' => $service->client->id,
                'name' => $service->client->cname,
                'company_name' => $service->client->coname,
                'email' => $service->client->email,
            ] : null,
            'vendor_id' => $service->vendor_id,
            'vendor' => $service->vendor ? [
                'id' => $service->vendor->id,
                'name' => $service->vendor->name,
                'email' => $service->vendor->email,
                'phone' => $service->vendor->phone,
            ] : null,
            'service_name' => $service->service_name,
            'service_details' => $service->service_details,
            'remark_text' => $service->remark_text,
            'remark_color' => $service->remark_color,
            'remark_badge_style' => $service->remark_badge_style,
            'start_date' => optional($service->start_date)->toDateString(),
            'end_date' => optional($service->end_date)->toDateString(),
            'billing_date' => optional($service->billing_date)->toDateString(),
            'status' => $service->status,
            'status_badge' => $service->status_badge,
            'effective_status' => $service->effective_status,
            'status_label' => $service->status_label,
            'effective_status_badge' => $service->effective_status_badge,
            'tab_key' => $service->tab_key,
            'created_at' => optional($service->created_at)->toISOString(),
            'updated_at' => optional($service->updated_at)->toISOString(),
            'deleted_at' => optional($service->deleted_at)->toISOString(),
            'links' => [
                'api' => [
                    'show' => url('/api/v1/services/' . $service->id),
                    'update' => url('/api/v1/services/' . $service->id),
                    'delete' => url('/api/v1/services/' . $service->id),
                ],
            ],
        ];
    }
}

