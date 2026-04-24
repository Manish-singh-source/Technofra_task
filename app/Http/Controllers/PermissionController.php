<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('access-control.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $modules = $this->getModules();
        return view('access-control.permissions.create', compact('modules'));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing a permission.
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('access-control.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $id,
            'guard_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }







    // not used below




    /**
     * Assign permission to a role.
     */
    public function assignToRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $role = Role::findById($request->role_id);
        $permission = Permission::findById($request->permission_id);

        $role->givePermissionTo($permission);

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return response()->json(['success' => 'Permission assigned to role successfully.']);
    }

    /**
     * Remove permission from a role.
     */
    public function removeFromRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $role = Role::findById($request->role_id);
        $permission = Permission::findById($request->permission_id);

        $role->revokePermissionTo($permission);

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return response()->json(['success' => 'Permission removed from role successfully.']);
    }

    /**
     * Get all modules for permission grouping.
     */
    private function getModules()
    {
        return [
            'renewals',
            'leads',
            'projects',
            'tasks',
            'raise_issue',
            'clients',
            'staff',
            'roles',
            'permissions',
            'services',
            'vendors',
            'dashboard',
            'calendar',
        ];
    }

    
}


