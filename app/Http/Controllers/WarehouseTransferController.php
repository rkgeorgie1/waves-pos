<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseTransferController extends Controller
{
    public function index()
    {
        return view('warehouse_transfers.index', ['transfers' => WarehouseTransfer::with(['fromWarehouse', 'toWarehouse'])->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('warehouse_transfers.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        DB::transaction(function () use ($data, &$t) {
            $t = WarehouseTransfer::create([
                'reference' => 'TR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                WarehouseTransferItem::create([
                    'warehouse_transfer_id' => $t->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                ]);
            }
        });

        return redirect()->route('warehouse_transfers.show', $t)->with('success', 'Transfer created.');
    }

    public function show(WarehouseTransfer $warehouseTransfer)
    {
        $warehouseTransfer->load('items.product', 'fromWarehouse', 'toWarehouse');

        return view('warehouse_transfers.show', compact('warehouseTransfer'));
    }

    public function deliveryNote(WarehouseTransfer $warehouseTransfer)
    {
        $warehouseTransfer->load('items.product', 'fromWarehouse', 'toWarehouse');

        return view('warehouse_transfers.delivery_note', compact('warehouseTransfer'));
    }

    public function complete(WarehouseTransfer $warehouseTransfer)
    {
        if ($warehouseTransfer->status !== 'pending') {
            return back()->with('error', 'Already processed.');
        }
        DB::transaction(function () use ($warehouseTransfer) {
            foreach ($warehouseTransfer->items as $it) {
                $from = StockBalance::firstOrCreate(['product_id' => $it->product_id, 'warehouse_id' => $warehouseTransfer->from_warehouse_id], ['quantity' => 0]);
                $from->quantity -= $it->quantity;
                $from->save();
                $to = StockBalance::firstOrCreate(['product_id' => $it->product_id, 'warehouse_id' => $warehouseTransfer->to_warehouse_id], ['quantity' => 0]);
                $to->quantity += $it->quantity;
                $to->save();
                StockMovement::create(['product_id' => $it->product_id, 'warehouse_id' => $warehouseTransfer->from_warehouse_id, 'type' => 'out', 'reference_type' => 'transfer', 'reference_id' => $warehouseTransfer->id, 'quantity' => -$it->quantity, 'balance_after' => $from->quantity, 'user_id' => auth()->id()]);
                StockMovement::create(['product_id' => $it->product_id, 'warehouse_id' => $warehouseTransfer->to_warehouse_id, 'type' => 'in', 'reference_type' => 'transfer', 'reference_id' => $warehouseTransfer->id, 'quantity' => $it->quantity, 'balance_after' => $to->quantity, 'user_id' => auth()->id()]);
            }
            $warehouseTransfer->status = 'completed';
            $warehouseTransfer->transferred_at = now();
            $warehouseTransfer->save();
        });

        return redirect()->route('warehouse_transfers.show', $warehouseTransfer)->with('success', 'Transfer completed.');
    }
}
