<?php

namespace Tests\Feature;

use App\Models\TenantContext;
use App\Models\User;
use App\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppFloatTest extends TestCase
{
    use RefreshDatabase;

    public function test_floating_chat_button_appears_on_the_landing_page_when_configured(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Chat with admin on WhatsApp')
            ->assertSee(WhatsApp::chatUrl('Hi, I would like to ask about payments for Airwaves POS.'), false);
    }

    public function test_floating_chat_button_is_hidden_on_the_landing_page_when_not_configured(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Chat with admin on WhatsApp')
            ->assertDontSee('whatsapp-float');
    }

    public function test_floating_chat_button_is_hidden_on_guest_pages(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Chat with admin on WhatsApp')
            ->assertDontSee('whatsapp-float');
    }

    public function test_floating_chat_button_is_hidden_on_authenticated_pages(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);

        $this->actingAs($this->user())
            ->get(route('billing.index'))
            ->assertOk()
            ->assertDontSee('Chat with admin on WhatsApp')
            ->assertDontSee('whatsapp-float');
    }

    private function user(): User
    {
        return User::factory()->create([
            'tenant_id' => app(TenantContext::class)->id(),
        ]);
    }
}
