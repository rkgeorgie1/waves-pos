@extends('layouts.app')
@section('title','New Sales Return')
@section('content')
<h4>New Sales Return</h4>
<form method="POST" action="{{ route('sales_returns.store') }}" id="srform">
@csrf
<div class="row g-3 mb-3">
    <div class="col-md-4"><label>Sale (optional)</label><select name="sale_id" class="form-select"><option value="">--</option>@foreach($sales as $s)<option value="{{ $s->id }}" {{ (isset($sale) && $sale?->id==$s->id)?'selected':'' }}>{{ $s->reference }} - {{ $s->customer->name ?? 'Walk-in' }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Customer ID (optional)</label><input name="customer_id" class="form-control" value="{{ $sale->customer_id ?? '' }}"></div>
    <div class="col-md-4"><label>Reason</label><input name="reason" class="form-control"></div>
</div>
<table class="table bg-white" id="items">
    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th></th></tr></thead>
    <tbody></tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">Add Item</button>
<button class="btn btn-primary">Save Return</button>
</form>
@push('scripts')
<script>
const products = @json($products);
function addRow() {
    const idx = document.querySelectorAll('#items tbody tr').length;
    const opts = products.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="items[${idx}][product_id]" class="form-select">${opts}</select></td>
        <td><input name="items[${idx}][quantity]" type="number" step="0.0001" class="form-control" value="1"></td>
        <td><input name="items[${idx}][unit_price]" type="number" step="0.01" class="form-control" value="0"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">x</button></td>`;
    document.querySelector('#items tbody').appendChild(tr);
}
addRow();
</script>
@endpush
@endsection
