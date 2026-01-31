<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $modules = ['renewals', 'leads', 'projects', 'task', 'raise_issue', 'client', 'staff', 'roles'];
        return view('add-role', compact('permissions', 'modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        $modules = ['renewals', 'leads', 'projects', 'task', 'raise_issue', 'client', 'staff', 'roles'];
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('edit-role', compact('role', 'permissions', 'modules', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles')->with('success', 'Role deleted successfully.');
    }
}
