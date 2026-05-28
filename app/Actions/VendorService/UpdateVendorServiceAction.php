<?php

namespace App\Actions\VendorService;

use App\DTOs\VendorService\VendorServiceData;
use App\Models\VendorService;

class UpdateVendorServiceAction
{
    public function execute(VendorService $service, VendorServiceData $data): VendorService
    {
        $service->update($data->toModelAttributes());

        return $service->refresh();
    }
}

