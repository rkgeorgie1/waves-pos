<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $returns = PurchaseReturn::with(['supplier', 'purchase'])->latest()->paginate(20);

        return view('purchase_returns.index', compact('returns'));
    }

    public function create()
    {
        return view('purchase_returns.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'purchases' => Purchase::with('supplier')->latest()->limit(50)->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, &$return) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += $it['quantity'] * $it['unit_cost'];
            }

            $return = PurchaseReturn::create([
                'reference' => 'PR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'purchase_id' => $data['purchase_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'amount' => $subtotal,
                'reason' => $data['reason'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                $lineTotal = $it['quantity'] * $it['unit_cost'];

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'unit_cost' => $it['unit_cost'],
                    'total' => $lineTotal,
                ]);

                $bal = StockBalance::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $data['warehouse_id']],
                    ['quantity' => 0, 'avg_cost' => 0]
                );
                $bal->quantity -= $it['quantity'];
                $bal->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $data['warehouse_id'],
                    'type' => 'out',
                    'reference_type' => 'purchase_return',
                    'reference_id' => $return->id,
                    'quantity' => -$it['quantity'],
                    'unit_cost' => $it['unit_cost'],
                    'balance_after' => $bal->quantity,
                    'user_id' => auth()->id(),
                ]);
            }

            if (! empty($data['supplier_id'])) {
                $sup = Supplier::find($data['supplier_id']);
                $sup->balance -= $subtotal;
                $sup->save();
            }
        });

        return redirect()->route('purchase_returns.index')->with('success', 'Purchase return recorded.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('supplier', 'purchase', 'items.product');

        return view('purchase_returns.show', compact('purchaseReturn'));
    }
}
