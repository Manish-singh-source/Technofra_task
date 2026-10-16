<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_adds_demo_records_without_mutating_existing_data_and_is_repeatable(): void
    {
        $existingUser = User::create([
            'first_name' => 'Keep',
            'last_name' => 'Existing',
            'email' => 'existing.user@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        DB::table('vendors')->insert([
            'name' => 'Existing Vendor',
            'email' => 'existing.vendor@example.com',
            'phone' => '9000000000',
            'address' => 'Keep this row',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $seeder = new UserSeeder();
        $seeder->run();

        $this->assertSame('Keep', $existingUser->fresh()->first_name);
        $this->assertSame('Keep this row', DB::table('vendors')->where('email', 'existing.vendor@example.com')->value('address'));
        $this->assertSame(20, User::whereIn('role', ['staff', 'client'])->count());
        $this->assertSeededModuleCounts();

        $seeder->run();

        $this->assertSame(21, User::count());
        $this->assertSame(21, DB::table('vendors')->count());
        $this->assertSame('Keep this row', DB::table('vendors')->where('email', 'existing.vendor@example.com')->value('address'));
        $this->assertSeededModuleCounts();
    }

    private function assertSeededModuleCounts(): void
    {
        foreach (
            [
                'vendor_services',
                'services',
                'amc_services',
                'amc_service_details',
                'projects',
                'tasks',
                'leads',
                'assigned_leads',
                'client_issues',
                'todos',
                'calendar_events',
                'bookcall',
                'digital_marketing_leads',
                'webapp_leads',
                'jobapplication',
                'contactform',
                'google_leads',
                'meta_leads',
            ] as $table
        ) {
            $this->assertSame(20, DB::table($table)->count(), "Expected 20 demo rows in {$table}.");
        }

        $this->assertSame(20, DB::table('user_address')->count());
        $this->assertSame(20, DB::table('client_business_details')->count());
    }
}
