<?php

namespace App\Actions\VendorService;

use App\Models\VendorService;

class DeleteVendorServiceAction
{
    public function execute(VendorService $service): void
    {
        $service->delete();
    }
}

