@push('styles')
<style>
    .upos-purchase-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 18px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .purchase-entry-table thead th {
        background: #2dce89;
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 600;
        vertical-align: middle;
        border-color: #26af74;
        padding: 5px 4px;
        line-height: 1.1;
    }
    .purchase-entry-table tbody td {
        vertical-align: middle;
        font-size: 0.75rem;
        padding: 4px;
    }
    .purchase-entry-table .form-control,
    .purchase-entry-table .form-select {
        font-size: 0.75rem;
        padding: 1px 26px 1px 6px;
        height: 26px;
    }
    .purchase-entry-table .product-select {
        background-position: right 4px center;
        background-size: 12px;
    }
</style>
@endpush

@php
    $isEditing = isset($purchase) && $purchase !== null;
    $selectedSupplierId = old('supplier_id', $isEditing ? $purchase->supplier_id : '');
    $selectedSupplier = $suppliers->firstWhere('id', $selectedSupplierId);
    $supplierAddress = old('supplier_address', $selectedSupplier?->address ?? $selectedSupplier?->phone ?? '-');
    $referenceValue = old('reference', $isEditing ? $purchase->reference : '');
    $orderedAtValue = old('ordered_at');

    if ($orderedAtValue === null) {
        $orderedAtValue = $isEditing && $purchase->ordered_at
            ? \Carbon\Carbon::parse($purchase->ordered_at)->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i');
    }

    $selectedStatus = old('status', $isEditing ? $purchase->status : 'received');
    $selectedWarehouseId = old('warehouse_id', $isEditing ? $purchase->warehouse_id : $warehouses->first()?->id);
    $discountTypeValue = old('discount_type', $isEditing ? $purchase->discount_type : 'none');
    $discountAmountValue = old('discount_amount', $isEditing ? $purchase->discount_amount : 0);
    $taxValue = old('tax', $isEditing ? $purchase->tax : 0);
    $shippingValue = old('shipping_charges', $isEditing ? $purchase->shipping_charges : 0);
    $paidValue = old('paid', $isEditing ? $purchase->paid : 0);
    $notesValue = old('notes', $isEditing ? $purchase->notes : '');
    $paymentMethodValue = old('payment_method', $isEditing ? ($paymentMethod ?? 'cash') : 'cash');
    $formItems = $isEditing ? $purchase->items : collect();
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">{{ $isEditing ? 'Edit Purchase '.$purchase->reference : 'Add Purchase' }}</h4>
</div>

<form method="POST" action="{{ $isEditing ? route('purchases.update', $purchase) : route('purchases.store') }}" id="poform">
@csrf
@if($isEditing)
    @method('PUT')
@endif

<!-- Card 1: Purchase Header Info -->
<div class="upos-purchase-card">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Supplier:*</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                <select name="supplier_id" id="supplierSelect" class="form-select" required>
                    <option value="">Please Select</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" data-address="{{ $s->address ?? $s->phone ?? '' }}" {{ (string) $selectedSupplierId === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('suppliers.create') }}" class="btn btn-outline-primary" title="Add Supplier"><i class="bi bi-plus-circle-fill"></i></a>
            </div>
            <div class="mt-2 small text-muted">
                <strong>Address:</strong> <span id="supplierAddress">{{ $supplierAddress }}</span>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold small">Reference No: <i class="bi bi-info-circle text-info" title="Leave empty to autogenerate"></i></label>
            <input name="reference" class="form-control form-control-sm" value="{{ $referenceValue }}" placeholder="Auto-generated if blank">
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold small">Purchase Date:*</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-calendar3"></i></span>
                <input type="datetime-local" name="ordered_at" class="form-control" value="{{ $orderedAtValue }}" required>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold small">Purchase Status:* <i class="bi bi-info-circle text-info" title="Items in Received status add directly to stock"></i></label>
            <select name="status" class="form-select form-select-sm" required>
                <option value="received" {{ $selectedStatus === 'received' ? 'selected' : '' }}>Received</option>
                <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="ordered" {{ $selectedStatus === 'ordered' ? 'selected' : '' }}>Ordered</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold small">Business Location:*</label>
            <select name="warehouse_id" class="form-select form-select-sm" required>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ (string) $selectedWarehouseId === (string) $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Card 2: Product Search & Purchase Items Table -->
