<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions:id,name')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            $roles->map(fn (Role $role) => $this->formatRoleResource($role))->values(),
            'Roles fetched successfully.'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|distinct|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        $payload = $validator->validated();

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $payload['name'],
                'guard_name' => 'web',
                'status' => 'active',
            ]);

            $permissions = Permission::query()
                ->whereIn('id', $payload['permissions'] ?? [])
                ->get();

            $role->syncPermissions($permissions);
            $this->clearPermissionCache();

            DB::commit();

            return ApiResponse::success(
                $this->formatRoleResource($role->load('permissions:id,name')->loadCount('permissions')),
                'Role created successfully.',
                201
            );
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to create role.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->find($id);

        if (!$role) {
            return ApiResponse::error('Role not found.', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|distinct|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        $payload = $validator->validated();

        DB::beginTransaction();

        try {
            $role->update([
                'name' => $payload['name'],
            ]);

            $permissions = Permission::query()
                ->whereIn('id', $payload['permissions'] ?? [])
                ->get();

            $role->syncPermissions($permissions);
            $this->clearPermissionCache();

            DB::commit();

            return ApiResponse::success(
                $this->formatRoleResource($role->fresh()->load('permissions:id,name')->loadCount('permissions')),
                'Role updated successfully.'
            );
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update role.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->find($id);

        if (!$role) {
            return ApiResponse::error('Role not found.', null, 404);
        }

        DB::beginTransaction();

        try {
            $roleName = $role->name;
            $role->delete();
            $this->clearPermissionCache();

            DB::commit();

            return ApiResponse::success([
                'id' => $id,
                'name' => $roleName,
            ], 'Role deleted successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to delete role.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function destroyAll()
    {
        DB::beginTransaction();

        try {
            $roles = Role::query()
                ->where('guard_name', 'web')
                ->get(['id', 'name']);

            $deletedCount = $roles->count();

            Role::query()
                ->where('guard_name', 'web')
                ->delete();

            $this->clearPermissionCache();

            DB::commit();

            return ApiResponse::success([
                'deleted_count' => $deletedCount,
                'deleted_role_ids' => $roles->pluck('id')->values(),
            ], $deletedCount > 0 ? 'All roles deleted successfully.' : 'No roles found to delete.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to delete roles.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    private function formatRoleResource(Role $role): array
    {
        $role->loadMissing('permissions:id,name');

        return [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'status' => $role->status,
            'permissions_count' => $role->permissions_count ?? $role->permissions->count(),
            'permissions' => $role->permissions
                ->map(fn (Permission $permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ])
                ->values(),
            'created_at' => optional($role->created_at)->toDateTimeString(),
            'updated_at' => optional($role->updated_at)->toDateTimeString(),
        ];
    }

    private function clearPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
