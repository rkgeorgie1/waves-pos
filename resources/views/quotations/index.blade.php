@extends('layouts.app')
@section('title', 'Quotations')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Quotations</h4>
    <div>
        <a href="{{ route('quotations.export') }}" class="btn btn-success">Download CSV</a>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary">New Quotation</a>
    </div>
</div>
<table class="table table-striped bg-white">
    <thead><tr><th>Reference</th><th>Customer</th><th>Total</th><th>Status</th><th>Valid Until</th><th></th></tr></thead>
    <tbody>
    @foreach($quotations as $q)
        <tr>
            <td>{{ $q->reference }}</td>
            <td>{{ $q->customer->name ?? 'Walk-in' }}</td>
            <td>{{ number_format($q->total, 2) }}</td>
            <td>{{ $q->status }}</td>
            <td>{{ $q->valid_until ?? '-' }}</td>
            <td>
                <a href="{{ route('quotations.show', $q) }}" class="btn btn-sm btn-outline-secondary">View</a>
                @if($q->status !== 'converted')
                    <a href="{{ route('quotations.show', $q) }}" class="btn btn-sm btn-success">Finalize</a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $quotations->links() }}
@endsection
