<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_can_save_multiple_items_with_sparse_indices(): void
    {
        $role = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'is_system' => true,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_active' => true,
        ]);
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $firstProduct = Product::create([
            'name' => 'First Product',
            'sku' => 'FIRST-1',
            'purchase_price' => 100,
            'retail_price' => 150,
        ]);
        $secondProduct = Product::create([
            'name' => 'Second Product',
            'sku' => 'SECOND-1',
            'purchase_price' => 50,
            'retail_price' => 80,
        ]);

        $response = $this->actingAs($user)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'received',
            'items' => [
                0 => ['product_id' => $firstProduct->id, 'quantity' => 2, 'unit_cost' => 100],
                2 => ['product_id' => $secondProduct->id, 'quantity' => 3, 'unit_cost' => 50],
            ],
        ]);

        $purchase = Purchase::firstOrFail();
        $response->assertRedirect(route('purchases.show', $purchase));

        $this->assertCount(2, $purchase->items);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $secondProduct->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $firstProduct->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $secondProduct->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 3,
        ]);
        $this->assertEquals(350, (float) $purchase->total);
    }

    public function test_purchases_list_links_to_edit_action(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $purchase = $this->purchaseRecord($user, $warehouse);

        $response = $this->actingAs($user)->get(route('purchases.index'));

        $response->assertOk();
        $response->assertSee(route('purchases.edit', $purchase));
    }

    public function test_index_searches_purchases_by_reference_supplier_and_location(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $alphaSupplier = $this->supplier('Alpha Supplier');
        $betaSupplier = $this->supplier('Beta Supplier');
        $matchingPurchase = $this->purchaseRecord($user, $warehouse, $alphaSupplier, 'PO-1001');
        $otherPurchase = $this->purchaseRecord($user, $warehouse, $betaSupplier, 'PO-2002');

        $response = $this->actingAs($user)->get(route('purchases.index', ['q' => 'alpha']));

        $response->assertOk();
        $response->assertSee($matchingPurchase->reference);
        $response->assertDontSee($otherPurchase->reference);
    }

    public function test_index_filters_purchases_by_reference_search_term(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $matchingPurchase = $this->purchaseRecord($user, $warehouse, null, 'PO-1001');
        $otherPurchase = $this->purchaseRecord($user, $warehouse, null, 'PO-2002');

        $response = $this->actingAs($user)->get(route('purchases.index', ['q' => '1001']));

        $response->assertOk();
        $response->assertSee($matchingPurchase->reference);
        $response->assertDontSee($otherPurchase->reference);
    }

    public function test_index_filters_purchases_by_location_search_term(): void
    {
        $user = $this->superAdmin();
        $mainWarehouse = $this->warehouse();
        $secondaryWarehouse = Warehouse::create([
            'name' => 'Secondary Store',
            'code' => 'SECONDARY',
            'is_active' => true,
        ]);
        $matchingPurchase = $this->purchaseRecord($user, $secondaryWarehouse, null, 'PO-1001');
        $otherPurchase = $this->purchaseRecord($user, $mainWarehouse, null, 'PO-2002');

        $response = $this->actingAs($user)->get(route('purchases.index', ['q' => 'secondary']));

        $response->assertOk();
        $response->assertSee($matchingPurchase->reference);
        $response->assertDontSee($otherPurchase->reference);
    }

    public function test_edit_form_renders_purchase_items_for_editing(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $supplier = $this->supplier();
        $product = $this->product('First Product', 'FIRST-1', 100, 150);
        $purchase = $this->receivedPurchase($user, $supplier, $warehouse, $product);

        $response = $this->actingAs($user)->get(route('purchases.edit', $purchase));

        $response->assertOk();
        $response->assertSee('Edit Purchase '.$purchase->reference);
        $response->assertSee('items[0][product_id]', false);
        $response->assertSee('Update Purchase');
    }

    public function test_update_changes_items_and_adjusts_stock_supplier_and_payment(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $supplier = $this->supplier('Supplier A', 0);
        $firstProduct = $this->product('First Product', 'FIRST-1', 100, 150);
        $secondProduct = $this->product('Second Product', 'SECOND-1', 50, 80);
        $purchase = $this->receivedPurchase(
            $user,
            $supplier,
            $warehouse,
            $firstProduct,
            $secondProduct,
        );

        $response = $this->actingAs($user)->put(route('purchases.update', $purchase), [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'received',
            'paid' => 50,
            'payment_method' => 'mpesa',
            'items' => [
                0 => ['product_id' => $firstProduct->id, 'quantity' => 1, 'unit_cost' => 100, 'selling_price' => 150],
                2 => ['product_id' => $secondProduct->id, 'quantity' => 6, 'unit_cost' => 50, 'selling_price' => 80],
            ],
        ]);

        $purchase->refresh();
        $response->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame(400.0, (float) $purchase->total);
        $this->assertSame(50.0, (float) $purchase->paid);
        $this->assertSame(350.0, (float) $purchase->balance);
        $this->assertSame([1.0, 6.0], $purchase->items->pluck('quantity')->map(fn ($quantity) => (float) $quantity)->all());

        $this->assertSame(1.0, (float) StockBalance::where('product_id', $firstProduct->id)->where('warehouse_id', $warehouse->id)->value('quantity'));
        $this->assertSame(6.0, (float) StockBalance::where('product_id', $secondProduct->id)->where('warehouse_id', $warehouse->id)->value('quantity'));
        $this->assertSame(350.0, (float) $supplier->refresh()->balance);
        $this->assertSame(50.0, (float) Payment::where('payable_type', Purchase::class)->where('payable_id', $purchase->id)->sum('amount'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'purchase.updated',
            'model' => $purchase->getMorphClass(),
            'model_id' => $purchase->id,
        ]);
    }

    public function test_update_rejects_edit_when_received_stock_has_been_sold(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $supplier = $this->supplier();
        $product = $this->product('First Product', 'FIRST-1', 100, 150);
        $purchase = $this->receivedPurchase($user, $supplier, $warehouse, $product);
        StockBalance::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->update(['quantity' => 0]);

        $response = $this->actingAs($user)
            ->from(route('purchases.edit', $purchase))
            ->put(route('purchases.update', $purchase), [
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'status' => 'received',
                'items' => [
                    0 => ['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 100],
                ],
            ]);

        $response->assertRedirect(route('purchases.edit', $purchase));
        $response->assertSessionHasErrors('items');

        $this->assertSame(2.0, (float) $purchase->items()->first()->quantity);
        $this->assertSame(0.0, (float) StockBalance::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->value('quantity'));
        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'purchase.updated',
            'model' => $purchase->getMorphClass(),
            'model_id' => $purchase->id,
        ]);
    }

    public function test_update_rejects_duplicate_reference(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $product = $this->product('First Product', 'FIRST-1', 100, 150);
        $firstPurchase = $this->purchaseRecord($user, $warehouse, null, 'PO-A');
        $secondPurchase = $this->purchaseRecord($user, $warehouse, null, 'PO-B');

        $response = $this->actingAs($user)
            ->from(route('purchases.edit', $firstPurchase))
            ->put(route('purchases.update', $firstPurchase), [
                'reference' => 'PO-B',
                'warehouse_id' => $warehouse->id,
                'status' => 'ordered',
                'items' => [
                    0 => ['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 100],
                ],
            ]);

        $response->assertSessionHasErrors('reference');
        $this->assertSame('PO-A', $firstPurchase->refresh()->reference);
        $this->assertSame('PO-B', $secondPurchase->refresh()->reference);
    }

    public function test_guest_is_redirected_to_login_when_editing_purchase(): void
    {
        $user = $this->superAdmin();
        $warehouse = $this->warehouse();
        $purchase = $this->purchaseRecord($user, $warehouse);

        $response = $this->get(route('purchases.edit', $purchase));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_purchase_permission_cannot_edit_purchase(): void
    {
        $role = Role::create([
            'name' => 'Auditor',
            'slug' => 'auditor',
            'is_system' => false,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $warehouse = $this->warehouse();
        $purchase = $this->purchaseRecord($user, $warehouse);

        $response = $this->actingAs($user)->get(route('purchases.edit', $purchase));

        $response->assertForbidden();
    }

    private function superAdmin(): User
    {
        $role = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'is_system' => true,
        ]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_active' => true,
        ]);
    }

    private function supplier(string $name = 'Test Supplier', float $balance = 0): Supplier
    {
        return Supplier::create([
            'name' => $name,
            'balance' => $balance,
        ]);
    }

    private function product(string $name, string $sku, float $purchasePrice, float $retailPrice): Product
    {
        return Product::create([
            'name' => $name,
            'sku' => $sku,
            'purchase_price' => $purchasePrice,
            'retail_price' => $retailPrice,
        ]);
    }

    private function purchaseRecord(User $user, Warehouse $warehouse, ?Supplier $supplier = null, ?string $reference = null): Purchase
    {
        return Purchase::create([
            'reference' => $reference ?? ('PO-'.fake()->unique()->numberBetween(1000, 999999)),
            'supplier_id' => $supplier?->id,
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'ordered',
            'subtotal' => 0,
            'total' => 0,
            'paid' => 0,
            'balance' => 0,
            'ordered_at' => now(),
        ]);
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function receivedPurchase(
        User $user,
        Supplier $supplier,
        Warehouse $warehouse,
        Product ...$products,
    ): Purchase {
        $items = [];

        foreach ($products as $index => $product) {
            $quantity = $index === 0 ? 2 : 3;
            $items[$index] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => (float) $product->purchase_price,
            ];
        }

        $this->actingAs($user)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'received',
            'paid' => 100,
            'payment_method' => 'cash',
            'items' => $items,
        ]);

        return Purchase::firstOrFail();
    }
}
