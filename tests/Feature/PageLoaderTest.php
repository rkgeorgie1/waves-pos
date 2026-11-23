<?php

namespace Tests\Feature;

use App\Models\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageLoaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_pages_include_the_page_loader(): void
    {
        $user = User::factory()->create([
            'tenant_id' => app(TenantContext::class)->id(),
            'is_platform_admin' => true,
        ]);

        $this->actingAs($user)
            ->get('/platform')
            ->assertOk()
            ->assertSee('id="posPageLoader"', false)
            ->assertSee('pos-page-loader-spinner', false)
            ->assertSee('Loading…', false);
    }

    public function test_guest_pages_do_not_include_the_page_loader(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('id="posPageLoader"', false);
    }
}
