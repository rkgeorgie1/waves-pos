@extends('layouts.app')
@section('title', 'Home - Waves POS')

@push('styles')
<style>
    .upos-home-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }
    .upos-home-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    .upos-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .circle-sky { background: #e0f2fe; color: #0284c7; }
    .circle-emerald { background: #d1fae5; color: #059669; }
    .circle-amber { background: #fef3c7; color: #d97706; }
    .circle-red { background: #fee2e2; color: #dc2626; }
    .upos-metric-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 2px;
    }
    .upos-metric-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        font-variant-numeric: tabular-nums;
    }
    .upos-section-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    .upos-section-header {
        padding: 14px 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .upos-section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .upos-filter-btn {
        background: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 7px 14px;
        font-size: 0.82rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<!-- Top Welcome & Date Filter Row -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <h4 class="fw-bold mb-0" style="color: #1e1e1e; font-size: 1.45rem;">
        Welcome {{ auth()->user()->name ?? 'Admin' }},
    </h4>

    <div class="dropdown">
        <button class="upos-filter-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-calendar3 me-1"></i>
            Filter by date:
            @switch($period)
                @case('today') Today @break
                @case('yesterday') Yesterday @break
                @case('7days') Last 7 Days @break
                @case('year') This Financial Year @break
                @default This Month
            @endswitch
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
            <li><a class="dropdown-item {{ $period === 'today' ? 'active' : '' }}" href="{{ route('dashboard', ['period' => 'today']) }}">Today</a></li>
            <li><a class="dropdown-item {{ $period === 'yesterday' ? 'active' : '' }}" href="{{ route('dashboard', ['period' => 'yesterday']) }}">Yesterday</a></li>
            <li><a class="dropdown-item {{ $period === '7days' ? 'active' : '' }}" href="{{ route('dashboard', ['period' => '7days']) }}">Last 7 Days</a></li>
            <li><a class="dropdown-item {{ $period === 'month' ? 'active' : '' }}" href="{{ route('dashboard', ['period' => 'month']) }}">This Month</a></li>
            <li><a class="dropdown-item {{ $period === 'year' ? 'active' : '' }}" href="{{ route('dashboard', ['period' => 'year']) }}">This Financial Year</a></li>
        </ul>
    </div>
</div>

<!-- Row 1: 4 Sales & Profit KPI Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-sky">
                <i class="bi bi-cart3"></i>
            </div>
            <div>
                <div class="upos-metric-label">Total Sales</div>
                <div class="upos-metric-value">KSh {{ number_format($todaySales, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-emerald">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <div class="upos-metric-label">Net <i class="bi bi-info-circle text-primary ms-1" title="Total Sales - Sell Return - Expense"></i></div>
                <div class="upos-metric-value">KSh {{ number_format($netAmount, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-amber">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="upos-metric-label">Invoice due</div>
                <div class="upos-metric-value">KSh {{ number_format($invoiceDue, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-red">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div>
                <div class="upos-metric-label">Total Sell Return</div>
                <div class="upos-metric-value">KSh {{ number_format($totalSellReturn, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: 4 Purchases & Expense KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-sky">
                <i class="bi bi-download"></i>
            </div>
            <div>
                <div class="upos-metric-label">Total purchase</div>
                <div class="upos-metric-value">KSh {{ number_format($monthPurchases, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-amber">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="upos-metric-label">Purchase due</div>
                <div class="upos-metric-value">KSh {{ number_format($purchaseDue, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-red">
                <i class="bi bi-arrow-return-left"></i>
            </div>
            <div>
                <div class="upos-metric-label">Total Purchase Return</div>
                <div class="upos-metric-value">KSh {{ number_format($totalPurchaseReturn, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="upos-home-card d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-red">
                <i class="bi bi-dash-circle"></i>
            </div>
            <div>
                <div class="upos-metric-label">Expense</div>
                <div class="upos-metric-value">KSh {{ number_format($monthExpenses, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Sales Last 30 Days Chart -->
<div class="upos-section-card mb-4">
    <div class="upos-section-header">
        <h6 class="upos-section-title">
            <i class="bi bi-bar-chart-line text-primary"></i> Sales Last 30 Days
        </h6>
    </div>
    <div class="p-4">
        <div style="height: 280px;">
            <canvas id="sales30DaysChart"></canvas>
        </div>
    </div>
</div>

<!-- Row 4: Sales Current Financial Year Chart -->
<div class="upos-section-card mb-4">
    <div class="upos-section-header">
        <h6 class="upos-section-title">
            <i class="bi bi-graph-up text-primary"></i> Sales Current Financial Year
        </h6>
    </div>
    <div class="p-4">
        <div style="height: 260px;">
            <canvas id="salesFyChart"></canvas>
        </div>
    </div>
</div>

<!-- Row 5: Payment Dues & Stock Alert Tables (2x2 Grid) -->
<div class="row g-4">
    <!-- Sales Payment Due -->
    <div class="col-lg-6">
        <div class="upos-section-card h-100">
            <div class="upos-section-header">
                <h6 class="upos-section-title">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Sales Payment Due
                </h6>
                <a href="{{ route('sales.index') }}" class="small text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Customer</th>
                            <th>Invoice No.</th>
                            <th class="text-end">Due Amount</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesPaymentDues as $sd)
                            <tr>
                                <td class="ps-3">{{ $sd->customer->name ?? 'Walk-In Customer' }}</td>
                                <td class="fw-semibold"><a href="{{ route('sales.show', $sd) }}" class="text-decoration-none">{{ $sd->reference }}</a></td>
                                <td class="text-end fw-semibold text-danger">KSh {{ number_format(max(0, $sd->total - $sd->paid), 2) }}</td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('sales.show', $sd) }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sales payment dues.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Purchase Payment Due -->
    <div class="col-lg-6">
        <div class="upos-section-card h-100">
            <div class="upos-section-header">
                <h6 class="upos-section-title">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Purchase Payment Due
                </h6>
                <a href="{{ route('purchases.index') }}" class="small text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Supplier</th>
                            <th>Reference No.</th>
                            <th class="text-end">Due Amount</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchasePaymentDues as $pd)
                            <tr>
                                <td class="ps-3">{{ $pd->supplier->name ?? '-' }}</td>
                                <td class="fw-semibold"><a href="{{ route('purchases.show', $pd) }}" class="text-decoration-none">{{ $pd->reference }}</a></td>
                                <td class="text-end fw-semibold text-danger">KSh {{ number_format(max(0, $pd->total - $pd->paid), 2) }}</td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('purchases.show', $pd) }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No purchase payment dues.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Product Stock Alert -->
    <div class="col-lg-6">
        <div class="upos-section-card h-100">
            <div class="upos-section-header">
                <h6 class="upos-section-title">
                    <i class="bi bi-clock-history text-danger"></i> Product Stock Alert
                </h6>
                <a href="{{ route('inventory.index') }}" class="small text-decoration-none">Inventory</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Product</th>
                            <th>Location</th>
                            <th class="pe-3 text-end">Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockItems as $item)
                            <tr>
                                <td class="ps-3">{{ $item->name }} ({{ $item->sku }})</td>
                                <td>{{ $item->warehouse_name ?? 'Main Warehouse' }}</td>
                                <td class="pe-3 text-end text-danger fw-bold">{{ number_format($item->quantity) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No products below alert quantity.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Shipments / Recent Sales -->
    <div class="col-lg-6">
        <div class="upos-section-card h-100">
            <div class="upos-section-header">
                <h6 class="upos-section-title">
                    <i class="bi bi-truck text-info"></i> Recent Sales &amp; Shipments
                </h6>
                <a href="{{ route('sales.shipments') }}" class="small text-decoration-none">Shipments</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Invoice No.</th>
                            <th>Customer name</th>
                            <th>Shipping Status</th>
                            <th class="pe-3 text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $s)
                            <tr>
                                <td class="ps-3 small">{{ $s->created_at->format('d-m-Y H:i') }}</td>
                                <td class="fw-semibold"><a href="{{ route('sales.show', $s) }}" class="text-decoration-none">{{ $s->reference }}</a></td>
                                <td>{{ $s->customer->name ?? 'Walk-In' }}</td>
                                <td>
                                    @if($s->shipping_status)
                                        <span class="badge bg-info-subtle text-info-emphasis">{{ $s->shipping_status }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-end fw-semibold">KSh {{ number_format($s->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No sales recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels30 = @json($chartLabels);
    const sales30 = @json($chartSalesData);

    const ctx30 = document.getElementById('sales30DaysChart');
    if (ctx30) {
        new Chart(ctx30, {
            type: 'line',
            data: {
                labels: labels30,
                datasets: [{
                    label: 'Total Sales (KSh)',
                    data: sales30,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const fyLabels = @json($fyLabels);
    const fySales = @json($fySalesData);

    const ctxFy = document.getElementById('salesFyChart');
    if (ctxFy) {
        new Chart(ctxFy, {
            type: 'line',
            data: {
                labels: fyLabels,
                datasets: [{
                    label: 'Monthly Sales (KSh)',
                    data: fySales,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
