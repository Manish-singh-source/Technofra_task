<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define all modules
        $modules = [
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
        ];

        // Define all actions
        $actions = ['view', 'create', 'edit', 'delete'];

        // Create permissions for each module and action
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . '_' . $module,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create additional specific permissions
        $additionalPermissions = [
            'manage_users',
            'manage_settings',
            'view_reports',
            'export_data',
            'import_data',
            'send_notifications',
            'manage_calendar',
            'view_all_projects',
            'view_own_projects',
            'assign_tasks',
            'view_all_tasks',
            'view_own_tasks',
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Create Super Admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('123456789'),
            ]
        );

        // Assign Super Admin role to the user
        $superAdminUser->assignRole($superAdmin);

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: admin@gmail.com');
        $this->command->info('Password: 123456789');
    }
}
