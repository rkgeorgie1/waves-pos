<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'warehouse'])
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('ordered_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('ordered_at', '<=', $request->end_date))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('ordered_at')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference' => 'nullable|string|max:100|unique:purchases,reference',
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
        ]);

        DB::transaction(function () use ($data, &$purchase) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $discPct = (float) ($it['discount_percent'] ?? 0);
                $netUnitCost = $it['unit_cost'] * (1 - ($discPct / 100));
                $subtotal += $it['quantity'] * $netUnitCost;
            }

            $discType = $data['discount_type'] ?? 'none';
            $discInput = (float) ($data['discount_amount'] ?? 0);
            $orderDiscount = $discType === 'percentage'
                ? ($subtotal * ($discInput / 100))
                : ($discType === 'fixed' ? $discInput : 0);

            $tax = (float) ($data['tax'] ?? 0);
            $shipping = (float) ($data['shipping_charges'] ?? 0);
            $total = max(0, $subtotal - $orderDiscount + $tax + $shipping);
            $paid = (float) ($data['paid'] ?? 0);
            $status = $data['status'] ?? 'received';

            $purchase = Purchase::create([
                'reference' => ! empty($data['reference']) ? $data['reference'] : ('PO-'.now()->format('YmdHis').'-'.random_int(100, 999)),
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => $status,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, $total - $paid),
                'notes' => $data['notes'] ?? null,
                'ordered_at' => ! empty($data['ordered_at']) ? $data['ordered_at'] : now(),
            ]);

            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                $discPct = (float) ($it['discount_percent'] ?? 0);
                $netUnitCost = $it['unit_cost'] * (1 - ($discPct / 100));
                $lineTotal = $it['quantity'] * $netUnitCost;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'received_quantity' => $status === 'received' ? $it['quantity'] : 0,
                    'unit_cost' => $netUnitCost,
                    'total' => $lineTotal,
                ]);

                // Update product purchase & selling price if provided
                $product->purchase_price = $netUnitCost;
                if (! empty($it['selling_price']) && $it['selling_price'] > 0) {
                    $product->retail_price = $it['selling_price'];
                }
                $product->save();

                if ($status === 'received') {
                    $bal = StockBalance::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $data['warehouse_id']],
                        ['quantity' => 0, 'avg_cost' => 0]
                    );
                    $bal->quantity += $it['quantity'];
                    $bal->avg_cost = $netUnitCost;
                    $bal->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $data['warehouse_id'],
                        'type' => 'in',
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'quantity' => $it['quantity'],
                        'unit_cost' => $netUnitCost,
                        'balance_after' => $bal->quantity,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            if ($paid > 0) {
                Payment::create([
                    'reference' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                    'payable_type' => Purchase::class,
                    'payable_id' => $purchase->id,
                    'method' => $data['payment_method'] ?? 'cash',
                    'amount' => $paid,
                    'paid_at' => now(),
                    'user_id' => auth()->id(),
                ]);
            }

            if (! empty($data['supplier_id']) && $status === 'received') {
                $sup = Supplier::find($data['supplier_id']);
                $sup->balance += max(0, $total - $paid);
                $sup->save();
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase saved successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('items', 'supplier', 'warehouse');

        return view('purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $remaining = $item->quantity - $item->received_quantity;
                if ($remaining <= 0) {
                    continue;
                }
                $bal = StockBalance::firstOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $purchase->warehouse_id], ['quantity' => 0, 'avg_cost' => 0]);
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
}
