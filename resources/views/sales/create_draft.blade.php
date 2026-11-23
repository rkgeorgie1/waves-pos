@extends('layouts.app')
@section('title','Add Draft')
@section('content')
<h4>Add Draft</h4>
<form method="POST" action="{{ route('sales.drafts.store') }}">
@csrf
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select">
            <option value="">Walk-in</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Warehouse</label>
        <select name="warehouse_id" class="form-select" required>
            @foreach($warehouses as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Discount</label>
        <input name="discount" type="number" step="0.01" class="form-control" value="0">
    </div>
    <div class="col-md-2">
        <label class="form-label">Tax</label>
        <input name="tax" type="number" step="0.01" class="form-control" value="0">
    </div>
</div>
<div class="table-responsive">
<table class="table bg-white" id="items">
    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th></th></tr></thead>
    <tbody></tbody>
</table>
</div>
<div class="mb-3">
    <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">Add Item</button>
</div>
<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="2"></textarea>
</div>
<button class="btn btn-primary">Save Draft</button>
</form>
@push('scripts')
<script>
const products = @json($products);
function addRow() {
    const idx = document.querySelectorAll('#items tbody tr').length;
    const opts = products.map(p => `<option value="${p.id}" data-price="${p.retail_price}">${p.name}</option>`).join('');
    const defaultPrice = products.length ? products[0].retail_price : 0;
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="items[${idx}][product_id]" class="form-select" onchange="this.closest('tr').querySelector('.unit-price').value = this.selectedOptions[0].dataset.price || 0">${opts}</select></td>
        <td><input name="items[${idx}][quantity]" type="number" step="0.0001" class="form-control" value="1" required></td>
        <td><input name="items[${idx}][unit_price]" type="number" step="0.01" class="form-control unit-price" value="${defaultPrice}" required></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">x</button></td>`;
    document.querySelector('#items tbody').appendChild(tr);
}
addRow();
</script>
@endpush
@endsection
