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
        // Create 'customer' role if it doesn't exist
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Assign basic view permissions to customer role
        $permissions = [
            'view_renewals',
            'view_projects',
            'view_client',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $customerRole->givePermissionTo($permission);
            }
        }
    }
}
