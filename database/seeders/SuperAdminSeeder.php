<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Super Admin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to Super Admin role
        $superAdminRole->syncPermissions($permissions);

        // Optionally, create a Super Admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('123456789'), // Change this to a secure password
            ]
        );

        // Assign Super Admin role to the user
        $superAdminUser->assignRole($superAdminRole);
    }
}