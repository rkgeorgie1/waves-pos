<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index()
    {
        return view('sales_returns.index', ['returns' => SalesReturn::with(['sale', 'customer'])->latest()->paginate(20)]);
    }

    public function create(Request $request)
    {
        $sale = $request->filled('sale_id') ? Sale::with('items.product')->find($request->sale_id) : null;

        return view('sales_returns.create', [
            'sale' => $sale,
            'sales' => Sale::with('customer')->latest()->limit(50)->get(),
            'products' => Product::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_id' => 'nullable|exists:sales,id',
            'customer_id' => 'nullable|exists:customers,id',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, &$ret) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += $it['quantity'] * $it['unit_price'];
            }
            $ret = SalesReturn::create([
                'reference' => 'SR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'sale_id' => $data['sale_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'amount' => $subtotal,
                'reason' => $data['reason'] ?? null,
                'status' => 'completed',
            ]);
            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                SalesReturnItem::create([
                    'sales_return_id' => $ret->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'total' => $it['quantity'] * $it['unit_price'],
                ]);
                // restock into default warehouse (first)
                $whId = Warehouse::query()->value('id');
                if ($whId) {
                    $bal = StockBalance::firstOrCreate(['product_id' => $product->id, 'warehouse_id' => $whId], ['quantity' => 0]);
                    $bal->quantity += $it['quantity'];
                    $bal->save();
                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $whId,
                        'type' => 'in',
                        'reference_type' => 'sales_return',
                        'reference_id' => $ret->id,
                        'quantity' => $it['quantity'],
                        'unit_cost' => $it['unit_price'],
                        'balance_after' => $bal->quantity,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('sales_returns.show', $ret)->with('success', 'Sales return recorded.');
    }

    public function export()
    {
        $returns = SalesReturn::with('customer', 'sale')->get();
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="sales_returns.csv"'];
        $callback = function () use ($returns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Sale', 'Customer', 'Amount', 'Status', 'Date', 'Reason']);
            foreach ($returns as $r) {
                fputcsv($out, [$r->reference, $r->sale->reference ?? '-', $r->customer->name ?? '-', $r->amount, $r->status, $r->created_at, $r->reason]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load('items.product', 'sale', 'customer');

        return view('sales_returns.show', compact('salesReturn'));
    }
}
