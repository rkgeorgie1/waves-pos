<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductToolsController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SellingPriceGroupController;
use App\Http\Controllers\SerialNumberController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierLedgerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'index'])->name('home');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// Kept outside the subscription gate so merchants can manage billing after a lapse.
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/pay/{plan}', [BillingController::class, 'pay'])->name('billing.pay');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
});

Route::middleware(['auth', 'verified', 'tenant', 'subscription'])->group(function () {
    Route::middleware('permission:dashboard.view')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:products.manage')->group(function () {
        Route::get('/products/update-price', [ProductToolsController::class, 'updatePrice'])->name('products.update_price');
        Route::post('/products/update-price', [ProductToolsController::class, 'updatePriceStore'])->name('products.update_price.store');
        Route::get('/products/import', [ProductToolsController::class, 'import'])->name('products.import');
        Route::post('/products/import', [ProductToolsController::class, 'importStore'])->name('products.import.store');
        Route::get('/products/print-labels', [ProductToolsController::class, 'printLabels'])->name('products.print_labels');

        Route::resource('products', ProductController::class);
        Route::resource('warranties', WarrantyController::class)->only(['index', 'create', 'store']);
        Route::resource('selling_price_groups', SellingPriceGroupController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/selling_price_groups/{sellingPriceGroup}/assign', [SellingPriceGroupController::class, 'assign'])->name('selling_price_groups.assign');
        Route::resource('product_variants', ProductVariantController::class)->only(['index', 'create', 'store']);
        Route::resource('product_batches', ProductBatchController::class)->only(['index', 'create', 'store']);
        Route::resource('serial_numbers', SerialNumberController::class)->only(['index', 'create', 'store']);

        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('units', UnitController::class);
    });

    Route::middleware('permission:pos.access')->group(function () {
        Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
        Route::post('/pos/sale', [SaleController::class, 'store'])->name('pos.store');
        Route::post('/pos/hold', [SaleController::class, 'hold'])->name('pos.hold');
        Route::get('/pos/held', [SaleController::class, 'held'])->name('pos.held');
        Route::get('/pos/held/{sale}', [SaleController::class, 'resume'])->name('pos.resume');
        Route::post('/pos/held/{sale}/complete', [SaleController::class, 'complete'])->name('pos.complete');
        Route::delete('/pos/held/{sale}', [SaleController::class, 'discard'])->name('pos.discard');
    });

    Route::middleware('permission:sales.manage')->group(function () {
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'storeDirect'])->name('sales.store_direct');
        Route::get('/sales/pos-list', [SaleController::class, 'posList'])->name('sales.pos_list');
        Route::get('/sales/drafts', [SaleController::class, 'drafts'])->name('sales.drafts.index');
        Route::get('/sales/drafts/create', [SaleController::class, 'createDraft'])->name('sales.drafts.create');
        Route::post('/sales/drafts', [SaleController::class, 'storeDraft'])->name('sales.drafts.store');
        Route::get('/sales/shipments', [SaleController::class, 'shipments'])->name('sales.shipments');
        Route::get('/sales/import', [SaleController::class, 'import'])->name('sales.import');
        Route::post('/sales/import', [SaleController::class, 'importStore'])->name('sales.import.store');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::post('/sales/{sale}/shipping', [SaleController::class, 'updateShipping'])->name('sales.update_shipping');
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::get('/sales/{sale}/delivery-note', [SaleController::class, 'deliveryNote'])->name('sales.delivery_note');
    });

    Route::middleware('permission:sales_returns.manage')->group(function () {
        Route::get('/sales_returns/export', [SalesReturnController::class, 'export'])->name('sales_returns.export');
        Route::resource('sales_returns', SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:quotations.manage')->group(function () {
        Route::get('/quotations/export', [QuotationController::class, 'export'])->name('quotations.export');
        Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    });

    Route::middleware('permission:purchases.manage')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    });

    Route::middleware('permission:purchase_returns.manage')->group(function () {
        Route::get('/purchase_returns', [PurchaseReturnController::class, 'index'])->name('purchase_returns.index');
        Route::get('/purchase_returns/create', [PurchaseReturnController::class, 'create'])->name('purchase_returns.create');
        Route::post('/purchase_returns', [PurchaseReturnController::class, 'store'])->name('purchase_returns.store');
        Route::get('/purchase_returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase_returns.show');
    });

    Route::middleware('permission:inventory.view')->get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/warehouse_transfers/{warehouse_transfer}/delivery-note', [WarehouseTransferController::class, 'deliveryNote'])->name('warehouse_transfers.delivery_note');
        Route::post('/warehouse_transfers/{warehouse_transfer}/complete', [WarehouseTransferController::class, 'complete'])->name('warehouse_transfers.complete');
        Route::resource('warehouse_transfers', WarehouseTransferController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:notifications.view')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    });

    Route::middleware('permission:reports.export')->group(function () {
        Route::get('/reports/export/stock', [ReportController::class, 'exportStock'])->name('reports.export.stock');
        Route::get('/reports/export/sales', [ReportController::class, 'exportSales'])->name('reports.export.sales');
        Route::get('/reports/export/inventory', [ReportController::class, 'exportInventory'])->name('reports.export.inventory');
        Route::get('/reports/export/profit', [ReportController::class, 'exportProfit'])->name('reports.export.profit');
    });

    Route::middleware('permission:cash_shifts.manage')->group(function () {
        Route::get('/cash_shifts', [CashShiftController::class, 'index'])->name('cash_shifts.index');
        Route::post('/cash_shifts/open', [CashShiftController::class, 'open'])->name('cash_shifts.open');
        Route::get('/cash_shifts/{cashShift}', [CashShiftController::class, 'show'])->name('cash_shifts.show');
        Route::post('/cash_shifts/{cashShift}/close', [CashShiftController::class, 'close'])->name('cash_shifts.close');
    });

    Route::middleware('permission:ledgers.view')->group(function () {
        Route::get('/customer_ledger', [CustomerLedgerController::class, 'index'])->name('customer_ledger.index');
        Route::get('/supplier_ledger', [SupplierLedgerController::class, 'index'])->name('supplier_ledger.index');
    });

    Route::middleware('permission:customers.manage')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('customer_groups', CustomerGroupController::class);
    });

    Route::middleware('permission:suppliers.manage')->group(function () {
        Route::resource('suppliers', SupplierController::class);
    });

    Route::middleware('permission:expenses.manage')->group(function () {
        Route::resource('expenses', ExpenseController::class);
    });

    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::get('/settings/warehouses', [SettingController::class, 'warehouses'])->name('settings.warehouses');
        Route::get('/settings/staff', [SettingController::class, 'staff'])->name('settings.staff');
        Route::get('/settings/currencies', [SettingController::class, 'currencies'])->name('settings.currencies');
        Route::get('/settings/access', [SettingController::class, 'access'])->name('settings.access');
        Route::get('/settings/receipt', [SettingController::class, 'receipt'])->name('settings.receipt');
    });

    Route::middleware('permission:settings.manage')->group(function () {
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/logo', [SettingController::class, 'uploadLogo'])->name('settings.logo.upload');
        Route::delete('/settings/logo', [SettingController::class, 'removeLogo'])->name('settings.logo.remove');
        Route::post('/settings/warehouses', [SettingController::class, 'storeWarehouse'])->name('settings.warehouses.store');
        Route::put('/settings/warehouses/{warehouse}', [SettingController::class, 'updateWarehouse'])->name('settings.warehouses.update');
        Route::post('/settings/staff', [SettingController::class, 'storeStaff'])->name('settings.staff.store');
        Route::post('/settings/staff/{user}/toggle', [SettingController::class, 'toggleStaff'])->name('settings.staff.toggle');
        Route::post('/settings/currencies', [SettingController::class, 'storeCurrency'])->name('settings.currencies.store');
        Route::post('/settings/currencies/default', [SettingController::class, 'setDefaultCurrency'])->name('settings.currencies.default');
        Route::post('/settings/roles', [SettingController::class, 'storeRole'])->name('settings.roles.store');
        Route::post('/settings/permissions', [SettingController::class, 'storePermission'])->name('settings.permissions.store');
        Route::post('/settings/roles/{role}/permissions', [SettingController::class, 'syncRolePermissions'])->name('settings.roles.permissions');
        Route::post('/settings/receipt', [SettingController::class, 'updateReceipt'])->name('settings.receipt.update');
    });

    // Product search API for POS.
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search');
        Route::get('/api/products/{product}/stock', [ProductController::class, 'stock'])->name('api.products.stock');
    });
});

// Platform operator console. Deliberately outside the subscription gate so
// billing can still be managed when a merchant's subscription has lapsed.
Route::middleware(['auth', 'tenant', 'platform'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/', [PlatformController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [PlatformController::class, 'settings'])->name('settings');
    Route::put('/settings', [PlatformController::class, 'updateSettings'])->name('settings.update');
    Route::get('/tenants', [PlatformController::class, 'tenants'])->name('tenants');
    Route::post('/tenants/{tenant}/status', [PlatformController::class, 'updateTenantStatus'])->name('tenants.status');
    Route::post('/tenants/{tenant}/plan', [PlatformController::class, 'updateTenantPlan'])->name('tenants.plan');
    Route::get('/plans', [PlatformController::class, 'plans'])->name('plans');
    Route::post('/plans', [PlatformController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [PlatformController::class, 'updatePlan'])->name('plans.update');
});

require __DIR__.'/auth.php';
