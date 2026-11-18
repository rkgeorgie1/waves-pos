@extends('layouts.app')
@section('title','Add Sale')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-semibold">Add Sale</h5>
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">All Sales</a>
</div>

<form method="POST" action="{{ route('sales.store_direct') }}">
@csrf
<div class="card p-3 mb-3">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-select">
                <option value="">Walk-in Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Location (Warehouse)</label>
            <select name="warehouse_id" class="form-select" required>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select">
                <option value="cash">Cash</option>
                <option value="mpesa">M-Pesa</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="Multiple Pay">Multiple Pay</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Amount Paid</label>
            <input name="paid" type="number" step="0.01" class="form-control" placeholder="Leave blank for full">
        </div>
        <div class="col-md-2">
            <label class="form-label">Shipping Status</label>
            <select name="shipping_status" class="form-select">
                <option value="">-- None --</option>
                <option value="Ordered">Ordered</option>
                <option value="Packed">Packed</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
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
        <div class="col-md-8">
            <label class="form-label">Shipping Details</label>
            <input name="shipping_details" class="form-control" placeholder="Delivery address, rider/courier details...">
        </div>
    </div>
</div>

<div class="card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-semibold">Sale Items</h6>
        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">+ Add Item</button>
    </div>
    <table class="table align-middle mb-0" id="items">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th style="width: 140px;">Qty</th>
                <th style="width: 160px;">Unit Price</th>
                <th style="width: 50px;"></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="card p-3 mb-3">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Sell Note</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Customer-facing or invoice note..."></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Staff Note</label>
            <textarea name="staff_note" class="form-control" rows="2" placeholder="Internal staff note..."></textarea>
        </div>
    </div>
</div>

<button class="btn btn-primary">Save Sale</button>
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
        <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove()">x</button></td>`;
    document.querySelector('#items tbody').appendChild(tr);
}
addRow();
</script>
@endpush
@endsection
