@extends('layouts.app')
@section('title', 'Add Purchase Return')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Add Purchase Return</h4>
    <a href="{{ route('purchase_returns.index') }}" class="btn btn-sm btn-outline-secondary">All Purchase Returns</a>
</div>

<form method="POST" action="{{ route('purchase_returns.store') }}" id="prForm">
@csrf
<div class="card p-4 mb-3">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Supplier:</label>
            <select name="supplier_id" class="form-select form-select-sm">
                <option value="">Please Select</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Purchase Reference:</label>
            <select name="purchase_id" class="form-select form-select-sm">
                <option value="">-- None --</option>
                @foreach($purchases as $p)
                    <option value="{{ $p->id }}">{{ $p->reference }} ({{ $p->supplier->name ?? '-' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Warehouse / Location:*</label>
            <select name="warehouse_id" class="form-select form-select-sm" required>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" @selected($w->is_default)>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label fw-semibold small">Reason:</label>
            <input name="reason" class="form-control form-control-sm" placeholder="Reason for return...">
        </div>
    </div>
</div>

<div class="card p-4 mb-3">
    <div class="row g-2 align-items-center justify-content-center mb-3">
        <div class="col-md-8 position-relative">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input id="productSearchInput" class="form-control" placeholder="Enter Product name / SKU / Scan bar code" autocomplete="off">
            </div>
            <div id="productSearchResults" class="list-group position-absolute w-100 shadow mt-1" style="max-height: 220px; overflow-y: auto; z-index: 40;"></div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-2" id="items">
            <thead class="table-light">
                <tr>
                    <th style="width: 36px;">#</th>
                    <th>Product Name</th>
                    <th style="width: 120px;">Quantity</th>
                    <th style="width: 135px;">Unit Cost</th>
                    <th style="width: 125px;">Line Total</th>
                    <th style="width: 40px;"><i class="bi bi-trash"></i></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
            <i class="bi bi-plus-circle me-1"></i>Add Item Row
        </button>
        <div class="text-end small">
            <div class="fs-6"><strong>Total Return Amount:</strong> KSh <span id="totalReturnAmount">0.00</span></div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary px-4 fw-semibold">Save Purchase Return</button>
</form>
@endsection

@push('scripts')
<script>
const products = @json($products);

function addRow(productObj = null) {
    const tbody = document.querySelector('#items tbody');
    const idx = tbody.querySelectorAll('tr').length;
    const selected = productObj || products[0] || { id: '', name: '', purchase_price: 0 };
    const opts = products.map(p =>
        `<option value="${p.id}" data-cost="${p.purchase_price || 0}" ${p.id === selected.id ? 'selected' : ''}>${p.name} (${p.sku || ''})</option>`
    ).join('');

    const cost = parseFloat(selected.purchase_price || 0);

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="row-index text-center">${idx + 1}</td>
        <td>
            <select name="items[${idx}][product_id]" class="form-select form-select-sm product-select" onchange="onProductChange(this)">
                ${opts}
            </select>
        </td>
        <td>
            <input name="items[${idx}][quantity]" type="number" step="0.0001" min="0.0001" class="form-control form-control-sm item-qty" value="1" oninput="recalcRow(this)">
        </td>
        <td>
            <input name="items[${idx}][unit_cost]" type="number" step="0.01" min="0" class="form-control form-control-sm item-cost" value="${cost.toFixed(2)}" oninput="recalcRow(this)">
        </td>
        <td class="text-end item-line-total fw-bold">${cost.toFixed(2)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('tr').remove();reindexRows();recalcTotals()"><i class="bi bi-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    recalcTotals();
}

function onProductChange(selectEl) {
    const tr = selectEl.closest('tr');
    const opt = selectEl.selectedOptions[0];
    const cost = parseFloat(opt?.dataset.cost || 0);
    tr.querySelector('.item-cost').value = cost.toFixed(2);
    recalcRow(selectEl);
}

function recalcRow(el) {
    const tr = el.closest('tr');
    const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
    const cost = parseFloat(tr.querySelector('.item-cost').value) || 0;
    const lineTotal = qty * cost;
    tr.querySelector('.item-line-total').textContent = lineTotal.toFixed(2);
    recalcTotals();
}

function reindexRows() {
    document.querySelectorAll('#items tbody tr').forEach((tr, i) => {
        tr.querySelector('.row-index').textContent = i + 1;
    });
}

function recalcTotals() {
    let total = 0;
    document.querySelectorAll('#items tbody tr').forEach(tr => {
        total += parseFloat(tr.querySelector('.item-line-total').textContent) || 0;
    });
    document.getElementById('totalReturnAmount').textContent = total.toFixed(2);
}

document.getElementById('productSearchInput').addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const resEl = document.getElementById('productSearchResults');
    resEl.innerHTML = '';
    if (q.length < 1) return;

    const matches = products.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.sku && p.sku.toLowerCase().includes(q)) ||
        (p.barcode && p.barcode.toLowerCase().includes(q))
    ).slice(0, 12);

    matches.forEach(p => {
        const a = document.createElement('a');
        a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-1 small';
        a.innerHTML = `<span>${p.name} (${p.sku || ''})</span><strong>Cost: KSh ${parseFloat(p.purchase_price || 0).toFixed(2)}</strong>`;
        a.onclick = () => {
            addRow(p);
            this.value = '';
            resEl.innerHTML = '';
        };
        resEl.appendChild(a);
    });
});

addRow();
</script>
@endpush
