@extends('layouts.app')
@section('title', 'Supplier Ledger (AP)')
@section('content')
<h4>Supplier Ledger (Accounts Payable)</h4>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="supplier_id" class="form-select"><option value="">All suppliers</option>@foreach($suppliers as $s)<option value="{{ $s->id }}" {{ $supplierId==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
</form>
<table class="table table-striped bg-white">
    <thead><tr><th>Date</th><th>Supplier</th><th>Reference</th><th>Debit</th><th>Credit</th><th>Balance</th><th>Notes</th></tr></thead>
    <tbody>
    @foreach($entries as $e)
        <tr>
            <td>{{ $e->created_at->format('Y-m-d H:i') }}</td>
            <td>{{ $e->supplier->name ?? '-' }}</td>
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
