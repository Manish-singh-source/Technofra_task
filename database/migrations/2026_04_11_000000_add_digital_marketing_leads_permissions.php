<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add permissions for digital_marketing_leads
        $permissions = [
            ['name' => 'view_digital_marketing_leads', 'guard_name' => 'web'],
            ['name' => 'create_digital_marketing_leads', 'guard_name' => 'web'],
            ['name' => 'edit_digital_marketing_leads', 'guard_name' => 'web'],
            ['name' => 'delete_digital_marketing_leads', 'guard_name' => 'web'],
            // Add missing book_calls permissions
            ['name' => 'create_book_calls', 'guard_name' => 'web'],
            ['name' => 'edit_book_calls', 'guard_name' => 'web'],
            ['name' => 'delete_book_calls', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                $permission
            );
        }

        // Update super_admin role to have all permissions
        $superAdminRole = DB::table('roles')->where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $allPermissions = DB::table('permissions')->pluck('id');
            DB::table('role_has_permissions')
                ->where('role_id', $superAdminRole->id)
                ->delete();
            
            foreach ($allPermissions as $permissionId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $superAdminRole->id
                ]);
            }
        }

        // Update admin role
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            $adminPermissions = [
                'view_users', 'create_users', 'edit_users',
                'view_roles', 'create_roles', 'edit_roles',
                'view_projects', 'create_projects', 'edit_projects',
                'view_clients', 'create_clients', 'edit_clients',
                'view_leads', 'create_leads', 'edit_leads',
                'view_dashboard', 'view_dashboard_welcome', 'view_calendar', 'view_renewals',
                'view_vendors', 'create_vendors', 'edit_vendors',
                'view_services', 'create_services', 'edit_services',
                'view_staff', 'create_staff', 'edit_staff',
                'view_tasks', 'create_tasks', 'edit_tasks',
                'view_book_calls', 'create_book_calls', 'edit_book_calls', 'delete_book_calls',
                'view_digital_marketing_leads', 'create_digital_marketing_leads', 'edit_digital_marketing_leads', 'delete_digital_marketing_leads',
                'view_general_settings', 'view_company_information', 'view_email_settings',
            ];
            
            DB::table('role_has_permissions')
                ->where('role_id', $adminRole->id)
                ->delete();

            foreach ($adminPermissions as $permName) {
                $perm = DB::table('permissions')->where('name', $permName)->first();
                if ($perm) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $perm->id,
                        'role_id' => $adminRole->id
                    ]);
                }
            }
        }

        // Update manager role
        $managerRole = DB::table('roles')->where('name', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = [
                'view_projects', 'create_projects', 'edit_projects',
                'view_clients', 'create_clients', 'edit_clients',
                'view_leads', 'create_leads', 'edit_leads',
                'view_dashboard', 'view_dashboard_welcome', 'view_calendar', 'view_renewals',
                'view_vendors', 'view_services', 'view_staff',
                'view_tasks', 'create_tasks', 'edit_tasks',
                'view_book_calls', 'create_book_calls', 'edit_book_calls', 'delete_book_calls',
                'view_digital_marketing_leads', 'create_digital_marketing_leads', 'edit_digital_marketing_leads', 'delete_digital_marketing_leads',
                'view_general_settings', 'view_company_information', 'view_email_settings',
            ];
            
            DB::table('role_has_permissions')
                ->where('role_id', $managerRole->id)
                ->delete();

            foreach ($managerPermissions as $permName) {
                $perm = DB::table('permissions')->where('name', $permName)->first();
                if ($perm) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $perm->id,
                        'role_id' => $managerRole->id
                    ]);
                }
            }
        }

        // Update staff role
        $staffRole = DB::table('roles')->where('name', 'staff')->first();
        if ($staffRole) {
            $staffPermissions = [
                'view_projects', 'view_clients', 'view_leads',
                'view_dashboard', 'view_tasks', 'create_tasks', 'edit_tasks',
                'view_book_calls', 'create_book_calls', 'edit_book_calls', 'delete_book_calls',
                'view_digital_marketing_leads', 'create_digital_marketing_leads', 'edit_digital_marketing_leads', 'delete_digital_marketing_leads',
            ];
            
            DB::table('role_has_permissions')
                ->where('role_id', $staffRole->id)
                ->delete();

            foreach ($staffPermissions as $permName) {
                $perm = DB::table('permissions')->where('name', $permName)->first();
                if ($perm) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $perm->id,
                        'role_id' => $staffRole->id
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove digital_marketing_leads permissions
        DB::table('permissions')->whereIn('name', [
            'view_digital_marketing_leads',
            'create_digital_marketing_leads',
            'edit_digital_marketing_leads',
            'delete_digital_marketing_leads',
            'create_book_calls',
            'edit_book_calls',
            'delete_book_calls',
        ])->delete();
    }
};