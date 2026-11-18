@extends('layouts.app')
@section('title', 'Warehouse Transfers')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Warehouse Transfers</h4>
    <a href="{{ route('warehouse_transfers.create') }}" class="btn btn-primary">New Transfer</a>
</div>
<table class="table table-striped bg-white">
    <thead><tr><th>Reference</th><th>From</th><th>To</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    @foreach($transfers as $t)
        <tr>
            <td>{{ $t->reference }}</td>
            <td>{{ $t->fromWarehouse->name ?? '-' }}</td>
            <td>{{ $t->toWarehouse->name ?? '-' }}</td>
            <td>{{ $t->status }}</td>
            <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
            <td><a href="{{ route('warehouse_transfers.show', $t) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $transfers->links() }}
@endsection
