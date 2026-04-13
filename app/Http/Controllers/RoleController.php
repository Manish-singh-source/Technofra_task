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
        $activeRoles = Role::where('status', 'active')->count();
        $inactiveRoles = Role::where('status', 'inactive')->count();
        $permissionsCount = Permission::count();
        return view('access-control.roles.index', compact('roles', 'activeRoles', 'inactiveRoles', 'permissionsCount'));
    }

    public function create()
    {
        $permissions = Permission::all();

        // $modules = $permissions
        //     ->pluck('name')                 // get only names
        //     ->map(function ($name) {
        //         return explode('_', $name)[1] ?? null; // get module part
        //     })
        //     ->filter()                      // remove nulls
        //     ->unique()                      // get unique modules
        //     ->values();                     // reset index

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

        return view('access-control.roles.create', compact('permissions', 'modules', 'settingsPermissions', 'welcomePermissions', 'calendarPermissions'));
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

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
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

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        Cache::forget('spatie.permission.cache');

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = explode(',', $request->ids);

        if (empty($ids)) {
            return redirect()->route('roles.index')->with('error', 'No roles selected for deletion.');
        }

        try {
            foreach ($ids as $id) {
                $role = Role::find($id);
                if ($role) {
                    $role->delete();
                }
            }

            Cache::forget('spatie.permission.cache');

            return redirect()->route('roles.index')->with('success', 'Selected roles deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')->with('error', 'Failed to delete selected roles: ' . $e->getMessage());
        }
    }
}
