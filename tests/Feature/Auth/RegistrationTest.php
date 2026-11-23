<?php

namespace Tests\Feature\Auth;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_sets_a_five_day_trial(): void
    {
        $this->travelTo('2026-11-23 12:00:00');

        $this->post('/register', [
            'name' => 'Trial User',
            'email' => 'trial@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $tenant = Tenant::findOrFail(auth()->user()->tenant_id);
        $subscription = Subscription::where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertSame('2026-11-28 12:00:00', $tenant->trial_ends_at?->toDateTimeString());
        $this->assertSame('2026-11-28 12:00:00', $subscription->trial_ends_at?->toDateTimeString());
    }
}
