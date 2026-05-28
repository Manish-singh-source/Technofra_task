<?php

namespace App\Actions\Vendor;

use App\Models\Vendor;

class DeleteVendorAction
{
    public function execute(Vendor $vendor): void
    {
        $vendor->delete();
    }
}

