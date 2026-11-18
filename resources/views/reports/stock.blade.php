@extends('layouts.app')
@section('title','Stock Report')
@section('content')
<h4>Stock Report</h4>
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
    <div class="col-md-6 text-end">
        <span class="badge bg-secondary">Total: {{ $summary['total_products'] }}</span>
        <span class="badge bg-success">In Stock: {{ $summary['in_stock'] }}</span>
        <span class="badge bg-warning text-dark">Low: {{ $summary['low_stock'] }}</span>
        <span class="badge bg-danger">Out: {{ $summary['out_of_stock'] }}</span>
        <span class="badge bg-info text-dark">Value: {{ number_format($summary['stock_value'],2) }}</span>
    </div>
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
