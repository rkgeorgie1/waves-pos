@extends('layouts.app')
@section('title', 'Sales Returns')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Sales Returns</h4>
    <div>
        <a href="{{ route('sales_returns.export') }}" class="btn btn-success">Download CSV</a>
        <a href="{{ route('sales_returns.create') }}" class="btn btn-primary">New Return</a>
    </div>
</div>
<table class="table table-striped bg-white">
    <thead><tr><th>Reference</th><th>Sale</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    @foreach($returns as $r)
        <tr>
            <td>{{ $r->reference }}</td>
            <td>{{ $r->sale->reference ?? '-' }}</td>
            <td>{{ $r->customer->name ?? '-' }}</td>
            <td>{{ number_format($r->amount, 2) }}</td>
            <td>{{ $r->status }}</td>
            <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
            <td><a href="{{ route('sales_returns.show', $r) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $returns->links() }}
@endsection
