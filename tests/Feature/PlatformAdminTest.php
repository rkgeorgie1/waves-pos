<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanLimits;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantContext;
use App\Models\User;
use App\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_platform_admin_cannot_access_the_console(): void
    {
        $this->actingAs($this->user(false))
            ->get('/platform')
            ->assertForbidden();
    }

    public function test_platform_admin_can_view_the_console(): void
    {
        $this->actingAs($this->user())
            ->get('/platform')
            ->assertOk();
    }

    public function test_platform_admin_can_search_organizations_by_user_email(): void
    {
        $matching = $this->tenant('email-match-org');
        $other = $this->tenant('email-other-org');

        User::factory()->create([
            'tenant_id' => $matching->id,
            'email' => 'merchant@example.com',
        ]);

        User::factory()->create([
            'tenant_id' => $other->id,
            'email' => 'other@example.com',
        ]);

        $this->actingAs($this->user())
            ->get(route('platform.tenants', ['q' => 'merchant@']))
            ->assertOk()
            ->assertSee('email-match-org')
            ->assertDontSee('email-other-org')
            ->assertSee('value="merchant@"', false);
    }

    public function test_platform_console_remains_available_without_an_active_subscription(): void
    {
        $user = $this->user();

        Subscription::where('tenant_id', $user->tenant_id)->update(['status' => 'canceled']);

        $this->actingAs($user)->get('/platform')->assertOk();
    }

    public function test_platform_admin_can_suspend_a_tenant(): void
    {
        $tenant = $this->tenant('other-merchant');

        $this->actingAs($this->user())
            ->post(route('platform.tenants.status', $tenant), ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertSame('suspended', $tenant->fresh()->status);
    }

    public function test_platform_admin_can_change_a_tenants_plan(): void
    {
        $tenant = $this->tenant('other-merchant');
        $plan = Plan::create([
            'name' => 'Small',
            'slug' => 'small',
            'monthly_price' => 10,
            'yearly_price' => 100,
            'max_products' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($this->user())
            ->post(route('platform.tenants.plan', $tenant), [
                'plan_id' => $plan->id,
                'billing_interval' => 'monthly',
            ])
            ->assertRedirect();

        $subscription = Subscription::withoutGlobalScopes()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('small', $tenant->fresh()->plan);

        $adminTenantId = app(TenantContext::class)->id();
        app(TenantContext::class)->set($tenant);

        $this->assertSame(3, app(PlanLimits::class)->limitFor('products'));

        app(TenantContext::class)->set(Tenant::findOrFail($adminTenantId));
    }

    public function test_platform_admin_can_edit_plan_details(): void
    {
        $plan = Plan::create([
            'name' => 'Growth',
            'slug' => 'growth-edit-test',
            'description' => 'Old description',
            'monthly_price' => 79,
            'yearly_price' => 790,
            'currency' => 'KES',
            'max_users' => 20,
            'max_products' => 5000,
            'max_warehouses' => 10,
            'max_sales_per_month' => 20000,
            'is_active' => true,
        ]);

        $this->actingAs($this->user())
            ->put(route('platform.plans.update', $plan), [
                'name' => 'Growth Plus',
                'description' => 'Updated description',
                'monthly_price' => 99,
                'yearly_price' => 990,
                'currency' => ' kes ',
                'max_users' => 50,
                'max_products' => 10000,
                'max_warehouses' => 20,
                'max_sales_per_month' => 50000,
                'stripe_price_id' => 'PLN_test123',
                'stripe_yearly_price_id' => 'PLN_test456',
                'is_active' => true,
            ])
            ->assertRedirect();

        $plan->refresh();

        $this->assertSame('Growth Plus', $plan->name);
        $this->assertSame('Updated description', $plan->description);
        $this->assertSame('99.00', $plan->monthly_price);
        $this->assertSame('990.00', $plan->yearly_price);
        $this->assertSame('KES', $plan->currency);
        $this->assertSame(50, $plan->max_users);
        $this->assertSame(10000, $plan->max_products);
        $this->assertSame(20, $plan->max_warehouses);
        $this->assertSame(50000, $plan->max_sales_per_month);
        $this->assertSame('PLN_test123', $plan->stripe_price_id);
        $this->assertSame('PLN_test456', $plan->stripe_yearly_price_id);
    }

    public function test_non_platform_admin_cannot_edit_plans(): void
    {
        $plan = Plan::create([
            'name' => 'Growth',
            'slug' => 'growth-edit-test',
            'monthly_price' => 79,
            'yearly_price' => 790,
            'is_active' => true,
        ]);

        $this->actingAs($this->user(false))
            ->put(route('platform.plans.update', $plan), [
                'name' => 'Hacked',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'KES',
            ])
            ->assertForbidden();

        $this->assertSame('Growth', $plan->fresh()->name);
    }

    public function test_platform_admin_can_create_a_plan(): void
    {
        $this->actingAs($this->user())
            ->post(route('platform.plans.store'), [
                'name' => 'Starter',
                'slug' => 'starter-new',
                'description' => 'For small shops',
                'monthly_price' => 29,
                'yearly_price' => 290,
                'currency' => 'kes',
                'max_users' => 5,
                'max_products' => 500,
                'max_warehouses' => 2,
                'max_sales_per_month' => 2000,
            ])
            ->assertRedirect();

        $plan = Plan::where('slug', 'starter-new')->firstOrFail();

        $this->assertSame('Starter', $plan->name);
        $this->assertSame('KES', $plan->currency);
        $this->assertTrue($plan->is_active);
        $this->assertSame(5, $plan->max_users);
    }

    public function test_non_platform_admin_cannot_manage_whatsapp_settings(): void
    {
        $user = $this->user(false);

        $this->actingAs($user)
            ->get(route('platform.settings'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('platform.settings.update'), ['whatsapp_number' => '0712345678'])
            ->assertForbidden();
    }

    public function test_platform_admin_can_update_the_whatsapp_number(): void
    {
        $this->actingAs($this->user())
            ->put(route('platform.settings.update'), [
                'whatsapp_number' => '0712345678',
                'whatsapp_country_code' => '254',
            ])
            ->assertRedirect();

        $this->assertTrue(WhatsApp::configured());
        $this->assertSame('254712345678', WhatsApp::number());
    }

    public function test_saved_whatsapp_number_enables_public_buttons(): void
    {
        $this->actingAs($this->user())
            ->put(route('platform.settings.update'), ['whatsapp_number' => '0712345678'])
            ->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('https://wa.me/254712345678', false)
            ->assertSee('Chat with admin on WhatsApp');
    }

    public function test_platform_admin_can_clear_the_whatsapp_number(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);

        $this->actingAs($this->user())
            ->put(route('platform.settings.update'), ['whatsapp_number' => ''])
            ->assertRedirect();

        $this->assertFalse(WhatsApp::configured());
    }

    public function test_whatsapp_country_code_must_be_numeric(): void
    {
        $this->actingAs($this->user())
            ->put(route('platform.settings.update'), [
                'whatsapp_number' => '0712345678',
                'whatsapp_country_code' => 'abc',
            ])
            ->assertSessionHasErrors('whatsapp_country_code');
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => $slug,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'trialing',
        ]);
    }

    private function user(bool $isPlatformAdmin = true): User
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
            'is_platform_admin' => $isPlatformAdmin,
        ]);
    }
}
