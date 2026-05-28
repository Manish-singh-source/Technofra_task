<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorData;
use App\Models\Vendor;

class UpdateVendorAction
{
    public function execute(Vendor $vendor, VendorData $data): Vendor
    {
        $vendor->update($data->toModelAttributes());

        return $vendor->refresh();
    }
}

