<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    //

    public function index()
    {
        $roles = Role::withCount('permissions')->paginate(10);

        if (!$roles) {
            return ApiResponse::error('No roles found');
        }

        return ApiResponse::success($roles, 'Roles found');
    }

    public function store(Request $request)
    {
        $role = Role::create([
            'name' => $request->name,
        ]);

        if ($request->permissions) {
            $role->permissions()->attach($request->permissions);
        }

        if (!$role) {
            return ApiResponse::error('Failed to create role');
        }

        return ApiResponse::success($role, 'Role created successfully');
    }


    public function update(Request $request, $id) {
        $role = Role::findOrFail($id);

        if (!$role) {
            return ApiResponse::error('Role not found');
        }

        $role->update([
            'name' => $request->name,
        ]);

        if($request->permissions) {
            $role->permissions()->detach();
            $role->permissions()->attach($request->permissions);
        }

        return ApiResponse::success($role, 'Role updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if(!$role) {
            return ApiResponse::error('Role not found');
        }

        // $role->permissions()->detach();
        $role->delete();

        return ApiResponse::success(null, 'Role deleted successfully');
    }
}
