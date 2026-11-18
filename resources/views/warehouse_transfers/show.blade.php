@extends('layouts.app')
@section('title', 'Warehouse Transfer')
@section('content')
<h4>Transfer {{ $warehouseTransfer->reference }}</h4>
<p>From: {{ $warehouseTransfer->fromWarehouse->name ?? '-' }} → To: {{ $warehouseTransfer->toWarehouse->name ?? '-' }} | Status: {{ $warehouseTransfer->status }}</p>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Qty</th></tr></thead>
    <tbody>
    @foreach($warehouseTransfer->items as $it)
        <tr><td>{{ $it->product_name }}</td><td>{{ $it->quantity }}</td></tr>
    @endforeach
    </tbody>
</table>
@if($warehouseTransfer->status === 'pending')
<form method="POST" action="{{ route('warehouse_transfers.complete', $warehouseTransfer) }}">@csrf<button class="btn btn-primary">Complete Transfer</button></form>
@endif
<a href="{{ route('warehouse_transfers.delivery_note', $warehouseTransfer) }}" target="_blank" class="btn btn-outline-primary">Delivery Note</a>
<a href="{{ route('warehouse_transfers.index') }}" class="btn btn-secondary">Back</a>
@endsection
