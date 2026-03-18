<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes (no authentication required)
Route::prefix('/v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('/v1')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
    
    // Staff API routes
    Route::prefix('staff')->group(function () {
        Route::get('/', [StaffController::class, 'apiIndex'])->middleware('permission:view_staff');
        Route::post('/', [StaffController::class, 'apiStore'])->middleware('permission:create_staff');
    });

    // Customer/Client API routes
    Route::prefix('clients')->group(function () {
        Route::get('/', [CustomerController::class, 'apiIndex'])->middleware('permission:view_clients');
        Route::post('/', [CustomerController::class, 'apiStore'])->middleware('permission:create_clients');
    });

    // Permission API routes
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'apiIndex'])->middleware('permission:view_roles');
        Route::get('/grouped', [PermissionController::class, 'apiGroupedPermissions'])->middleware('permission:view_roles');
    });

    // Role API routes
    Route::prefix('roles')->group(function () {
        Route::get('/', function () {
            $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        })->middleware('permission:view_roles');
    });
});

// Fallback route for authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
