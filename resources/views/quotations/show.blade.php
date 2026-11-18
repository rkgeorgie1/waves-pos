@extends('layouts.app')
@section('title', 'Quotation')
@section('content')
<h4>Quotation {{ $quotation->reference }}</h4>
<p>Customer: {{ $quotation->customer->name ?? 'Walk-in' }} | Status: {{ $quotation->status }} | Valid until: {{ $quotation->valid_until ?? '-' }}</p>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
    <tbody>
    @foreach($quotation->items as $it)
        <tr>
            <td>{{ $it->product_name }}</td>
            <td>{{ $it->quantity }}</td>
            <td>{{ number_format($it->unit_price, 2) }}</td>
            <td>{{ number_format($it->total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr><th colspan="3">Total</th><th>{{ number_format($quotation->total, 2) }}</th></tr></tfoot>
</table>
@if($quotation->status !== 'converted')
<form method="POST" action="{{ route('quotations.convert', $quotation) }}" class="row g-2 align-items-end">
@csrf
    <div class="col-md-3">
        <label class="form-label">Paid Amount</label>
        <input name="paid" type="number" step="0.01" class="form-control" value="{{ $quotation->total }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-select">
            <option value="cash">Cash</option>
            <option value="mpesa">M-Pesa</option>
            <option value="bank">Bank</option>
            <option value="card">Card</option>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary">Finalize (Review Payment)</button>
    </div>
</form>
@endif
<a href="{{ route('quotations.index') }}" class="btn btn-secondary">Back</a>
@endsection
