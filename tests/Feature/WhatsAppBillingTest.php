<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Role;
use App\Models\TenantContext;
use App\Models\User;
use App\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);
    }

    public function test_payment_link_redirects_to_whatsapp_with_prefilled_monthly_message(): void
    {
        $plan = Plan::where('slug', 'starter')->firstOrFail();

        $this->actingAs($this->adminUser())
            ->get(route('billing.pay', ['plan' => $plan, 'interval' => 'monthly']))
            ->assertRedirect('https://wa.me/254712345678?text='.rawurlencode("Hi, I'd like to pay for the Starter plan (monthly) - KES 29.00. Organization: Default Organization."));
    }

    public function test_payment_link_uses_the_yearly_price_for_yearly_billing(): void
    {
        $plan = Plan::where('slug', 'growth')->firstOrFail();

        $this->actingAs($this->adminUser())
            ->get(route('billing.pay', ['plan' => $plan, 'interval' => 'yearly']))
            ->assertRedirect('https://wa.me/254712345678?text='.rawurlencode("Hi, I'd like to pay for the Growth plan (yearly) - KES 790.00. Organization: Default Organization."));
    }

    public function test_payment_link_normalizes_an_international_number(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '+254 712 345 678']);

        $plan = Plan::where('slug', 'starter')->firstOrFail();

        $this->actingAs($this->adminUser())
            ->get(route('billing.pay', ['plan' => $plan, 'interval' => 'monthly']))
            ->assertRedirect('https://wa.me/254712345678?text='.rawurlencode("Hi, I'd like to pay for the Starter plan (monthly) - KES 29.00. Organization: Default Organization."));
    }

    public function test_payment_link_rejects_an_invalid_interval(): void
    {
        $plan = Plan::where('slug', 'starter')->firstOrFail();

        $this->actingAs($this->adminUser())
            ->get(route('billing.pay', ['plan' => $plan, 'interval' => 'weekly']))
            ->assertSessionHasErrors('interval');
    }

    public function test_payment_link_returns_404_for_an_inactive_plan(): void
    {
        $plan = Plan::create([
            'name' => 'Hidden plan',
            'slug' => 'hidden-plan',
            'monthly_price' => 10,
            'yearly_price' => 100,
            'currency' => 'KES',
            'is_active' => false,
        ]);

        $this->actingAs($this->adminUser())
            ->get(route('billing.pay', ['plan' => $plan, 'interval' => 'monthly']))
            ->assertNotFound();
    }

    public function test_payment_link_fails_back_when_whatsapp_is_not_configured(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => null]);

        $plan = Plan::where('slug', 'starter')->firstOrFail();

        $this->actingAs($this->adminUser())
            ->get(route('billing.pay', ['plan' => $plan, 'interval' => 'monthly']))
            ->assertRedirect()
            ->assertSessionHasErrors('plan');
    }

    public function test_guest_cannot_open_a_payment_link(): void
    {
        $plan = Plan::where('slug', 'starter')->firstOrFail();

        $this->get(route('billing.pay', ['plan' => $plan, 'interval' => 'monthly']))
            ->assertRedirect(route('login'));
    }

    private function adminUser(): User
    {
        $role = Role::firstOrCreate([
            'slug' => 'super-admin',
        ], [
            'name' => 'Super Administrator',
            'is_system' => true,
        ]);

        return User::factory()->create([
            'tenant_id' => app(TenantContext::class)->id(),
            'role_id' => $role->id,
        ]);
    }
}
