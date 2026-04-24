<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    //
    /**
     * API: Get all permissions.
     */
    public function index()
    {
        $permissions = Permission::all();
        
        return ApiResponse::success($permissions, 'Permissions retrieved successfully');
    }

    /**
     * API: Get permissions grouped by module.
     */
    public function apiGroupedPermissions()
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('_', $permission->name);
            $action = $parts[0] ?? 'other';
            $module = implode('_', array_slice($parts, 1)) ?: 'other';

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }
}
