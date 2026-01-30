<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

        return redirect()->back()->with('success', 'Role created successfully.');
    }
}
