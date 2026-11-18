<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        return view('quotations.index', ['quotations' => Quotation::with('customer')->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('quotations.create', [
            'customers' => Customer::all(),
            'products' => Product::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'valid_until' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, &$q) {
            $subtotal = 0;
            foreach ($data['items'] as $it) {
                $subtotal += $it['quantity'] * $it['unit_price'];
            }
            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;
            $q = Quotation::create([
                'reference' => 'QT-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'draft',
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['items'] as $it) {
                $product = Product::find($it['product_id']);
                QuotationItem::create([
                    'quotation_id' => $q->id,
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'quotation_id' => $q->id,
                'reference' => $q->reference,
            ]);
        }

        return redirect()->route('quotations.show', $q)->with('success', 'Quotation created.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('items.product', 'customer');

        return view('quotations.show', compact('quotation'));
    }

    public function export()
    {
        $qs = Quotation::with('customer')->get();
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="quotations.csv"'];
        $callback = function () use ($qs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Customer', 'Subtotal', 'Discount', 'Tax', 'Total', 'Status', 'Valid Until']);
            foreach ($qs as $q) {
                fputcsv($out, [$q->reference, $q->customer->name ?? 'Walk-in', $q->subtotal, $q->discount, $q->tax, $q->total, $q->status, $q->valid_until]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function convert(Request $request, Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Already converted.');
        }
        $data = $request->validate([
            'paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|string',
            'payments.*.amount' => 'required_with:payments|numeric|min:0',
        ]);
        DB::transaction(function () use ($quotation, $data, &$sale) {
            $payments = $data['payments'] ?? null;
            if ($payments) {
                $paid = array_sum(array_column($payments, 'amount'));
            } else {
                $paid = $data['paid'] ?? $quotation->total;
                $payments = [['method' => $data['payment_method'] ?? 'cash', 'amount' => $paid]];
            }
            $total = $quotation->total;

            $whId = Warehouse::query()->value('id');
            $sale = Sale::create([
                'reference' => 'SL-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'customer_id' => $quotation->customer_id,
                'user_id' => auth()->id(),
                'warehouse_id' => $whId,
                'status' => 'completed',
                'type' => 'retail',
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'tax' => $quotation->tax,
                'total' => $total,
                'paid' => $paid,
                'balance' => $total - $paid,
                'payment_status' => ($paid >= $total) ? 'paid' : 'partial',
                'sold_at' => now(),
            ]);
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
            foreach ($quotation->items as $it) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $it->product_id,
                    'product_name' => $it->product_name,
                    'quantity' => $it->quantity,
                    'unit_price' => $it->unit_price,
                    'discount' => $it->discount,
                    'tax' => $it->tax,
                    'total' => $it->total,
                ]);
            }
            if ($quotation->customer_id) {
                $customer = Customer::find($quotation->customer_id);
                $customer->balance += ($total - $paid);
                $customer->save();
            }
            $quotation->status = 'converted';
            $quotation->save();
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Quotation converted to sale.');
    }
}
