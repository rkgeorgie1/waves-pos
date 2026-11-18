<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\StockBalance;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $dailySales = Sale::selectRaw('DATE(created_at) as d, SUM(total) as t, COUNT(*) as c')->groupBy('d')->orderByDesc('d')->limit(14)->get();
        $lowStock = StockBalance::with('product', 'warehouse')->whereColumn('quantity', '<=', DB::raw('0'))->get();
        $topProducts = DB::table('sale_items')->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total) as revenue'))->groupBy('product_name')->orderByDesc('revenue')->limit(10)->get();

        return view('reports.index', compact('dailySales', 'lowStock', 'topProducts'));
    }

    public function sales(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $sales = Sale::with('customer', 'user')
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->latest()->paginate(50);

        $summary = [
            'count' => Sale::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->count(),
            'total' => Sale::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->sum('total'),
            'paid' => Sale::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->sum('paid'),
        ];

        return view('reports.sales', compact('sales', 'from', 'to', 'summary'));
    }

    public function inventory()
    {
        $balances = StockBalance::with(['product', 'warehouse'])->orderBy('quantity')->get();

        return view('reports.inventory', compact('balances'));
    }

    public function stock(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status', 'all');
        $balances = StockBalance::with(['product', 'warehouse'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get()
            ->filter(function ($b) use ($status) {
                if ($status === 'in_stock') {
                    return $b->quantity > 0;
                }
                if ($status === 'out_of_stock') {
                    return $b->quantity <= 0;
                }
                if ($status === 'low_stock') {
                    return $b->product && $b->quantity > 0 && $b->quantity <= $b->product->min_stock_level;
                }

                return true;
            })
            ->sortBy('quantity')->values();

        $summary = [
            'total_products' => $balances->count(),
            'in_stock' => $balances->where('quantity', '>', 0)->count(),
            'low_stock' => $balances->filter(fn ($b) => $b->product && $b->quantity <= $b->product->min_stock_level && $b->quantity > 0)->count(),
            'out_of_stock' => $balances->where('quantity', '<=', 0)->count(),
            'stock_value' => $balances->sum(fn ($b) => $b->quantity * ($b->product->purchase_price ?? 0)),
        ];

        $warehouses = Warehouse::all();

        return view('reports.stock', compact('balances', 'summary', 'warehouses', 'warehouseId', 'status'));
    }

    public function exportStock(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status', 'all');
        $balances = StockBalance::with(['product', 'warehouse'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get()
            ->filter(function ($b) use ($status) {
                if ($status === 'in_stock') {
                    return $b->quantity > 0;
                }
                if ($status === 'out_of_stock') {
                    return $b->quantity <= 0;
                }
                if ($status === 'low_stock') {
                    return $b->product && $b->quantity > 0 && $b->quantity <= $b->product->min_stock_level;
                }

                return true;
            })->values();
        $headings = ['Product', 'SKU', 'Warehouse', 'Quantity', 'Min Level', 'Status'];
        $rows = $balances->map(fn ($b) => [
            $b->product->name ?? '-',
            $b->product->sku ?? '-',
            $b->warehouse->name ?? '-',
            (int) $b->quantity,
            $b->product->min_stock_level ?? 0,
            $b->quantity <= 0 ? 'Out of Stock' : ($b->product && $b->quantity <= $b->product->min_stock_level ? 'Low Stock' : 'In Stock'),
        ])->toArray();
        $title = 'Stock Report';

        return $this->exportTable($title, 'stock_report', $headings, $rows, $request->get('format', 'csv'));
    }

    public function profit(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $revenue = Sale::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->sum('total');
        $cost = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sale_items.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('SUM(sale_items.quantity * products.purchase_price) as c')
            ->value('c') ?? 0;
        $expenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');

        return view('reports.profit', compact('from', 'to', 'revenue', 'cost', 'expenses'));
    }

    public function exportSales(Request $request)
    {
        $format = $request->get('format', 'csv');
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $sales = Sale::with('customer')->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->get();

        $headings = ['Reference', 'Date', 'Customer', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Balance', 'Status'];
        $rows = $sales->map(fn ($s) => [$s->reference, (string) $s->created_at, $s->customer->name ?? 'Walk-in', $s->subtotal, $s->discount, $s->tax, $s->total, $s->paid, $s->balance, $s->status])->toArray();
        $title = "Sales Report ({$from} to {$to})";

        return $this->exportTable($title, "sales_{$from}_to_{$to}", $headings, $rows, $format);
    }

    public function exportProfit(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $revenue = Sale::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->sum('total');
        $cost = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sale_items.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('SUM(sale_items.quantity * products.purchase_price) as c')
            ->value('c') ?? 0;
        $expenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');

        $headings = ['Metric', 'Amount'];
        $rows = [
            ['Period From', $from],
            ['Period To', $to],
            ['Revenue (Sales)', $revenue],
            ['Cost of Goods Sold', $cost],
            ['Gross Profit', $revenue - $cost],
            ['Expenses', $expenses],
            ['Net Profit', $revenue - $cost - $expenses],
        ];
        $title = "Profit Report ({$from} to {$to})";

        return $this->exportTable($title, "profit_{$from}_to_{$to}", $headings, $rows, $request->get('format', 'csv'));
    }

    protected function exportTable(string $title, string $baseName, array $headings, array $rows, string $format = 'csv')
    {
        if ($format === 'xlsx') {
            $export = new class($headings, $rows) implements FromArray, WithHeadings
            {
                public function __construct(private array $h, private array $r) {}

                public function array(): array
                {
                    return $this->r;
                }

                public function headings(): array
                {
                    return $this->h;
                }
            };

            return Excel::download($export, $baseName.'.xlsx');
        }

        if ($format === 'pdf') {
            $html = view('reports.export_pdf', compact('title', 'headings', 'rows'))->render();

            return Pdf::loadHTML($html)->download($baseName.'.pdf');
        }

        if ($format === 'doc') {
            $html = view('reports.export_doc', compact('title', 'headings', 'rows'))->render();

            return response($html, 200, [
                'Content-Type' => 'application/msword',
                'Content-Disposition' => "attachment; filename=\"{$baseName}.doc\"",
            ]);
        }

        // default csv
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$baseName}.csv\""];
        $callback = function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headings);
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportInventory(Request $request)
    {
        $format = $request->get('format', 'csv');
        $balances = StockBalance::with(['product', 'warehouse'])->get();
        $headings = ['Product', 'SKU', 'Warehouse', 'Quantity', 'Min Level', 'Stock Value'];
        $rows = $balances->map(fn ($b) => [$b->product->name ?? '-', $b->product->sku ?? '-', $b->warehouse->name ?? '-', $b->quantity, $b->product->min_stock_level ?? 0, ($b->quantity * ($b->product->purchase_price ?? 0))])->toArray();
        $title = 'Inventory Report';

        return $this->exportTable($title, 'inventory', $headings, $rows, $format);
    }
}
