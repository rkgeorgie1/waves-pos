<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_queries_only_return_rows_from_the_current_tenant(): void
    {
        $tenantA = $this->createTenant('tenant-a');
        $tenantB = $this->createTenant('tenant-b');

        app(TenantContext::class)->set($tenantA);
        $productA = Product::create(['name' => 'Merchant A product', 'sku' => 'SHARED-SKU']);

        app(TenantContext::class)->set($tenantB);
        $productB = Product::create(['name' => 'Merchant B product', 'sku' => 'SHARED-SKU']);

        $this->assertSame(1, Product::count());
        $this->assertSame($productB->id, Product::first()->id);
        $this->assertNull(Product::find($productA->id));
    }

    public function test_no_tenant_context_fails_closed(): void
    {
        $tenant = $this->createTenant('tenant-a');

        app(TenantContext::class)->set($tenant);
        Product::create(['name' => 'Hidden product', 'sku' => 'HIDDEN']);

        app(TenantContext::class)->forget();

        $this->assertSame(0, Product::count());
    }

    public function test_suspended_tenant_cannot_access_authenticated_pages(): void
    {
        $tenant = $this->createTenant('suspended');
        $tenant->status = 'suspended';
        $tenant->save();

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->get('/verify-email')->assertForbidden();
    }

    public function test_registration_provisions_a_separate_tenant(): void
    {
        $defaultTenantId = Tenant::where('slug', 'default')->value('id');

        $this->post('/register', [
            'name' => 'New Merchant',
            'email' => 'merchant@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'merchant@example.com')->firstOrFail();

        $this->assertNotSame($defaultTenantId, $user->tenant_id);
        $this->assertDatabaseHas('roles', [
            'tenant_id' => $user->tenant_id,
            'slug' => 'super-admin',
        ]);
        $this->assertDatabaseHas('settings', [
            'tenant_id' => $user->tenant_id,
            'key' => 'currency',
        ]);
        $this->assertDatabaseHas('warehouses', [
            'tenant_id' => $user->tenant_id,
            'code' => 'MAIN',
        ]);
    }

    private function createTenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => $slug,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }
}
