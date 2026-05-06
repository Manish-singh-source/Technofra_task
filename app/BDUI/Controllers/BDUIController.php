<?php

namespace App\BDUI\Controllers;

use App\BDUI\Services\SchemaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BDUIController extends Controller
{
    public function __construct(private readonly SchemaService $schemaService) {}

    public function dashboard(): JsonResponse
    {
        return response()->json(
            $this->schemaService->getScreen('dashboard')
        );
    }
}

