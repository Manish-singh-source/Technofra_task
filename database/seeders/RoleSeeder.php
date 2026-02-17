<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define all permissions
        $permissions = [
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Role Management
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            
            // Project Management
            'view_projects',
            'create_projects',
            'edit_projects',
            'delete_projects',
            
            // Client Management
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',
            
            // Lead Management
            'view_leads',
            'create_leads',
            'edit_leads',
            'delete_leads',
            
            // Dashboard
            'view_dashboard',
            'view_renewals',
            
            // Vendor Management
            'view_vendors',
            'create_vendors',
            'edit_vendors',
            'delete_vendors',
            
            // Service Management
            'view_services',
            'create_services',
            'edit_services',
            'delete_services',
            
            // Staff Management
            'view_staff',
            'create_staff',
            'edit_staff',
            'delete_staff',
            
            // Task Management
            'view_tasks',
            'create_tasks',
            'edit_tasks',
            'delete_tasks',
            
            // Settings Management
            'view_general_settings',
            'view_company_information',
            'view_email_settings',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName], ['name' => $permissionName]);
        }

        // Define roles with their permissions
        $roles = [
            'super_admin' => [
                'view_users',
                'create_users',
                'edit_users',
                'delete_users',
                'view_roles',
                'create_roles',
                'edit_roles',
                'delete_roles',
                'view_projects',
                'create_projects',
                'edit_projects',
                'delete_projects',
                'view_clients',
                'create_clients',
                'edit_clients',
                'delete_clients',
                'view_leads',
                'create_leads',
                'edit_leads',
                'delete_leads',
                'view_dashboard',
                'view_renewals',
                'view_vendors',
                'create_vendors',
                'edit_vendors',
                'delete_vendors',
                'view_services',
                'create_services',
                'edit_services',
                'delete_services',
                'view_staff',
                'create_staff',
                'edit_staff',
                'delete_staff',
                'view_tasks',
                'create_tasks',
                'edit_tasks',
                'delete_tasks',
                'view_general_settings',
                'view_company_information',
                'view_email_settings',
            ],
            'admin' => [
                'view_users',
                'create_users',
                'edit_users',
                'view_roles',
                'create_roles',
                'edit_roles',
                'view_projects',
                'create_projects',
                'edit_projects',
                'view_clients',
                'create_clients',
                'edit_clients',
                'view_leads',
                'create_leads',
                'edit_leads',
                'view_dashboard',
                'view_renewals',
                'view_vendors',
                'create_vendors',
                'edit_vendors',
                'view_services',
                'create_services',
                'edit_services',
                'view_staff',
                'create_staff',
                'edit_staff',
                'view_tasks',
                'create_tasks',
                'edit_tasks',
                'view_general_settings',
                'view_company_information',
                'view_email_settings',
            ],
            'manager' => [
                'view_projects',
                'create_projects',
                'edit_projects',
                'view_clients',
                'create_clients',
                'edit_clients',
                'view_leads',
                'create_leads',
                'edit_leads',
                'view_dashboard',
                'view_renewals',
                'view_vendors',
                'view_services',
                'view_staff',
                'view_tasks',
                'create_tasks',
                'edit_tasks',
                'view_general_settings',
                'view_company_information',
                'view_email_settings',
            ],
            'customer' => [
                'view_renewals',
                'view_projects',
                'view_client',
            ],
            'staff' => [
                'view_projects',
                'view_clients',
                'view_leads',
                'view_dashboard',
                'view_tasks',
                'create_tasks',
                'edit_tasks',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName], ['name' => $roleName]);
            
            // Sync permissions (update operation - removes old permissions not in this list)
            $permissionIds = Permission::whereIn('name', $rolePermissions)->pluck('id')->toArray();
            $role->syncPermissions($permissionIds);
        }
    }
}
