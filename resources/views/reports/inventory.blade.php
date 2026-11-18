@extends('layouts.app')
@section('title','Inventory Report')
@section('content')
<h4>Inventory Report</h4>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Warehouse</th><th>Qty</th><th>Min Level</th><th>Value</th></tr></thead>
    <tbody>
    @foreach($balances as $b)
        <tr>
            <td>{{ $b->product->name ?? '-' }}</td>
            <td>{{ $b->warehouse->name ?? '-' }}</td>
            <td>{{ $b->quantity }}</td>
            <td>{{ $b->product->min_stock_level ?? 0 }}</td>
            <td>{{ number_format($b->quantity * ($b->product->purchase_price ?? 0),2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<a href="{{ route('reports.export.inventory') }}?format=csv" class="btn btn-success">CSV</a>
<a href="{{ route('reports.export.inventory') }}?format=xlsx" class="btn btn-primary">Excel</a>
<a href="{{ route('reports.export.inventory') }}?format=pdf" class="btn btn-danger">PDF</a>
<a href="{{ route('reports.export.inventory') }}?format=doc" class="btn btn-secondary">Word</a>
<a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
@endsection
