<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = Purchase::with(['supplier', 'warehouse'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';

                $query->where(function ($query) use ($term) {
                    $query->where('reference', 'like', $term)
                        ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', $term))
                        ->orWhereHas('warehouse', fn ($q) => $q->where('name', 'like', $term));
                });
            })
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('ordered_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('ordered_at', '<=', $request->end_date))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('payment_status'), function ($q) use ($request) {
                return match ($request->payment_status) {
                    'paid' => $q->whereColumn('paid', '>=', 'total'),
                    'partial' => $q->where('paid', '>', 0)->whereColumn('paid', '<', 'total'),
                    'due' => $q->where('paid', 0),
                    default => $q,
                };
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('ordered_at')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->purchaseRules());
        $purchase = null;

        DB::transaction(function () use ($data, &$purchase) {
            $totals = $this->calculateTotals($data);
            $status = $data['status'] ?? 'received';
            $paid = (float) ($data['paid'] ?? 0);

            $purchase = Purchase::create([
                'reference' => ! empty($data['reference']) ? $data['reference'] : ('PO-'.now()->format('YmdHis').'-'.random_int(100, 999)),
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => $status,
                'subtotal' => $totals['subtotal'],
                'discount_type' => $data['discount_type'] ?? 'none',
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax' => $totals['tax'],
                'shipping_charges' => $totals['shipping'],
                'total' => $totals['total'],
                'paid' => $paid,
                'balance' => max(0, $totals['total'] - $paid),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
                'ordered_at' => ! empty($data['ordered_at']) ? $data['ordered_at'] : now(),
            ]);

            foreach ($totals['items'] as $item) {
                $product = $this->applyItemPricing($item);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'received_quantity' => $status === 'received' ? $item['quantity'] : 0,
                    'unit_cost' => $item['unit_cost'],
                    'total' => $item['line_total'],
                ]);

                $this->applyReceivedStock($purchase, $product, $item['quantity'], $item['unit_cost']);
            }

            if ($paid > 0) {
                $this->createPayment($purchase, $paid, $data['payment_method'] ?? 'cash', 'Purchase payment');
            }

            if (! empty($data['supplier_id']) && $status === 'received') {
                $supplier = Supplier::whereKey($data['supplier_id'])->lockForUpdate()->firstOrFail();
                $supplier->balance += max(0, $totals['total'] - $paid);
                $supplier->save();
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase saved successfully.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load('items', 'supplier', 'warehouse');

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $purchase->load('items.product', 'payments');

        $productIds = $purchase->items->pluck('product_id');
        $products = Product::query()
            ->where(function ($query) use ($productIds) {
                $query->where('is_active', true)->orWhereIn('id', $productIds);
            })
            ->orderBy('name')
            ->get();

        return view('purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => $products,
            'warehouses' => Warehouse::all(),
            'paymentMethod' => $purchase->payments->first()?->method ?? 'cash',
        ]);
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validate($this->purchaseRules($purchase->id));
        $purchase->load('items');

        $oldValues = [
            'reference' => $purchase->reference,
            'supplier_id' => $purchase->supplier_id,
            'warehouse_id' => $purchase->warehouse_id,
            'status' => $purchase->status,
            'ordered_at' => $purchase->ordered_at,
            'subtotal' => $purchase->subtotal,
            'tax' => $purchase->tax,
            'total' => $purchase->total,
            'paid' => $purchase->paid,
            'notes' => $purchase->notes,
            'items' => $purchase->items->map(fn (PurchaseItem $item): array => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
            ])->all(),
        ];

        $oldSupplierId = $purchase->supplier_id;
        $oldStatus = $purchase->status;
        $oldTotal = (float) $purchase->total;
        $oldPaid = (float) $purchase->paid;

        DB::transaction(function () use ($data, $purchase, $oldValues, $oldSupplierId, $oldStatus, $oldTotal, $oldPaid) {
            $totals = $this->calculateTotals($data);
            $status = $data['status'] ?? 'received';
            $paid = (float) ($data['paid'] ?? 0);

            $this->reverseReceivedStock($purchase);

            $purchase->update([
                'reference' => ($data['reference'] ?? null) ?: $purchase->reference,
                'supplier_id' => $data['supplier_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'status' => $status,
                'subtotal' => $totals['subtotal'],
                'discount_type' => $data['discount_type'] ?? 'none',
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax' => $totals['tax'],
                'shipping_charges' => $totals['shipping'],
                'total' => $totals['total'],
                'paid' => $paid,
                'balance' => max(0, $totals['total'] - $paid),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
                'ordered_at' => ! empty($data['ordered_at']) ? $data['ordered_at'] : $purchase->ordered_at,
            ]);

            $purchase->items()->delete();

            foreach ($totals['items'] as $item) {
                $product = $this->applyItemPricing($item);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'received_quantity' => $status === 'received' ? $item['quantity'] : 0,
                    'unit_cost' => $item['unit_cost'],
                    'total' => $item['line_total'],
                ]);

                $this->applyReceivedStock($purchase, $product, $item['quantity'], $item['unit_cost']);
            }

            $this->adjustSupplierBalance(
                $oldSupplierId,
                $oldStatus,
                $oldTotal,
                $oldPaid,
                $purchase->supplier_id,
                $status,
                $totals['total'],
                $paid,
            );

            $this->syncPaymentAdjustment($purchase, $paid, $data['payment_method'] ?? 'cash');

            AuditLog::record('purchase.updated', $purchase, [
                'reference' => $purchase->reference,
                'supplier_id' => $purchase->supplier_id,
                'warehouse_id' => $purchase->warehouse_id,
                'status' => $purchase->status,
                'total' => $purchase->total,
                'paid' => $purchase->paid,
                'items' => array_map(fn (array $item): array => [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ], $totals['items']),
            ], $oldValues);
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase updated successfully.');
    }

    public function receive(Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($purchase) {
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                $remaining = $item->quantity - $item->received_quantity;
                if ($remaining <= 0) {
                    continue;
                }

                $bal = StockBalance::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $purchase->warehouse_id],
                    ['quantity' => 0, 'avg_cost' => 0],
                );
                $bal->quantity += $remaining;
                $bal->avg_cost = $item->unit_cost;
                $bal->save();

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                    'type' => 'in',
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'quantity' => $remaining,
                    'unit_cost' => $item->unit_cost,
                    'balance_after' => $bal->quantity,
                    'user_id' => auth()->id(),
                ]);

                $item->received_quantity = $item->quantity;
                $item->save();
            }

            $purchase->status = 'received';
            $purchase->save();

            if ($purchase->supplier_id) {
                $sup = Supplier::find($purchase->supplier_id);
                $sup->balance += max(0, $purchase->total - $purchase->paid);
                $sup->save();
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Goods received.');
    }

    /**
     * @return array<string, string>
     */
    private function purchaseRules(?int $purchaseId = null): array
    {
        $referenceRule = 'nullable|string|max:100|unique:purchases,reference';

        if ($purchaseId !== null) {
            $referenceRule .= ','.$purchaseId;
        }

        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference' => $referenceRule,
            'ordered_at' => 'nullable|date',
            'status' => 'nullable|string|in:received,pending,ordered',
            'warehouse_id' => 'required|exists:warehouses,id',
            'discount_type' => 'nullable|string|in:none,fixed,percentage',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_charges' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.selling_price' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{subtotal: float, discount: float, tax: float, shipping: float, total: float, items: array<int, array<string, mixed>>}
     */
    private function calculateTotals(array $data): array
    {
        $items = [];
        $subtotal = 0.0;

        foreach (array_values($data['items']) as $item) {
            $quantity = (float) $item['quantity'];
            $discountPercent = (float) ($item['discount_percent'] ?? 0);
            $unitCost = (float) $item['unit_cost'] * (1 - ($discountPercent / 100));
            $lineTotal = $quantity * $unitCost;
            $subtotal += $lineTotal;

            $items[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $lineTotal,
                'discount_percent' => $discountPercent,
                'selling_price' => isset($item['selling_price']) ? (float) $item['selling_price'] : null,
            ];
        }

        $discountType = $data['discount_type'] ?? 'none';
        $discountInput = (float) ($data['discount_amount'] ?? 0);
        $discount = $discountType === 'percentage'
            ? ($subtotal * ($discountInput / 100))
            : ($discountType === 'fixed' ? $discountInput : 0.0);
        $tax = (float) ($data['tax'] ?? 0);
        $shipping = (float) ($data['shipping_charges'] ?? 0);
        $total = max(0, $subtotal - $discount + $tax + $shipping);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function applyItemPricing(array $item): Product
    {
        $product = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();
        $product->purchase_price = $item['unit_cost'];

        if ($item['selling_price'] !== null && $item['selling_price'] > 0) {
            $product->retail_price = $item['selling_price'];
        }

        $product->save();

        return $product;
    }

    private function applyReceivedStock(Purchase $purchase, Product $product, float $quantity, float $unitCost): void
    {
        if ($purchase->status !== 'received' || ! $purchase->warehouse_id) {
            return;
        }

        $balance = StockBalance::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $purchase->warehouse_id],
            ['quantity' => 0, 'avg_cost' => 0],
        );
        $balance->quantity += $quantity;
        $balance->avg_cost = $unitCost;
        $balance->save();

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $purchase->warehouse_id,
            'type' => 'in',
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'balance_after' => $balance->quantity,
            'user_id' => auth()->id(),
        ]);
    }

    private function reverseReceivedStock(Purchase $purchase): void
    {
        if ($purchase->status !== 'received' || ! $purchase->warehouse_id) {
            return;
        }

        foreach ($purchase->items as $item) {
            $quantity = (float) $item->received_quantity;

            if ($quantity <= 0) {
                continue;
            }

            $balance = StockBalance::where('product_id', $item->product_id)
                ->where('warehouse_id', $purchase->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (! $balance || (float) $balance->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'items' => 'Stock for '.$item->product_name.' has already been used, so this purchase can no longer be edited.',
                ]);
            }

            $balance->quantity -= $quantity;
            $balance->save();

            StockMovement::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $purchase->warehouse_id,
                'type' => 'adjustment',
                'reference_type' => 'purchase.edit',
                'reference_id' => $purchase->id,
                'quantity' => -$quantity,
                'unit_cost' => $item->unit_cost,
                'balance_after' => $balance->quantity,
                'notes' => 'Reversed by purchase edit',
                'user_id' => auth()->id(),
            ]);
        }
    }

    private function supplierBalanceContribution(?int $supplierId, string $status, float $total, float $paid): float
    {
        if (! $supplierId || $status !== 'received') {
            return 0.0;
        }

        return max(0, $total - $paid);
    }

    private function adjustSupplierBalance(
        ?int $oldSupplierId,
        string $oldStatus,
        float $oldTotal,
        float $oldPaid,
        ?int $newSupplierId,
        string $newStatus,
        float $newTotal,
        float $newPaid,
    ): void {
        $adjustments = [];

        if ($oldSupplierId) {
            $adjustments[$oldSupplierId] = -$this->supplierBalanceContribution($oldSupplierId, $oldStatus, $oldTotal, $oldPaid);
        }

        if ($newSupplierId) {
            $adjustments[$newSupplierId] = ($adjustments[$newSupplierId] ?? 0)
                + $this->supplierBalanceContribution($newSupplierId, $newStatus, $newTotal, $newPaid);
        }

        foreach ($adjustments as $supplierId => $delta) {
            if (abs($delta) < 0.005) {
                continue;
            }

            $supplier = Supplier::whereKey($supplierId)->lockForUpdate()->firstOrFail();
            $supplier->balance += $delta;
            $supplier->save();
        }
    }

    private function createPayment(Purchase $purchase, float $amount, string $method, string $notes): void
    {
        Payment::create([
            'reference' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'payable_type' => Purchase::class,
            'payable_id' => $purchase->id,
            'method' => $method,
            'amount' => $amount,
            'notes' => $notes,
            'paid_at' => now(),
            'user_id' => auth()->id(),
        ]);
    }

    private function syncPaymentAdjustment(Purchase $purchase, float $paid, string $method): void
    {
        $currentPaid = (float) $purchase->payments()->sum('amount');
        $delta = round($paid - $currentPaid, 2);

        if (abs($delta) < 0.005) {
            return;
        }

        $this->createPayment($purchase, $delta, $method, 'Purchase edit adjustment');
    }
}
