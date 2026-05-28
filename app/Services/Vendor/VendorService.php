<?php

namespace App\Services\Vendor;

use App\Actions\Vendor\BulkUploadVendorsAction;
use App\Actions\Vendor\CreateVendorAction;
use App\Actions\Vendor\DeleteVendorAction;
use App\Actions\Vendor\ToggleVendorStatusAction;
use App\Actions\Vendor\UpdateVendorAction;
use App\DTOs\Vendor\VendorData;
use App\DTOs\Vendor\VendorFilterData;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class VendorService
{
    public function __construct(
        private VendorAccessService $accessService,
        private CreateVendorAction $createVendorAction,
        private UpdateVendorAction $updateVendorAction,
        private DeleteVendorAction $deleteVendorAction,
        private ToggleVendorStatusAction $toggleVendorStatusAction,
        private BulkUploadVendorsAction $bulkUploadVendorsAction,
    ) {}

    public function listForWeb(?User $user): Collection
    {
        return $this->accessService->scopedQuery($user)->latest()->get();
    }

    public function listForApi(?User $user, VendorFilterData $filters): LengthAwarePaginator
    {
        return $this->accessService->scopedQuery($user)
            ->when($filters->search, function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate($filters->perPage);
    }

    public function findOrFail(?User $user, int $vendorId): Vendor
    {
        return $this->accessService->scopedQuery($user)->findOrFail($vendorId);
    }

    public function create(VendorData $data): Vendor
    {
        return $this->createVendorAction->execute($data);
    }

    public function update(Vendor $vendor, VendorData $data): Vendor
    {
        return $this->updateVendorAction->execute($vendor, $data);
    }

    public function delete(Vendor $vendor): void
    {
        $this->deleteVendorAction->execute($vendor);
    }

    public function deleteSelected(?User $user, array $ids): int
    {
        return $this->accessService->scopedQuery($user)
            ->whereIn('id', array_map('intval', $ids))
            ->delete();
    }

    public function toggleStatus(Vendor $vendor, string|int|null $status): Vendor
    {
        return $this->toggleVendorStatusAction->execute($vendor, $status);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function bulkUpload(UploadedFile $file): array
    {
        return $this->bulkUploadVendorsAction->execute($file);
    }
}

