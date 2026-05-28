<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorCollection extends ResourceCollection
{
    public $collects = VendorResource::class;

    public function toArray(Request $request): array
    {
        return [
            'items' => $this->collection,
        ];
    }

    public function with($request): array
    {
        return [
            'success' => true,
            'message' => 'Vendors retrieved successfully',
        ];
    }
}

