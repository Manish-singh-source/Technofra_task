<?php

use App\BDUI\Controllers\BDUIController;
use Illuminate\Support\Facades\Route;

Route::prefix('ui/v1')->group(function () {
    // Add auth middleware here later if needed:
    // Route::middleware('auth:sanctum')->group(function () { ... });
    Route::get('/dashboard', [BDUIController::class, 'dashboard']);
});

