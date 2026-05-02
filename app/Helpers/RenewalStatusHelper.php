<?php

namespace App\Helpers;

use App\Models\Service;
use App\Models\VendorService;
use Carbon\Carbon;

class RenewalStatusHelper
{
    public static function markExpiredClientRenewals(): int
    {
        return Service::query()
            ->whereNotNull('client_id')
            ->whereDate('end_date', '<', Carbon::today())
            ->where('status', '!=', 'inactive')
            ->where('status', '!=', 'expired')
            ->update([
                'status' => 'expired',
            ]);
    }

    public static function markExpiredVendorRenewals(): int
    {
        return VendorService::query()
            ->whereDate('end_date', '<', Carbon::today())
            ->where('status', '!=', 'inactive')
            ->where('status', '!=', 'expired')
            ->update([
                'status' => 'expired',
            ]);
    }
}
