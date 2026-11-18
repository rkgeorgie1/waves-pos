<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $balances = StockBalance::with(['product', 'warehouse'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$request->q}%")->orWhere('sku', 'like', "%{$request->q}%")))
            ->latest()->paginate(30);

        $warehouses = Warehouse::all();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('inventory.index', compact('balances', 'warehouses', 'products'));
    }

    public function adjust(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric',
            'type' => 'required|string|in:add,subtract',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $qty = $data['type'] === 'add' ? (float) $data['quantity'] : -(float) $data['quantity'];

            $bal = StockBalance::firstOrCreate(
                ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']],
                ['quantity' => 0, 'avg_cost' => 0]
            );
            $bal->quantity += $qty;
            $bal->save();

            StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'adjustment',
                'reference_type' => 'stock_adjustment',
                'quantity' => $qty,
                'balance_after' => $bal->quantity,
                'notes' => $data['reason'],
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
