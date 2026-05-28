<?php

namespace App\Services\Vendor;

use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;

class VendorAccessService
{
    public function canViewAll(?User $user): bool
    {
        return (bool) ($user && ($user->hasRole('admin') || $user->hasRole('super_admin2') || $user->hasRole('super_admin')));
    }

    public function scopedQuery(?User $user): Builder
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
}

