<?php

namespace App\Services\Vendor;

use App\Actions\VendorService\CreateVendorServiceAction;
use App\Actions\VendorService\DeleteVendorServiceAction;
use App\Actions\VendorService\UpdateVendorServiceAction;
use App\DTOs\VendorService\VendorServiceData;
use App\DTOs\VendorService\VendorServiceFilterData;
use App\Helpers\RenewalStatusHelper;
use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class VendorServiceManagementService
{
    public function __construct(
        private CreateVendorServiceAction $createAction,
        private UpdateVendorServiceAction $updateAction,
        private DeleteVendorServiceAction $deleteAction,
    ) {}

    public function canViewAll(?User $user): bool
    {
        return (bool) ($user && ($user->hasRole('admin') || $user->hasRole('super_admin2') || $user->hasRole('super_admin')));
    }

    public function scopedServicesQuery(?User $user): Builder
    {
        $query = VendorService::query();
        if (! $this->canViewAll($user)) {
            $query->whereIn('vendor_id', Service::query()
                ->whereNotNull('vendor_id')
                ->where('client_id', optional($user)->id)
                ->select('vendor_id'));
        }

        return $query;
    }

    public function scopedVendorsQuery(?User $user): Builder
    {
        $query = Vendor::query();
        if (! $this->canViewAll($user)) {
            $query->whereIn('id', Service::query()
                ->whereNotNull('vendor_id')
                ->where('client_id', optional($user)->id)
                ->select('vendor_id'));
        }

        return $query;
    }

    /**
     * @return array{services: Collection<int,VendorService>, tabCounts: array<string,int>, activeTab: string}
     */
    public function listForWeb(?User $user, VendorServiceFilterData $filters): array
    {
        RenewalStatusHelper::markExpiredVendorRenewals();

        $today = Carbon::today()->toDateString();
        $fiveDaysFromNow = Carbon::today()->addDays(5)->toDateString();

        $query = $this->scopedServicesQuery($user)->with('vendor');
        if ($filters->fromDate) {
            $query->where('billing_date', '>=', $filters->fromDate);
        }
        if ($filters->toDate) {
            $query->where('billing_date', '<=', $filters->toDate);
        }

        $services = $query
            ->orderByRaw(
                'CASE WHEN end_date < ? THEN 0 WHEN end_date BETWEEN ? AND ? THEN 1 ELSE 2 END',
                [$today, $today, $fiveDaysFromNow]
            )
            ->latest('updated_at')
            ->orderBy('end_date')
            ->get();

        return [
            'services' => $services,
            'activeTab' => $filters->tab,
            'tabCounts' => [
                'all' => $services->count(),
                'upcoming' => $services->where('tab_key', 'upcoming')->count(),
                'active' => $services->where('tab_key', 'active')->count(),
                'inactive' => $services->where('tab_key', 'inactive')->count(),
                'pending' => $services->where('tab_key', 'pending')->count(),
                'expired' => $services->where('tab_key', 'expired')->count(),
            ],
        ];
    }

    public function listForApi(VendorServiceFilterData $filters): LengthAwarePaginator
    {
        RenewalStatusHelper::markExpiredVendorRenewals();

        $today = Carbon::today()->toDateString();
        $upcomingUntil = Carbon::today()->addDays(7)->toDateString();

        return VendorService::query()
            ->with(['vendor:id,name,email,phone'])
            ->select('id', 'vendor_id', 'service_name', 'service_details', 'remark_text', 'remark_color', 'plan_type', 'start_date', 'end_date', 'billing_date', 'status', 'created_at', 'updated_at')
            ->when($filters->fromDate, fn ($q) => $q->whereDate('billing_date', '>=', $filters->fromDate))
            ->when($filters->toDate, fn ($q) => $q->whereDate('billing_date', '<=', $filters->toDate))
            ->when($filters->tab !== 'all', function ($query) use ($filters, $today, $upcomingUntil) {
                match ($filters->tab) {
                    'upcoming' => $query->where('status', 'active')->whereDate('end_date', '>=', $today)->whereDate('end_date', '<=', $upcomingUntil),
                    'active' => $query->where('status', 'active')->where(function ($nested) use ($upcomingUntil) {
                        $nested->whereNull('end_date')->orWhereDate('end_date', '>', $upcomingUntil);
                    }),
                    'expired' => $query->where('status', 'expired'),
                    'inactive' => $query->where('status', 'inactive'),
                    'pending' => $query->where('status', 'pending'),
                    default => null,
                };
            })
            ->orderByDesc('created_at')
            ->paginate($filters->perPage);
    }

    public function findForWebOrFail(?User $user, int $id, array $with = []): VendorService
    {
        return $this->scopedServicesQuery($user)->with($with)->findOrFail($id);
    }

    public function findForApiOrFail(int $id): VendorService
    {
        return VendorService::query()->with('vendor')->findOrFail($id);
    }

    public function create(VendorServiceData $data): VendorService
    {
        return $this->createAction->execute($data);
    }

    public function update(VendorService $service, VendorServiceData $data): VendorService
    {
        return $this->updateAction->execute($service, $data);
    }

    public function delete(VendorService $service): void
    {
        $this->deleteAction->execute($service);
    }

    public function deleteSelected(?User $user, array $ids): int
    {
        return $this->scopedServicesQuery($user)
            ->whereIn('id', array_map('intval', $ids))
            ->delete();
    }

    public function ensureVendorAccess(?User $user, int $vendorId): void
    {
        if ($this->canViewAll($user)) {
            return;
        }

        $hasVendorAccess = $this->scopedVendorsQuery($user)->where('id', $vendorId)->exists();
        abort_if(! $hasVendorAccess, 403, 'Unauthorized vendor selection.');
    }
}
