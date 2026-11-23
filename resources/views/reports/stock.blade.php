@extends('layouts.app')
@section('title','Stock Report')
@section('content')
<style>
    .stock-report-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 24px;
        margin-bottom: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: linear-gradient(120deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    }
    .stock-report-brand {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }
    .stock-report-logo-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 184px;
        height: 72px;
        padding: 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
    }
    .stock-report-logo {
        display: block;
        max-width: 100%;
        max-height: 54px;
        height: auto;
        object-fit: contain;
    }
    .stock-report-brand-fallback {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
        color: #1d4ed8;
    }
    .stock-report-titles h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #111827;
    }
    .stock-report-titles p {
        margin: 3px 0 0;
        font-size: 0.78rem;
        color: #6b7280;
    }
    .stock-report-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .stock-stat {
        min-width: 94px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }
    .stock-stat .label {
        display: block;
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #6b7280;
    }
    .stock-stat .value {
        display: block;
        margin-top: 2px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
    }
    .stock-stat.total .value { color: #3b82f6; }
    .stock-stat.in .value { color: #059669; }
    .stock-stat.low .value { color: #d97706; }
    .stock-stat.out .value { color: #dc2626; }
    @media (max-width: 767.98px) {
        .stock-report-header {
            align-items: stretch;
            padding: 16px;
        }
        .stock-report-brand {
            gap: 12px;
        }
        .stock-report-logo-wrap {
            width: 136px;
            height: 60px;
            padding: 6px 10px;
        }
        .stock-report-logo {
            max-height: 42px;
        }
        .stock-report-titles h4 {
            font-size: 1.05rem;
        }
        .stock-report-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .stock-stat {
            min-width: 0;
        }
    }
</style>

<div class="stock-report-header">
    <div class="stock-report-brand">
        <div class="stock-report-logo-wrap">
            @if($logoUrl !== '')
                <img src="{{ $logoUrl }}" alt="{{ $businessName }} logo" class="stock-report-logo">
            @else
                <span class="stock-report-brand-fallback">{{ $businessName }}</span>
            @endif
        </div>
        <div class="stock-report-titles">
            <h4>Stock Report</h4>
            <p>Inventory levels by warehouse &middot; Generated {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
    <div class="stock-report-stats">
        <div class="stock-stat total">
            <span class="label">Total</span>
            <span class="value">{{ number_format($summary['total_products']) }}</span>
        </div>
        <div class="stock-stat in">
            <span class="label">In Stock</span>
            <span class="value">{{ number_format($summary['in_stock']) }}</span>
        </div>
        <div class="stock-stat low">
            <span class="label">Low</span>
            <span class="value">{{ number_format($summary['low_stock']) }}</span>
        </div>
        <div class="stock-stat out">
            <span class="label">Out</span>
            <span class="value">{{ number_format($summary['out_of_stock']) }}</span>
        </div>
        <div class="stock-stat">
            <span class="label">Value</span>
            <span class="value">{{ number_format($summary['stock_value'], 2) }}</span>
        </div>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="warehouse_id" class="form-select"><option value="">All Warehouses</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" {{ $warehouseId==$w->id?'selected':'' }}>{{ $w->name }}</option>@endforeach</select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="all" {{ $status=='all'?'selected':'' }}>All Stock</option>
            <option value="in_stock" {{ $status=='in_stock'?'selected':'' }}>Goods In Stock</option>
            <option value="out_of_stock" {{ $status=='out_of_stock'?'selected':'' }}>Goods Out of Stock</option>
            <option value="low_stock" {{ $status=='low_stock'?'selected':'' }}>Low Stock</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
</form>
<div class="mb-2">
    <a href="{{ route('reports.export.stock') }}?warehouse_id={{ $warehouseId }}&status={{ $status }}&format=csv" class="btn btn-sm btn-success">CSV</a>
    <a href="{{ route('reports.export.stock') }}?warehouse_id={{ $warehouseId }}&status={{ $status }}&format=xlsx" class="btn btn-sm btn-primary">Excel</a>
    <a href="{{ route('reports.export.stock') }}?warehouse_id={{ $warehouseId }}&status={{ $status }}&format=pdf" class="btn btn-sm btn-danger">PDF</a>
    <a href="{{ route('reports.export.stock') }}?warehouse_id={{ $warehouseId }}&status={{ $status }}&format=doc" class="btn btn-sm btn-secondary">Word</a>
</div>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Warehouse</th><th>Qty</th><th>Min</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($balances as $b)
        <tr>
            <td>{{ $b->product->name ?? '-' }}</td>
            <td>{{ $b->warehouse->name ?? '-' }}</td>
            <td>{{ number_format($b->quantity, 0) }}</td>
            <td>{{ $b->product->min_stock_level ?? 0 }}</td>
            <td>
                @if($b->quantity <= 0) <span class="badge bg-danger">Out</span>
                @elseif($b->product && $b->quantity <= $b->product->min_stock_level) <span class="badge bg-warning text-dark">Low</span>
                @else <span class="badge bg-success">OK</span> @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
@endsection
