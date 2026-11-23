<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'quantity' => 'required|numeric|min:0.0001',
            'type' => 'required|string|in:add,subtract',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $qty = $data['type'] === 'add' ? (float) $data['quantity'] : -(float) $data['quantity'];

            $bal = StockBalance::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->lockForUpdate()
                ->first();

            if ($bal === null) {
                if ($qty < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Insufficient stock for this adjustment.',
                    ]);
                }

                $bal = StockBalance::create([
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'quantity' => 0,
                    'avg_cost' => 0,
                ]);
            }

            $balanceBefore = (float) $bal->quantity;
            $newQuantity = $balanceBefore + $qty;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock for this adjustment.',
                ]);
            }

            $adjustment = StockAdjustment::create([
                'reference' => 'ADJ-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'warehouse_id' => $data['warehouse_id'],
                'reason' => $data['reason'],
                'notes' => "Product #{$data['product_id']}: {$data['type']} ".abs($qty),
                'user_id' => auth()->id(),
            ]);

            $bal->quantity = $newQuantity;
            $bal->save();

            StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'adjustment',
                'reference_type' => 'stock_adjustment',
                'reference_id' => $adjustment->id,
                'quantity' => $qty,
                'balance_after' => $newQuantity,
                'notes' => $data['reason'],
                'user_id' => auth()->id(),
            ]);

            AuditLog::record(
                'stock.adjusted',
                $adjustment,
                [
                    'product_id' => (int) $data['product_id'],
                    'warehouse_id' => (int) $data['warehouse_id'],
                    'quantity' => $qty,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $newQuantity,
                ],
                ['quantity' => $balanceBefore],
            );
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
