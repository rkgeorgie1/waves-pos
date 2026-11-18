@extends('layouts.app')
@section('title', 'Sales Return')
@section('content')
<h4>Sales Return {{ $salesReturn->reference }}</h4>
<p>Sale: {{ $salesReturn->sale->reference ?? '-' }} | Customer: {{ $salesReturn->customer->name ?? '-' }} | Reason: {{ $salesReturn->reason ?? '-' }}</p>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
    <tbody>
    @foreach($salesReturn->items as $it)
        <tr>
            <td>{{ $it->product_name }}</td>
            <td>{{ $it->quantity }}</td>
            <td>{{ number_format($it->unit_price, 2) }}</td>
            <td>{{ number_format($it->total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr><th colspan="3">Total</th><th>{{ number_format($salesReturn->amount, 2) }}</th></tr></tfoot>
</table>
<a href="{{ route('sales_returns.index') }}" class="btn btn-secondary">Back</a>
@endsection
