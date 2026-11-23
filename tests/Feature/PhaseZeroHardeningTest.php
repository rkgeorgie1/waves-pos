<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseZeroHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_open_settings(): void
    {
        $role = Role::create([
            'name' => 'Cashier',
            'slug' => 'cashier',
            'is_system' => true,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    public function test_super_admin_bypasses_permission_checks(): void
    {
        $user = $this->createSuperAdmin();

        $this->assertTrue($user->hasPermission('settings.manage'));

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk();
    }

    public function test_permission_seeder_grants_role_permissions(): void
    {
        Role::create([
            'name' => 'Cashier',
            'slug' => 'cashier',
            'is_system' => true,
        ]);
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create([
            'role_id' => Role::where('slug', 'cashier')->firstOrFail()->id,
        ]);

        $this->assertTrue($user->hasPermission('pos.access'));
        $this->assertFalse($user->hasPermission('settings.manage'));
    }

    public function test_inactive_user_cannot_login_and_attempt_is_audited(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login_blocked',
            'user_id' => $user->id,
        ]);
    }

    public function test_sale_refuses_to_oversell_and_rolls_back(): void
    {
        $user = $this->createSuperAdmin();
        [$warehouse, $product] = $this->createWarehouseAndProduct();

        $response = $this->actingAs($user)->postJson(route('pos.store'), [
            'warehouse_id' => $warehouse->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('stock_balances', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_sale_with_available_stock_is_audited_and_deducts_stock(): void
    {
        $user = $this->createSuperAdmin();
        [$warehouse, $product] = $this->createWarehouseAndProduct();
        StockBalance::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'avg_cost' => 50,
        ]);

        $response = $this->actingAs($user)->postJson(route('pos.store'), [
            'warehouse_id' => $warehouse->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 8,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sale.created',
            'user_id' => $user->id,
        ]);
    }

    public function test_stock_adjustment_is_persisted_linked_and_audited(): void
    {
        $user = $this->createSuperAdmin();
        [$warehouse, $product] = $this->createWarehouseAndProduct();

        $this->actingAs($user)->post(route('inventory.adjust'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'type' => 'add',
            'reason' => 'Opening stock',
        ])->assertRedirect();

        $auditLog = AuditLog::where('action', 'stock.adjusted')->firstOrFail();

        $this->assertDatabaseHas('stock_adjustments', [
            'warehouse_id' => $warehouse->id,
            'reason' => 'Opening stock',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'reference_type' => 'stock_adjustment',
            'reference_id' => $auditLog->model_id,
            'quantity' => 5,
        ]);
        $this->assertSame($user->id, $auditLog->user_id);
    }

    public function test_role_permission_changes_are_audited(): void
    {
        $user = $this->createSuperAdmin();
        $role = Role::create([
            'name' => 'Custom Role',
            'slug' => 'custom-role',
            'is_system' => false,
        ]);
        $permission = Permission::create([
            'name' => 'Custom Permission',
            'slug' => 'custom-permission',
            'module' => 'General',
        ]);

        $this->actingAs($user)
            ->post(route('settings.roles.permissions', $role), ['permissions' => [$permission->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'role.permissions_synced',
            'model_id' => $role->id,
            'user_id' => $user->id,
        ]);
        $this->assertTrue($role->permissions()->whereKey($permission->id)->exists());
    }

    private function createSuperAdmin(): User
    {
        $role = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'is_system' => true,
        ]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * @return array{Warehouse, Product}
     */
    private function createWarehouseAndProduct(): array
    {
        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN'.random_int(100, 999),
            'is_default' => true,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-'.random_int(10000, 99999),
            'purchase_price' => 50,
            'retail_price' => 100,
            'is_active' => true,
        ]);

        return [$warehouse, $product];
    }
}
