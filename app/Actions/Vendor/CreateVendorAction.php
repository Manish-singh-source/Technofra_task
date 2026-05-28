<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorData;
use App\Models\Vendor;

class CreateVendorAction
{
    public function execute(VendorData $data): Vendor
    {
        return Vendor::create($data->toModelAttributes());
    }
}

