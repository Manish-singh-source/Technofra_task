<?php

namespace App\Actions\Vendor;

use App\Enums\VendorStatus;
use App\Models\Vendor;

class ToggleVendorStatusAction
{
    public function execute(Vendor $vendor, string|int|null $status): Vendor
    {
        $vendor->status = VendorStatus::fromMixed($status)->value;
        $vendor->save();

        return $vendor->refresh();
    }
}

