<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        $modules = ['renewals', 'leads', 'projects', 'tasks', 'raise_issue', 'clients', 'staff', 'roles', 'permissions', 'services', 'vendors', 'dashboard', 'book_calls', 'digital_marketing_leads'];
        $settingsPermissions = [
            'view_general_settings',
            'view_company_information',
            'view_email_settings'
        ];
        $welcomePermissions = [
            'view_dashboard_welcome'
        ];
        $calendarPermissions = [
            'view_calendar'
        ];

        return view('add-role', compact('permissions', 'modules', 'settingsPermissions', 'welcomePermissions', 'calendarPermissions'));
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

        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        $modules = ['renewals', 'leads', 'projects', 'tasks', 'raise_issue', 'clients', 'staff', 'roles', 'permissions', 'services', 'vendors', 'dashboard', 'book_calls', 'digital_marketing_leads'];
        $settingsPermissions = [
            'view_general_settings',
            'view_company_information',
            'view_email_settings'
        ];
        $welcomePermissions = [
            'view_dashboard_welcome'
        ];
        $calendarPermissions = [
            'view_calendar'
        ];
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('edit-role', compact('role', 'permissions', 'modules', 'settingsPermissions', 'welcomePermissions', 'calendarPermissions', 'rolePermissions'));
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

        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles')->with('success', 'Role deleted successfully.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = explode(',', $request->ids);

        if (empty($ids)) {
            return redirect()->route('roles')->with('error', 'No roles selected for deletion.');
        }

        try {
            foreach ($ids as $id) {
                $role = Role::find($id);
                if ($role) {
                    $role->delete();
                }
            }

            Cache::forget('spatie.permission.cache');

            return redirect()->route('roles')->with('success', 'Selected roles deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('roles')->with('error', 'Failed to delete selected roles: ' . $e->getMessage());
        }
    }
}

