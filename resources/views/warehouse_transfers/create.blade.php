@extends('layouts.app')
@section('title','New Transfer')
@section('content')
<h4>New Warehouse Transfer</h4>
<form method="POST" action="{{ route('warehouse_transfers.store') }}" id="tf">
@csrf
<div class="row g-3 mb-3">
    <div class="col-md-4"><label>From Warehouse</label><select name="from_warehouse_id" class="form-select">@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>To Warehouse</label><select name="to_warehouse_id" class="form-select">@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Notes</label><input name="notes" class="form-control"></div>
</div>
<table class="table bg-white" id="items">
    <thead><tr><th>Product</th><th>Qty</th><th></th></tr></thead>
    <tbody></tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">Add Item</button>
<button class="btn btn-primary">Save Transfer</button>
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
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">x</button></td>`;
    document.querySelector('#items tbody').appendChild(tr);
}
addRow();
</script>
@endpush
@endsection
