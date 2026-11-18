<?php

namespace Tests\Feature;

use App\Models\StaffActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_activity_log_can_be_filtered_by_staff_member()
    {
        $viewer = User::factory()->create(['role' => 'admin']);
        $selectedStaff = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);

        StaffActivityLog::create([
            'user_id' => $selectedStaff->id,
            'staff_name' => $selectedStaff->name,
            'staff_email' => $selectedStaff->email,
            'session_id' => 'selected-session',
            'ip_address' => '192.0.2.10',
            'user_agent' => 'Mozilla/5.0 (Linux; Android 13) Chrome/120.0.0.0 Mobile Safari/537.36',
            'logged_in_at' => now(),
        ]);
        StaffActivityLog::create([
            'user_id' => $otherStaff->id,
            'staff_name' => $otherStaff->name,
            'staff_email' => $otherStaff->email,
            'session_id' => 'other-session',
            'ip_address' => '192.0.2.11',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'logged_in_at' => now(),
        ]);

        $response = $this->actingAs($viewer)
            ->get(route('staff.activity-log', ['staff_id' => $selectedStaff->id]));

        $response->assertOk()
            ->assertViewIs('staff.activity-log')
            ->assertSee('192.0.2.10')
            ->assertSee('Android phone')
            ->assertDontSee('192.0.2.11');

        $this->actingAs($viewer)->get(route('staff'))->assertOk();
    }
}