<div class="upos-purchase-card">
    <div class="row g-2 align-items-center justify-content-center mb-3">
        <div class="col-md-8 position-relative">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input id="productSearchInput" class="form-control" placeholder="Enter Product name / SKU / Scan bar code" autocomplete="off">
            </div>
            <div id="productSearchResults" class="list-group position-absolute w-100 shadow mt-1" style="max-height: 220px; overflow-y: auto; z-index: 40;"></div>
        </div>
        <div class="col-md-2">
            <a href="{{ route('products.create') }}" class="text-primary text-decoration-none fw-semibold small">
                <i class="bi bi-plus-lg me-1"></i>Add new product
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered purchase-entry-table mb-2" id="items">
            <thead>
                <tr>
                    <th style="width: 36px;">#</th>
                    <th style="min-width: 180px;">Product Name</th>
                    <th style="width: 110px;">Purchase Quantity</th>
                    <th style="width: 120px;">Unit Cost (Before Discount)</th>
                    <th style="width: 95px;">Discount Percent</th>
                    <th style="width: 115px;">Unit Cost (Before Tax)</th>
                    <th style="width: 115px;">Line Total</th>
                    <th style="width: 105px;">Profit Margin %</th>
                    <th style="width: 125px;">Unit Selling Price <small>(Inc. tax)</small></th>
                    <th style="width: 40px;"><i class="bi bi-trash"></i></th>
                </tr>
            </thead>
            <tbody>
                @foreach($formItems as $index => $item)
                    @php
                        $itemSellingPrice = $item->product->retail_price ?? 0;
                        $itemMargin = $item->unit_cost > 0 ? ((($itemSellingPrice - $item->unit_cost) / $item->unit_cost) * 100) : 0;
                    @endphp
                    <tr>
                        <td class="row-index text-center">{{ $index + 1 }}</td>
                        <td>
                            <select name="items[{{ $index }}][product_id]" class="form-select form-select-sm product-select" style="min-width: 180px;" onchange="onProductChange(this)">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-cost="{{ $p->purchase_price ?? 0 }}" data-retail="{{ $p->retail_price ?? 0 }}" {{ (string) $p->id === (string) $item->product_id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku ?? '' }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input name="items[{{ $index }}][quantity]" type="number" step="0.0001" min="0.0001" class="form-control form-control-sm item-qty" value="{{ $item->quantity }}" oninput="recalcRow(this)">
                        </td>
                        <td>
                            <input name="items[{{ $index }}][unit_cost]" type="number" step="0.01" min="0" class="form-control form-control-sm item-cost" value="{{ $item->unit_cost }}" oninput="recalcRow(this)">
                        </td>
                        <td>
                            <input name="items[{{ $index }}][discount_percent]" type="number" step="0.01" min="0" max="100" class="form-control form-control-sm item-disc" value="0" oninput="recalcRow(this)">
                        </td>
                        <td class="text-end item-net-cost fw-medium">{{ number_format($item->unit_cost, 2, '.', '') }}</td>
                        <td class="text-end item-line-total fw-bold">{{ number_format($item->total, 2, '.', '') }}</td>
                        <td>
                            <input type="number" step="0.01" class="form-control form-control-sm item-margin" value="{{ number_format($itemMargin, 2, '.', '') }}" oninput="onMarginChange(this)">
                        </td>
                        <td>
                            <input name="items[{{ $index }}][selling_price]" type="number" step="0.01" min="0" class="form-control form-control-sm item-selling" value="{{ $itemSellingPrice }}" oninput="onSellingChange(this)">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('tr').remove();reindexRows();recalcTotals()"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
            <i class="bi bi-plus-circle me-1"></i>Add Item Row
        </button>
        <div class="text-end small">
            <div><strong>Total Items:</strong> <span id="totalItemsCount">0.00</span></div>
            <div class="fs-6"><strong>Net Total Amount:</strong> KSh <span id="netTotalAmount">0.00</span></div>
        </div>
    </div>
</div>

<!-- Card 3: Discount, Tax, Shipping & Additional Notes -->
<div class="upos-purchase-card">
    <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Discount Type:</label>
            <select name="discount_type" id="discountType" class="form-select form-select-sm" onchange="recalcTotals()">
                <option value="none" {{ $discountTypeValue === 'none' ? 'selected' : '' }}>None</option>
                <option value="fixed" {{ $discountTypeValue === 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="percentage" {{ $discountTypeValue === 'percentage' ? 'selected' : '' }}>Percentage</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Discount Amount:</label>
            <input type="number" step="0.01" name="discount_amount" id="discountAmount" class="form-control form-control-sm" value="{{ $discountAmountValue }}" oninput="recalcTotals()">
        </div>
        <div class="col-md-6 text-md-end">
            <strong>Discount: (-)</strong> KSh <span id="discountComputed">0.00</span>
        </div>
    </div>

    <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Purchase Tax:</label>
            <input type="number" step="0.01" name="tax" id="purchaseTax" class="form-control form-control-sm" value="{{ $taxValue }}" oninput="recalcTotals()">
        </div>
        <div class="col-md-8 text-md-end">
            <strong>Purchase Tax: (+)</strong> KSh <span id="taxComputed">0.00</span>
        </div>
    </div>

    <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">(+) Additional Shipping charges:</label>
            <input type="number" step="0.01" name="shipping_charges" id="shippingCharges" class="form-control form-control-sm" value="{{ $shippingValue }}" oninput="recalcTotals()">
        </div>
        <div class="col-md-8 text-md-end">
            <div class="fs-5 fw-bold">Purchase Total: KSh <span id="grandPurchaseTotal">0.00</span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <label class="form-label fw-semibold small">Additional Notes</label>
            <textarea name="notes" class="form-control form-control-sm" rows="3">{{ $notesValue }}</textarea>
        </div>
    </div>
</div>

<!-- Card 4: Payment -->
<div class="upos-purchase-card">
    <h6 class="fw-bold mb-3">{{ $isEditing ? 'Payment summary' : 'Add payment' }}</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Amount Paid:*</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-cash"></i></span>
                <input type="number" step="0.01" name="paid" id="paidAmount" class="form-control" value="{{ $paidValue }}" oninput="recalcTotals()">
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Payment Method:*</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-wallet2"></i></span>
                <select name="payment_method" class="form-select">
                    <option value="cash" {{ $paymentMethodValue === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mpesa" {{ $paymentMethodValue === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="bank_transfer" {{ $paymentMethodValue === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ $paymentMethodValue === 'cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong>Payment due:</strong> <span class="text-danger fw-bold">KSh <span id="paymentDueDisplay">0.00</span></span>
        </div>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">{{ $isEditing ? 'Update Purchase' : 'Save' }}</button>
    </div>
</div>
</form>

@push('scripts')
<script>
const products = @json($products);

document.getElementById('supplierSelect').addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    document.getElementById('supplierAddress').textContent = (opt && opt.dataset.address) ? opt.dataset.address : '-';
});

function addRow(productObj = null) {
    const tbody = document.querySelector('#items tbody');
    const idx = tbody.querySelectorAll('tr').length;
    const selected = productObj || products[0] || { id: '', name: '', purchase_price: 0, retail_price: 0 };
    const opts = products.map(p =>
        `<option value="${p.id}" data-cost="${p.purchase_price || 0}" data-retail="${p.retail_price || 0}" ${p.id === selected.id ? 'selected' : ''}>${p.name} (${p.sku || ''})</option>`
    ).join('');

    const cost = parseFloat(selected.purchase_price || 0);
    const retail = parseFloat(selected.retail_price || 0);
    const margin = cost > 0 ? (((retail - cost) / cost) * 100).toFixed(2) : '0.00';

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="row-index text-center">${idx + 1}</td>
        <td>
            <select name="items[${idx}][product_id]" class="form-select form-select-sm product-select" style="min-width: 180px;" onchange="onProductChange(this)">
                ${opts}
            </select>
        </td>
        <td>
            <input name="items[${idx}][quantity]" type="number" step="0.0001" min="0.0001" class="form-control form-control-sm item-qty" value="1" oninput="recalcRow(this)">
        </td>
        <td>
            <input name="items[${idx}][unit_cost]" type="number" step="0.01" min="0" class="form-control form-control-sm item-cost" value="${cost.toFixed(2)}" oninput="recalcRow(this)">
        </td>
        <td>
            <input name="items[${idx}][discount_percent]" type="number" step="0.01" min="0" max="100" class="form-control form-control-sm item-disc" value="0" oninput="recalcRow(this)">
        </td>
        <td class="text-end item-net-cost fw-medium">${cost.toFixed(2)}</td>
        <td class="text-end item-line-total fw-bold">${cost.toFixed(2)}</td>
        <td>
            <input type="number" step="0.01" class="form-control form-control-sm item-margin" value="${margin}" oninput="onMarginChange(this)">
        </td>
        <td>
            <input name="items[${idx}][selling_price]" type="number" step="0.01" min="0" class="form-control form-control-sm item-selling" value="${retail.toFixed(2)}" oninput="onSellingChange(this)">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('tr').remove();reindexRows();recalcTotals()"><i class="bi bi-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    reindexRows();
    recalcTotals();
}

function onProductChange(selectEl) {
    const tr = selectEl.closest('tr');
    const opt = selectEl.selectedOptions[0];
    const cost = parseFloat(opt?.dataset.cost || 0);
    const retail = parseFloat(opt?.dataset.retail || 0);
    tr.querySelector('.item-cost').value = cost.toFixed(2);
    tr.querySelector('.item-selling').value = retail.toFixed(2);
    recalcRow(selectEl);
}

function recalcRow(el) {
    const tr = el.closest('tr');
    const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
    const cost = parseFloat(tr.querySelector('.item-cost').value) || 0;
    const disc = parseFloat(tr.querySelector('.item-disc').value) || 0;
    const netCost = cost * (1 - disc / 100);
    const lineTotal = qty * netCost;
    const selling = parseFloat(tr.querySelector('.item-selling').value) || 0;

    tr.querySelector('.item-net-cost').textContent = netCost.toFixed(2);
    tr.querySelector('.item-line-total').textContent = lineTotal.toFixed(2);
    tr.querySelector('.item-margin').value = netCost > 0 ? (((selling - netCost) / netCost) * 100).toFixed(2) : '0.00';
    recalcTotals();
}

function onMarginChange(el) {
    const tr = el.closest('tr');
    const netCost = parseFloat(tr.querySelector('.item-net-cost').textContent) || 0;
    const margin = parseFloat(el.value) || 0;
    tr.querySelector('.item-selling').value = (netCost * (1 + margin / 100)).toFixed(2);
}

function onSellingChange(el) {
    const tr = el.closest('tr');
    const netCost = parseFloat(tr.querySelector('.item-net-cost').textContent) || 0;
    const selling = parseFloat(el.value) || 0;
    tr.querySelector('.item-margin').value = netCost > 0 ? (((selling - netCost) / netCost) * 100).toFixed(2) : '0.00';
}

function reindexRows() {
    document.querySelectorAll('#items tbody tr').forEach((tr, i) => {
        tr.querySelector('.row-index').textContent = i + 1;
        tr.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
        });
    });
}

function recalcTotals() {
    let totalQty = 0;
    let netTotal = 0;
    document.querySelectorAll('#items tbody tr').forEach(tr => {
        totalQty += parseFloat(tr.querySelector('.item-qty').value) || 0;
        netTotal += parseFloat(tr.querySelector('.item-line-total').textContent) || 0;
    });

    document.getElementById('totalItemsCount').textContent = totalQty.toFixed(2);
    document.getElementById('netTotalAmount').textContent = netTotal.toFixed(2);

    const discType = document.getElementById('discountType').value;
    const discVal = parseFloat(document.getElementById('discountAmount').value) || 0;
    const discount = discType === 'percentage' ? (netTotal * (discVal / 100)) : (discType === 'fixed' ? discVal : 0);
    const tax = parseFloat(document.getElementById('purchaseTax').value) || 0;
    const shipping = parseFloat(document.getElementById('shippingCharges').value) || 0;
    const grand = Math.max(0, netTotal - discount + tax + shipping);
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;

    document.getElementById('discountComputed').textContent = discount.toFixed(2);
    document.getElementById('taxComputed').textContent = tax.toFixed(2);
    document.getElementById('grandPurchaseTotal').textContent = grand.toFixed(2);
    document.getElementById('paymentDueDisplay').textContent = Math.max(0, grand - paid).toFixed(2);
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

if (! document.querySelector('#items tbody tr')) {
    addRow();
} else {
    reindexRows();
    recalcTotals();
}
</script>
@endpush
