@extends('layouts.app')
@section('title', 'Product Batches')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Product Batches (Expiry Tracking)</h4>
    <a href="{{ route('product_batches.create') }}" class="btn btn-primary">New Batch</a>
</div>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Warehouse</th><th>Batch</th><th>Expiry</th><th>Qty</th><th>Cost</th></tr></thead>
    <tbody>
    @foreach($batches as $b)
        <tr>
            <td>{{ $b->product->name ?? '-' }}</td>
            <td>{{ $b->warehouse->name ?? '-' }}</td>
            <td>{{ $b->batch_number }}</td>
            <td>{{ $b->expiry_date ?? '-' }}</td>
            <td>{{ $b->quantity }}</td>
            <td>{{ number_format($b->cost, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $batches->links() }}
@endsection
