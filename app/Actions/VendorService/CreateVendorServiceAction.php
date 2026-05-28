<?php

namespace App\Actions\VendorService;

use App\DTOs\VendorService\VendorServiceData;
use App\Models\VendorService;

class CreateVendorServiceAction
{
    public function execute(VendorServiceData $data): VendorService
    {
        return VendorService::create($data->toModelAttributes());
    }
}

