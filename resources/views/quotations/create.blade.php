@extends('layouts.app')
@section('title','New Quotation')
@section('content')
<h4>New Quotation</h4>
<form method="POST" action="{{ route('quotations.store') }}" id="qform">
@csrf
<div class="row g-3 mb-3">
    <div class="col-md-4"><label>Customer</label><select name="customer_id" class="form-select"><option value="">Walk-in</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Valid Until</label><input name="valid_until" type="date" class="form-control"></div>
    <div class="col-md-4"><label>Discount</label><input name="discount" type="number" step="0.01" class="form-control" value="0"></div>
</div>
<table class="table bg-white" id="items">
    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th></th></tr></thead>
    <tbody></tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">Add Item</button>
<button class="btn btn-primary">Save Quotation</button>
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
