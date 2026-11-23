<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\TenantContext;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function pos()
    {
        $customers = Customer::orderBy('name')->get();
        $warehouses = Warehouse::all();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $held = Sale::where('status', 'held')->latest()->get(['id', 'reference', 'total', 'created_at']);
        $recentTransactions = Sale::with('customer')->where('status', 'completed')->latest()->limit(10)->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'KSh';
        $products = Product::with(['stockBalances'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(80)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'category_id' => $p->category_id,
                'brand_id' => $p->brand_id,
                'retail_price' => (float) $p->retail_price,
                'wholesale_price' => (float) ($p->wholesale_price ?: $p->retail_price),
                'stock' => (float) $p->stockBalances->sum('quantity'),
            ]);

        return view('pos.index', compact('customers', 'warehouses', 'categories', 'brands', 'held', 'recentTransactions', 'products', 'currency'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|string',
            'payments.*.amount' => 'required_with:payments|numeric|min:0',
            'notes' => 'nullable|string',
            'staff_note' => 'nullable|string',
            'shipping_status' => 'nullable|string',
            'shipping_details' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, &$sale) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += ($it['quantity'] * $it['unit_price']) - ($it['discount'] ?? 0);
            }
            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;

            // split payments support
            $payments = $data['payments'] ?? null;
            if ($payments) {
                $paid = array_sum(array_column($payments, 'amount'));
            } else {
                $paid = $data['paid'] ?? $total;
                $payments = [['method' => $data['payment_method'] ?? 'cash', 'amount' => $paid]];
            }
            $methodLabel = count($payments) > 1 ? 'Multiple Pay' : ($payments[0]['method'] ?? 'cash');

            $sale = Sale::create([
                'reference' => (string) random_int(10000, 99999),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'completed',
                'type' => 'retail',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, $total - $paid),
                'payment_status' => ($paid >= $total) ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
                'payment_method' => $methodLabel,
                'shipping_status' => $data['shipping_status'] ?? null,
                'shipping_details' => $data['shipping_details'] ?? null,
                'notes' => $data['notes'] ?? null,
                'staff_note' => $data['staff_note'] ?? null,
                'sold_at' => now(),
            ]);

            AuditLog::record('sale.created', $sale, [
                'reference' => $sale->reference,
                'type' => 'retail',
                'total' => $total,
                'warehouse_id' => (int) $data['warehouse_id'],
            ]);

            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'discount' => $it['discount'] ?? 0,
                    'tax' => 0,
                    'total' => ($it['quantity'] * $it['unit_price']) - ($it['discount'] ?? 0),
                ]);

                $bal = $this->deductStock($product, (int) $data['warehouse_id'], (float) $it['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $data['warehouse_id'],
                    'type' => 'out',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'quantity' => -$it['quantity'],
                    'unit_cost' => $product->purchase_price,
                    'balance_after' => $bal->quantity,
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($payments as $p) {
                if (($p['amount'] ?? 0) > 0) {
                    Payment::create([
                        'reference' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                        'payable_type' => Sale::class,
                        'payable_id' => $sale->id,
                        'method' => $p['method'],
                        'amount' => $p['amount'],
                        'paid_at' => now(),
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            if (! empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                $customer->balance += ($total - $paid);
                $customer->save();
            }
        });

        return response()->json(['success' => true, 'sale_id' => $sale->id, 'reference' => $sale->reference]);
    }

    public function hold(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, &$sale) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += ($it['quantity'] * $it['unit_price']) - ($it['discount'] ?? 0);
            }
            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;

            $sale = Sale::create([
                'reference' => 'HLD-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'held',
                'type' => 'retail',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid' => 0,
                'balance' => $total,
                'payment_status' => 'held',
                'sold_at' => now(),
            ]);

            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'discount' => $it['discount'] ?? 0,
                    'tax' => 0,
                    'total' => ($it['quantity'] * $it['unit_price']) - ($it['discount'] ?? 0),
                ]);
            }
        });

        return response()->json(['success' => true, 'sale_id' => $sale->id, 'reference' => $sale->reference]);
    }

    public function held()
    {
        $held = Sale::where('status', 'held')->with('customer', 'items')->latest()->get();

        return response()->json($held->map(fn ($s) => [
            'id' => $s->id,
            'reference' => $s->reference,
            'customer' => $s->customer->name ?? 'Walk-in',
            'total' => (float) $s->total,
            'items' => $s->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'name' => $i->product_name,
                'price' => (float) $i->unit_price,
                'qty' => (float) $i->quantity,
                'discount' => (float) $i->discount,
            ]),
            'customer_id' => $s->customer_id,
            'warehouse_id' => $s->warehouse_id,
            'discount' => (float) $s->discount,
            'tax' => (float) $s->tax,
        ]));
    }

    public function resume(Sale $sale)
    {
        $sale->load('items');

        return response()->json([
            'id' => $sale->id,
            'reference' => $sale->reference,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'discount' => (float) $sale->discount,
            'tax' => (float) $sale->tax,
            'items' => $sale->items->map(fn ($i) => [
                'id' => $i->product_id,
                'name' => $i->product_name,
                'price' => (float) $i->unit_price,
                'qty' => (float) $i->quantity,
                'discount' => (float) $i->discount,
            ]),
        ]);
    }

    public function complete(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|string',
            'payments.*.amount' => 'required_with:payments|numeric|min:0',
        ]);

        DB::transaction(function () use ($sale, $data) {
            $sale->load('items');
            $payments = $data['payments'] ?? null;
            if ($payments) {
                $paid = array_sum(array_column($payments, 'amount'));
            } else {
                $paid = $data['paid'] ?? $sale->total;
                $payments = [['method' => $data['payment_method'] ?? 'cash', 'amount' => $paid]];
            }

            foreach ($sale->items as $it) {
                $product = Product::find($it->product_id);
                $bal = $this->deductStock($product, (int) $sale->warehouse_id, (float) $it->quantity);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $sale->warehouse_id,
                    'type' => 'out',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'quantity' => -$it->quantity,
                    'unit_cost' => $product->purchase_price,
                    'balance_after' => $bal->quantity,
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($payments as $p) {
                if (($p['amount'] ?? 0) > 0) {
                    Payment::create([
                        'reference' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                        'payable_type' => Sale::class,
                        'payable_id' => $sale->id,
                        'method' => $p['method'],
                        'amount' => $p['amount'],
                        'paid_at' => now(),
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            $sale->status = 'completed';
            $sale->paid = $paid;
            $sale->balance = $sale->total - $paid;
            $sale->payment_status = ($paid >= $sale->total) ? 'paid' : 'partial';
            $sale->reference = str_replace('HLD-', 'SAL-', $sale->reference);
            $sale->save();

            AuditLog::record('sale.completed', $sale, [
                'reference' => $sale->reference,
                'paid' => $paid,
                'total' => (float) $sale->total,
            ]);

            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                $customer->balance += ($sale->total - $paid);
                $customer->save();
            }
        });

        return response()->json(['success' => true, 'sale_id' => $sale->id, 'reference' => $sale->reference]);
    }

    public function discard(Sale $sale)
    {
        if ($sale->status !== 'held') {
            return response()->json(['success' => false], 422);
        }
        $sale->items()->delete();
        $sale->delete();

        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        $sales = Sale::with(['customer', 'user', 'warehouse', 'payments'])
            ->withSum('items as total_items', 'quantity')
            ->withSum('returns as return_due', 'amount')
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('sold_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('sold_at', '<=', $request->end_date))
            ->when($request->filled('business_location'), fn ($q) => $q->where('warehouse_id', $request->business_location))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('shipping_status'), fn ($q) => $q->where('shipping_status', $request->shipping_status))
            ->when($request->filled('payment_method'), fn ($q) => $q->whereHas('payments', fn ($pq) => $pq->where('method', $request->payment_method)))
            ->when($request->filled('sell_status'), fn ($q) => $q->where('status', $request->sell_status))
            ->latest('sold_at')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $users = User::where('tenant_id', app(TenantContext::class)->id())
            ->orderBy('name')
            ->get();

        return view('sales.index', compact('sales', 'warehouses', 'customers', 'users'));
    }

    public function create()
    {
        return view('sales.create', [
            'customers' => Customer::orderBy('name')->get(),
            'warehouses' => Warehouse::all(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeDirect(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'shipping_status' => 'nullable|string',
            'shipping_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'staff_note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, &$sale) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += $it['quantity'] * $it['unit_price'];
            }
            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;
            $paid = $data['paid'] ?? $total;

            $sale = Sale::create([
                'reference' => (string) random_int(10000, 99999),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'completed',
                'type' => 'direct',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, $total - $paid),
                'payment_status' => ($paid >= $total) ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'shipping_status' => $data['shipping_status'] ?? null,
                'shipping_details' => $data['shipping_details'] ?? null,
                'notes' => $data['notes'] ?? null,
                'staff_note' => $data['staff_note'] ?? null,
                'sold_at' => now(),
            ]);

            AuditLog::record('sale.created', $sale, [
                'reference' => $sale->reference,
                'type' => 'direct',
                'total' => $total,
                'warehouse_id' => (int) $data['warehouse_id'],
            ]);

            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $it['quantity'] * $it['unit_price'],
                ]);

                $bal = $this->deductStock($product, (int) $data['warehouse_id'], (float) $it['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $data['warehouse_id'],
                    'type' => 'out',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'quantity' => -$it['quantity'],
                    'unit_cost' => $product->purchase_price,
                    'balance_after' => $bal->quantity,
                    'user_id' => auth()->id(),
                ]);
            }

            if ($paid > 0) {
                Payment::create([
                    'reference' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                    'payable_type' => Sale::class,
                    'payable_id' => $sale->id,
                    'method' => $data['payment_method'] ?? 'cash',
                    'amount' => $paid,
                    'paid_at' => now(),
                    'user_id' => auth()->id(),
                ]);
            }

            if (! empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                $customer->balance += ($total - $paid);
                $customer->save();
            }
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Sale created.');
    }

    public function posList()
    {
        $sales = Sale::where('type', 'retail')
            ->with(['customer', 'user', 'warehouse', 'payments'])
            ->withSum('items as total_items', 'quantity')
            ->withSum('returns as return_due', 'amount')
            ->latest()
            ->paginate(20);

        return view('sales.pos_list', compact('sales'));
    }

    public function drafts()
    {
        $drafts = Sale::whereIn('status', ['draft', 'held'])->with('customer', 'user')->latest()->paginate(20);

        return view('sales.drafts', compact('drafts'));
    }

    public function createDraft()
    {
        return view('sales.create_draft', [
            'customers' => Customer::orderBy('name')->get(),
            'warehouses' => Warehouse::all(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeDraft(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, &$sale) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += $it['quantity'] * $it['unit_price'];
            }
            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;

            $sale = Sale::create([
                'reference' => 'DRF-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'draft',
                'type' => 'direct',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid' => 0,
                'balance' => $total,
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'sold_at' => now(),
            ]);

            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $it['quantity'] * $it['unit_price'],
                ]);
            }
        });

        return redirect()->route('sales.drafts.index')->with('success', 'Draft saved.');
    }

    public function shipments()
    {
        $sales = Sale::with('customer', 'warehouse')->latest()->paginate(20);

        return view('sales.shipments', compact('sales'));
    }

    public function import()
    {
        return view('sales.import');
    }

    public function importStore(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $count = 0;
        $whId = Warehouse::query()->value('id');

        while (($row = fgetcsv($handle)) !== false) {
            if (! $header || count($header) !== count($row)) {
                continue;
            }
            $data = array_combine($header, $row);
            $product = ! empty($data['sku'])
                ? Product::where('sku', $data['sku'])->first()
                : (! empty($data['product_id']) ? Product::find($data['product_id']) : null);
            if (! $product) {
                continue;
            }

            $qty = (float) ($data['quantity'] ?? 1);
            $price = (float) ($data['unit_price'] ?? $product->retail_price ?? 0);
            $total = $qty * $price;

            $sale = Sale::create([
                'reference' => $data['reference'] ?? ('IMP-'.now()->format('YmdHis').'-'.random_int(100, 999)),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'] ?? $whId,
                'status' => 'completed',
                'type' => 'import',
                'subtotal' => $total,
                'discount' => 0,
                'tax' => 0,
                'total' => $total,
                'paid' => $total,
                'balance' => 0,
                'payment_status' => 'paid',
                'sold_at' => now(),
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'unit_price' => $price,
                'discount' => 0,
                'tax' => 0,
                'total' => $total,
            ]);

            $count++;
        }
        fclose($handle);

        return redirect()->route('sales.index')->with('success', "Imported {$count} sales.");
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'payments', 'user', 'warehouse');
        $settings = Setting::pluck('value', 'key');

        return view('sales.show', compact('sale', 'settings'));
    }

    public function updateShipping(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'shipping_status' => 'nullable|string|max:100',
            'shipping_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'staff_note' => 'nullable|string',
        ]);

        $sale->shipping_status = $data['shipping_status'] ?? null;
        $sale->shipping_details = $data['shipping_details'] ?? null;
        if ($request->has('notes')) {
            $sale->notes = $data['notes'];
        }
        if ($request->has('staff_note')) {
            $sale->staff_note = $data['staff_note'];
        }
        $sale->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Shipping updated for Invoice {$sale->reference}.");
    }

    public function receipt(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'payments', 'user', 'warehouse');
        $settings = Setting::pluck('value', 'key');

        return view('sales.receipt', compact('sale', 'settings'));
    }

    public function deliveryNote(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'warehouse');
        $settings = Setting::pluck('value', 'key');

        return view('sales.delivery_note', compact('sale', 'settings'));
    }

    /**
     * Lock the stock row and deduct the sold quantity, refusing overselling.
     */
    private function deductStock(Product $product, int $warehouseId, float $quantity): StockBalance
    {
        $balance = StockBalance::where('product_id', $product->id)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if ($balance === null || (float) $balance->quantity < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Insufficient stock for {$product->name}.",
            ]);
        }

        $balance->quantity = (float) $balance->quantity - $quantity;
        $balance->save();

        return $balance;
    }
}
