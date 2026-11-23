<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_public_landing_page_with_plans_and_account_links(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Start free trial')
            ->assertSee('Every account starts with a 5-day trial.')
            ->assertDontSee('14-day')
            ->assertDontSee('14 days')
            ->assertSee(route('register'), false)
            ->assertSee(route('login'), false)
            ->assertSee('Free')
            ->assertSee('Starter')
            ->assertSee('Growth')
            ->assertSee('Enterprise');
    }

    public function test_landing_page_uses_kes_currency_from_admin_settings(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('KES 29.00')
            ->assertSee('KES 79.00')
            ->assertSee('KES 199.00')
            ->assertSee('KES 4,820')
            ->assertDontSee('$29.00')
            ->assertDontSee('$79.00')
            ->assertDontSee('$199.00')
            ->assertDontSee('$4,820');
    }

    public function test_landing_page_hides_inactive_plans(): void
    {
        Plan::create([
            'name' => 'Hidden Plan',
            'slug' => 'hidden',
            'description' => 'Not available to new merchants.',
            'monthly_price' => 999,
            'yearly_price' => 9990,
            'features' => [],
            'is_active' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('Hidden Plan');
    }

    public function test_authenticated_user_sees_dashboard_and_billing_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Open dashboard')
            ->assertSee(route('dashboard'), false)
            ->assertSee(route('billing.index'), false);
    }

    public function test_pricing_page_shows_whatsapp_links_for_paid_plans_when_configured(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);
        $starter = Plan::where('slug', 'starter')->firstOrFail();
        $growth = Plan::where('slug', 'growth')->firstOrFail();

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee(WhatsApp::chatUrl(WhatsApp::paymentMessage($starter, 'monthly')), false)
            ->assertSee(WhatsApp::chatUrl(WhatsApp::paymentMessage($starter, 'yearly')), false)
            ->assertSee(WhatsApp::chatUrl(WhatsApp::paymentMessage($growth, 'monthly')), false)
            ->assertSee(WhatsApp::chatUrl(WhatsApp::paymentMessage($growth, 'yearly')), false)
            ->assertSee('Chat about monthly')
            ->assertSee('Ask about yearly')
            ->assertSee('Chat with admin on WhatsApp');
    }

    public function test_pricing_page_hides_whatsapp_links_when_the_number_is_not_configured(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Chat about monthly')
            ->assertDontSee('Ask about yearly')
            ->assertDontSee('wa.me')
            ->assertDontSee('Chat with admin on WhatsApp');
    }
}
