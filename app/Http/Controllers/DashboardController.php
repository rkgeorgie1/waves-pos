<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Setting;
use App\Models\StockBalance;
use App\Models\Supplier;
use App\Models\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $start = match ($period) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
        $end = $period === 'yesterday' ? now()->subDay()->endOfDay() : now()->endOfDay();
        $tenantId = app(TenantContext::class)->id();

        $currency = Setting::where('key', 'currency')->value('value') ?? 'KSh';

        $todaySales = Sale::whereBetween('created_at', [$start, $end])->where('status', 'completed')->sum('total');
        $todayOrdersCount = Sale::whereBetween('created_at', [$start, $end])->where('status', 'completed')->count();
        $monthSales = $todaySales;

        $invoiceDue = Sale::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->sum(DB::raw('CASE WHEN total > paid THEN total - paid ELSE 0 END'));

        $totalSellReturn = SalesReturn::whereBetween('created_at', [$start, $end])->sum('amount');
        $monthPurchases = Purchase::whereBetween('created_at', [$start, $end])->sum('total');
        $purchaseDue = Purchase::whereBetween('created_at', [$start, $end])
            ->sum(DB::raw('CASE WHEN total > paid THEN total - paid ELSE 0 END'));
        $totalPurchaseReturn = PurchaseReturn::whereBetween('created_at', [$start, $end])->sum('amount');
        $monthExpenses = Expense::whereBetween('created_at', [$start, $end])->sum('amount');
        $netAmount = $todaySales - $totalSellReturn - $monthExpenses;

        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();

        $lowStockCount = StockBalance::query()
            ->join('products', 'products.id', '=', 'stock_balances.product_id')
            ->where('products.tenant_id', $tenantId)
            ->whereColumn('stock_balances.quantity', '<=', 'products.min_stock_level')
            ->count();

        $lowStockItems = StockBalance::query()
            ->join('products', 'products.id', '=', 'stock_balances.product_id')
            ->where('products.tenant_id', $tenantId)
            ->leftJoin('warehouses', 'warehouses.id', '=', 'stock_balances.warehouse_id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'warehouses.name as warehouse_name',
                'stock_balances.quantity',
                'products.min_stock_level'
            )
            ->whereColumn('stock_balances.quantity', '<=', 'products.min_stock_level')
            ->orderBy('stock_balances.quantity')
            ->limit(8)
            ->get();

        $customerDebt = Customer::sum('balance');
        $supplierDebt = Supplier::sum('balance');
        $inventoryValue = StockBalance::query()
            ->join('products', 'products.id', '=', 'stock_balances.product_id')
            ->where('products.tenant_id', $tenantId)
            ->selectRaw('SUM(stock_balances.quantity * products.purchase_price) as v')
            ->value('v') ?? 0;

        $salesPaymentDues = Sale::with('customer')
            ->whereColumn('total', '>', 'paid')
            ->latest()
            ->limit(6)
            ->get();

        $purchasePaymentDues = Purchase::with('supplier')
            ->whereColumn('total', '>', 'paid')
            ->latest()
            ->limit(6)
            ->get();

        $recentSales = Sale::with(['customer', 'warehouse'])->latest()->limit(8)->get();
        $topProducts = SaleItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total) as revenue'))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Sales Last 30 Days
        $chartLabels = [];
        $chartSalesData = [];
        for ($i = 29; $i >= 0; $i--) {
            $dStart = now()->subDays($i)->startOfDay();
            $dEnd = now()->subDays($i)->endOfDay();
            $chartLabels[] = $dStart->format('d M');
            $chartSalesData[] = (float) Sale::whereBetween('created_at', [$dStart, $dEnd])
                ->where('status', 'completed')
                ->sum('total');
        }

        // Sales Current Financial Year (by month)
        $fyLabels = [];
        $fySalesData = [];
        for ($m = 11; $m >= 0; $m--) {
            $mStart = now()->subMonths($m)->startOfMonth();
            $mEnd = now()->subMonths($m)->endOfMonth();
            $fyLabels[] = $mStart->format('M Y');
            $fySalesData[] = (float) Sale::whereBetween('created_at', [$mStart, $mEnd])
                ->where('status', 'completed')
                ->sum('total');
        }

        $paymentBreakdown = Payment::select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        return view('dashboard', compact(
            'period',
            'currency',
            'todaySales',
            'todayOrdersCount',
            'monthSales',
            'netAmount',
            'invoiceDue',
            'totalSellReturn',
            'monthPurchases',
            'purchaseDue',
            'totalPurchaseReturn',
            'monthExpenses',
            'totalProducts',
            'totalCustomers',
            'totalSuppliers',
            'lowStockCount',
            'lowStockItems',
            'customerDebt',
            'supplierDebt',
            'inventoryValue',
            'salesPaymentDues',
            'purchasePaymentDues',
            'recentSales',
            'topProducts',
            'chartLabels',
            'chartSalesData',
            'fyLabels',
            'fySalesData',
            'paymentBreakdown'
        ));
    }
}
