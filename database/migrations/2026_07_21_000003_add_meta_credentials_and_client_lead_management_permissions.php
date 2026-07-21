<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'manage_meta_credentials',
            'view_leads_management',
            'view_leads',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = ['super_admin', 'super-admin', 'admin', 'client', 'customer'];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($permissions);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $roles = Role::query()
            ->whereIn('name', ['super_admin', 'super-admin', 'admin', 'client', 'customer'])
            ->get();

        foreach ($roles as $role) {
            $role->revokePermissionTo('manage_meta_credentials');
            $role->revokePermissionTo('view_leads_management');
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};