@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Reports</h4>
</div>

<!-- Reports Grid -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="{{ route('reports.sales') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-sky" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Sales Report</div>
                <div class="small text-muted">Detailed sales by date</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('reports.stock') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-emerald" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Stock Report</div>
                <div class="small text-muted">Stock levels & status</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('reports.inventory') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-emerald" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-box-seam"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Inventory Report</div>
                <div class="small text-muted">Stock levels & valuation</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('reports.profit') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-amber" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Profit &amp; Loss</div>
                <div class="small text-muted">Revenue, cost &amp; expenses</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('reports.export.sales') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-red" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-download"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Export Sales CSV</div>
                <div class="small text-muted">Download sales data</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('reports.export.inventory') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-sky" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-download"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Export Inventory CSV</div>
                <div class="small text-muted">Download stock data</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('reports.export.profit') }}" class="card p-3 text-decoration-none d-flex align-items-center gap-3 h-100">
            <div class="upos-icon-circle circle-amber" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-download"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">Export Profit CSV</div>
                <div class="small text-muted">Download P&L data</div>
            </div>
        </a>
    </div>
</div>

<!-- Daily Sales & Top Products -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Daily Sales (Last 14 Days)</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Invoices</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailySales as $d)
                            <tr>
                                <td>{{ $d->d }}</td>
                                <td class="text-center">{{ $d->c }}</td>
                                <td class="text-end fw-medium">KSh {{ number_format($d->t, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No sales data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Top Products by Revenue</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty Sold</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $p)
                            <tr>
                                <td>{{ $p->product_name }}</td>
                                <td class="text-center">{{ number_format($p->qty) }}</td>
                                <td class="text-end fw-medium">KSh {{ number_format($p->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No product sales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
