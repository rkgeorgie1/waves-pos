@extends('layouts.app')
@section('title', 'Customer Ledger (AR)')
@section('content')
<h4>Customer Ledger (Accounts Receivable)</h4>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="customer_id" class="form-select"><option value="">All customers</option>@foreach($customers as $c)<option value="{{ $c->id }}" {{ $customerId==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
</form>
<table class="table table-striped bg-white">
    <thead><tr><th>Date</th><th>Customer</th><th>Reference</th><th>Debit</th><th>Credit</th><th>Balance</th><th>Notes</th></tr></thead>
    <tbody>
    @foreach($entries as $e)
        <tr>
            <td>{{ $e->created_at->format('Y-m-d H:i') }}</td>
            <td>{{ $e->customer->name ?? '-' }}</td>
            <td>{{ $e->reference_type }} #{{ $e->reference_id }}</td>
            <td>{{ number_format($e->debit,2) }}</td>
            <td>{{ number_format($e->credit,2) }}</td>
            <td>{{ number_format($e->balance,2) }}</td>
            <td>{{ $e->notes }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $entries->links() }}
@endsection
