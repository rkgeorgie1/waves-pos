<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanLimits;
use App\Models\Product;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantContext;
use App\Models\User;
use App\Models\Warehouse;
use App\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_activity_reflects_trial_and_period_dates(): void
    {
        $tenant = app(TenantContext::class)->current();
        $plan = Plan::where('slug', 'free')->firstOrFail();

        $trialing = $this->subscriptionFor($tenant, $plan, [
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(3),
        ]);
        $this->assertTrue($trialing->isCurrentlyActive());

        $expiredTrial = $this->subscriptionFor($tenant, $plan, [
            'status' => 'trialing',
            'trial_ends_at' => now()->subDay(),
        ]);
        $this->assertFalse($expiredTrial->isCurrentlyActive());

        $active = $this->subscriptionFor($tenant, $plan, [
            'status' => 'active',
            'current_period_end' => now()->addDays(10),
        ]);
        $this->assertTrue($active->isCurrentlyActive());

        $pastDue = $this->subscriptionFor($tenant, $plan, ['status' => 'past_due']);
        $this->assertTrue($pastDue->isCurrentlyActive());

        $canceled = $this->subscriptionFor($tenant, $plan, ['status' => 'canceled']);
        $this->assertFalse($canceled->isCurrentlyActive());
    }

    public function test_canceled_subscription_blocks_authenticated_access(): void
    {
        $user = $this->adminUser();

        Subscription::where('tenant_id', $user->tenant_id)->update(['status' => 'canceled']);

        $this->actingAs($user)->get('/dashboard')->assertStatus(402);
    }

    public function test_active_subscription_allows_authenticated_access(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_product_creation_is_blocked_when_plan_limit_is_reached(): void
    {
        $user = $this->adminUser();
        $this->usePlan(['slug' => 'limited-products', 'max_products' => 1]);

        Product::create(['name' => 'Existing product', 'sku' => 'EXISTING']);

        $this->assertFalse(app(PlanLimits::class)->allows('products'));

        $this->actingAs($user)
            ->post('/products', $this->productPayload())
            ->assertSessionHasErrors('plan_limit');

        $this->assertSame(1, Product::count());
    }

    public function test_sku_is_unique_per_tenant_not_globally(): void
    {
        $tenantA = app(TenantContext::class)->current();
        $this->usePlan(['slug' => 'unlimited-products']);

        Product::create(['name' => 'First tenant product', 'sku' => 'SHARED-SKU']);

        $tenantB = Tenant::create([
            'name' => 'Second merchant',
            'slug' => 'second-merchant',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        app(TenantContext::class)->set($tenantB);
        Product::create(['name' => 'Second tenant product', 'sku' => 'SHARED-SKU']);

        $this->assertSame(1, Product::where('tenant_id', $tenantB->id)->count());

        app(TenantContext::class)->set($tenantA);
        $this->assertSame(1, Product::where('tenant_id', $tenantA->id)->count());
    }

    public function test_warehouse_creation_is_blocked_when_plan_limit_is_reached(): void
    {
        $user = $this->adminUser();
        $this->usePlan(['slug' => 'one-warehouse', 'max_warehouses' => 1]);

        Warehouse::create(['name' => 'Only warehouse', 'code' => 'ONLY']);

        $this->assertFalse(app(PlanLimits::class)->allows('warehouses'));

        $this->actingAs($user)
            ->post('/settings/warehouses', ['name' => 'Second warehouse', 'code' => 'SECOND'])
            ->assertSessionHasErrors('plan_limit');

        $this->assertSame(1, Warehouse::count());
    }

    public function test_stripe_webhook_syncs_the_plan_and_subscription_status(): void
    {
        $tenant = app(TenantContext::class)->current();
        $tenant->forceFill(['stripe_id' => 'cus_test123'])->save();

        $plan = Plan::create([
            'name' => 'Paid',
            'slug' => 'paid',
            'monthly_price' => 20,
            'yearly_price' => 200,
            'stripe_price_id' => 'price_monthly',
            'is_active' => true,
        ]);

        $this->postJson('/stripe/webhook', [
            'id' => 'evt_test',
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_test123',
                    'status' => 'active',
                    'cancel_at_period_end' => false,
                    'current_period_start' => now()->addDay()->timestamp,
                    'current_period_end' => now()->addMonth()->timestamp,
                    'items' => [
                        'data' => [[
                            'id' => 'si_test123',
                            'quantity' => 1,
                            'price' => ['id' => 'price_monthly', 'product' => 'prod_test123'],
                        ]],
                    ],
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'stripe_id' => 'sub_test123',
            'stripe_status' => 'active',
            'status' => 'active',
            'plan_id' => $plan->id,
        ]);

        $this->assertSame('paid', $tenant->fresh()->plan);
    }

    public function test_billing_page_shows_the_current_plan(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/billing')
            ->assertOk()
            ->assertSee('Free')
            ->assertSee('Usage this plan');
    }

    public function test_plan_cards_offer_whatsapp_payment_links(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => '0712345678']);

        $user = $this->adminUser();
        $starter = Plan::where('slug', 'starter')->firstOrFail();
        $growth = Plan::where('slug', 'growth')->firstOrFail();

        $response = $this->actingAs($user)->get('/billing');

        $response->assertOk()
            ->assertSee(route('billing.pay', ['plan' => $starter, 'interval' => 'monthly']), false)
            ->assertSee(route('billing.pay', ['plan' => $growth, 'interval' => 'monthly']), false)
            ->assertSee('Chat about Starter')
            ->assertSee('Chat about Growth')
            ->assertSee('Chat with the admin to arrange payment');
    }

    public function test_plan_cards_hide_whatsapp_links_when_the_number_is_not_configured(): void
    {
        WhatsApp::saveSettings(['whatsapp_number' => null]);

        $response = $this->actingAs($this->adminUser())->get('/billing');

        $response->assertOk()
            ->assertSee('WhatsApp payments are not configured yet.')
            ->assertDontSee('/billing/pay/');
    }

    public function test_seeded_plans_use_kes_currency(): void
    {
        $this->assertSame(['KES'], Plan::pluck('currency')->unique()->values()->all());
    }

    public function test_subscription_can_be_cancelled_without_a_payment_gateway(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('billing.cancel'))
            ->assertRedirect();

        $this->assertSame('cancelled', Subscription::where('tenant_id', $user->tenant_id)->firstOrFail()->status);
        $this->assertSame('cancelled', Tenant::findOrFail($user->tenant_id)->subscription_status);
    }

    public function test_invoices_page_explains_that_payment_records_are_shared_on_whatsapp(): void
    {
        $this->actingAs($this->adminUser())
            ->get(route('billing.invoices'))
            ->assertOk()
            ->assertSee('Payment records are shared by the admin on WhatsApp.');
    }

    public function test_billing_page_is_accessible_when_subscription_has_lapsed(): void
    {
        $user = $this->adminUser();

        Subscription::where('tenant_id', $user->tenant_id)->update(['status' => 'canceled']);

        $this->actingAs($user)
            ->get('/billing')
            ->assertOk()
            ->assertSee('Inactive');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function subscriptionFor(Tenant $tenant, Plan $plan, array $attributes): Subscription
    {
        return Subscription::create(array_merge([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_interval' => 'monthly',
            'current_period_start' => now(),
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function usePlan(array $attributes): Plan
    {
        $plan = Plan::create(array_merge([
            'name' => 'Test plan',
            'monthly_price' => 0,
            'yearly_price' => 0,
            'is_active' => true,
        ], $attributes));

        Subscription::where('tenant_id', app(TenantContext::class)->id())->update([
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addDays(10),
        ]);

        return $plan;
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

    /**
     * @return array<string, mixed>
     */
    private function productPayload(): array
    {
        return [
            'name' => 'New product',
            'sku' => 'NEW-SKU',
            'quantity' => 0,
            'vat_rate' => 0,
            'min_stock_level' => 0,
            'purchase_price' => 0,
            'retail_price' => 0,
            'wholesale_price' => 0,
            'contractor_price' => 0,
        ];
    }
}
