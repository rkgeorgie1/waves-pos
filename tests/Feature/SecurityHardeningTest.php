<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_added_to_web_responses(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeaderContains('Content-Security-Policy', "frame-ancestors 'self'");
        $response->assertHeaderContains('Content-Security-Policy', "form-action 'self' ".config('app.url'));
        $response->assertHeaderContains('Permissions-Policy', 'geolocation=()');
    }

    public function test_secure_request_receives_hsts_header(): void
    {
        $this->get('https://localhost/login')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_forwarded_https_request_receives_hsts_header(): void
    {
        $this->get(route('login'), ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_account_is_locked_after_repeated_failed_logins(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < User::MAX_FAILED_LOGIN_ATTEMPTS; $attempt++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->assertSame(User::MAX_FAILED_LOGIN_ATTEMPTS, $user->refresh()->failed_login_attempts);
        $this->assertTrue($user->isLocked());

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('temporarily locked', (string) session('errors')->first('email'));
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login_locked',
            'user_id' => $user->id,
        ]);
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['failed_login_attempts' => 3])->save();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame(0, $user->refresh()->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_expired_lockout_allows_login(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'failed_login_attempts' => User::MAX_FAILED_LOGIN_ATTEMPTS,
            'locked_until' => now()->subMinute(),
        ])->save();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->refresh()->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }
}
