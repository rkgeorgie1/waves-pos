<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PlanLimits;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\TenantContext;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $products = Product::with(['category', 'brand', 'unit', 'stockBalances'])
            ->when($q, fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%")->orWhere('barcode', 'like', "%$q%"))
            ->when($request->filled('category_id'), fn ($x) => $x->where('category_id', $request->category_id))
            ->when($request->filled('brand_id'), fn ($x) => $x->where('brand_id', $request->brand_id))
            ->when($request->filled('unit_id'), fn ($x) => $x->where('unit_id', $request->unit_id))
            ->latest()->paginate(20)->withQueryString();

        return view('products.index', compact('products', 'q'));
    }

    public function create()
    {
        $nextSku = $this->generateUniqueSku();

        return view('products.create', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'units' => Unit::all(),
            'suppliers' => Supplier::all(),
            'warehouses' => Warehouse::all(),
            'nextSku' => $nextSku,
        ]);
    }

    public function store(Request $request, PlanLimits $limits)
    {
        if (! $limits->allows('products')) {
            return back()->withErrors([
                'plan_limit' => 'Your plan allows '.$limits->limitFor('products').' products. Upgrade your plan to add more.',
            ])->withInput();
        }

        $tenantId = app(TenantContext::class)->id();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => ['nullable', 'string', Rule::unique('products', 'sku')->where('tenant_id', $tenantId)],
            'barcode' => ['nullable', 'string', Rule::unique('products', 'barcode')->where('tenant_id', $tenantId)],
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'vat_category' => 'nullable|string|in:inclusive,exclusive,no_tax',
            'vat_rate' => 'numeric|min:0',
            'min_stock_level' => 'integer|min:0',
            'default_supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_price' => 'numeric|min:0',
            'retail_price' => 'numeric|min:0',
            'wholesale_price' => 'numeric|min:0',
            'contractor_price' => 'numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if (empty($data['sku'])) {
            $data['sku'] = $this->generateUniqueSku();
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $initialQty = (float) $data['quantity'];
        $targetWarehouseId = $data['warehouse_id'] ?? Warehouse::where('is_default', true)->value('id') ?? Warehouse::query()->value('id');
        unset($data['quantity'], $data['warehouse_id']);

        $product = Product::create($data);

        foreach (Warehouse::all() as $w) {
            $qty = ((int) $w->id === (int) $targetWarehouseId) ? $initialQty : 0;
            StockBalance::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $w->id],
                ['quantity' => $qty, 'avg_cost' => $product->purchase_price ?? 0]
            );
        }

        if ($initialQty > 0 && $targetWarehouseId) {
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $targetWarehouseId,
                'type' => 'in',
                'reference_type' => 'opening_stock',
                'reference_id' => $product->id,
                'quantity' => $initialQty,
                'unit_cost' => $product->purchase_price ?? 0,
                'balance_after' => $initialQty,
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'unit', 'stockBalances.warehouse']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('stockBalances');

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'units' => Unit::all(),
            'suppliers' => Supplier::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|unique:products,barcode,'.$product->id,
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'vat_category' => 'nullable|string|in:inclusive,exclusive,no_tax',
            'vat_rate' => 'numeric|min:0',
            'min_stock_level' => 'integer|min:0',
            'default_supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_price' => 'numeric|min:0',
            'retail_price' => 'numeric|min:0',
            'wholesale_price' => 'numeric|min:0',
            'contractor_price' => 'numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $newQty = $data['quantity'] ?? null;
        $targetWarehouseId = $data['warehouse_id'] ?? Warehouse::where('is_default', true)->value('id') ?? Warehouse::query()->value('id');
        unset($data['quantity'], $data['warehouse_id']);

        $product->update($data);

        if ($newQty !== null && $targetWarehouseId) {
            StockBalance::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $targetWarehouseId],
                ['quantity' => (float) $newQty]
            );
        }

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $products = Product::with('stockBalances')
            ->where('is_active', true)
            ->where(function ($x) use ($q) {
                $x->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('barcode', $q);
            })
            ->limit(20)->get()
            ->map(function ($p) {
                $stock = $p->stockBalances->sum('quantity');

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode,
                    'retail_price' => (float) $p->retail_price,
                    'wholesale_price' => (float) $p->wholesale_price,
                    'stock' => (float) $stock,
                ];
            });

        return response()->json($products);
    }

    public function stock(Product $product)
    {
        return response()->json([
            'stock' => (float) StockBalance::where('product_id', $product->id)->sum('quantity'),
        ]);
    }

    private function generateUniqueSku(): string
    {
        $num = ((int) Product::max('id')) + 1;
        do {
            $sku = 'SKU-'.str_pad((string) $num, 4, '0', STR_PAD_LEFT);
            $num++;
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }
}
