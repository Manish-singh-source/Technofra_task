<?php

namespace Tests\Feature;

use App\Models\StaffActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_registration_form()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth-basic-signup');
    }

    /** @test */
    public function user_can_view_login_form()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth-basic-signin');
    }

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function user_cannot_register_with_invalid_data()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
            'terms' => ''
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'terms']);
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function staff_login_and_logout_are_recorded_with_device_details()
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        ])->post('/login', [
            'email' => $staff->email,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $log = StaffActivityLog::query()->where('user_id', $staff->id)->firstOrFail();
        $this->assertSame('Windows desktop', $log->device_name);
        $this->assertSame('Google Chrome', $log->browser_name);
        $this->assertNotEmpty($log->ip_address);
        $this->assertNotNull($log->logged_in_at);

        $this->post('/logout')->assertRedirect('/login');

        $this->assertNotNull($log->fresh()->logged_out_at);
    }

    /** @test */
    public function staff_login_uses_forwarded_ip_only_from_a_configured_proxy()
    {
        config(['trustedproxy.proxies' => '10.0.0.1']);

        $staff = User::factory()->create([
            'role' => 'staff',
            'password' => Hash::make('password123'),
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.25',
        ])->post('/login', [
            'email' => $staff->email,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertSame(
            '203.0.113.25',
            StaffActivityLog::query()->where('user_id', $staff->id)->value('ip_address')
        );
    }

    /** @test */
    public function staff_login_ignores_forwarded_ip_from_an_untrusted_source()
    {
        config(['trustedproxy.proxies' => null]);

        $staff = User::factory()->create([
            'role' => 'staff',
            'password' => Hash::make('password123'),
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.10',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.25',
        ])->post('/login', [
            'email' => $staff->email,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertSame(
            '198.51.100.10',
            StaffActivityLog::query()->where('user_id', $staff->id)->value('ip_address')
        );
    }


    /** @test */
    public function user_can_login_with_remember_me_enabled()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
            'remember' => '1'
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertCookie(Auth::getRecallerName());
        $this->assertAuthenticated();
    }
    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** @test */
    public function guest_cannot_access_protected_routes()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_is_redirected_from_root_to_login()
    {
        $response = $this->get('/');

        // Root redirects to dashboard, which redirects to login
        $response->assertRedirect('/dashboard');

        // Follow the redirect to dashboard
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_protected_routes()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }
}
