@extends('layouts.app')
@section('title', 'Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Inventory</h4>
</div>

<!-- Filters Card -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('inventory.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Product name / SKU..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Warehouse</label>
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn upos-filter-btn btn-sm px-3">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm px-3">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Stock Adjustment Form -->
<div class="card p-4 mb-3">
    <h6 class="fw-bold mb-3">Stock Adjustment</h6>
    <form method="POST" action="{{ route('inventory.adjust') }}">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Product:*</label>
                <select name="product_id" class="form-select form-select-sm" required>
                    <option value="">Please Select</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Warehouse:*</label>
                <select name="warehouse_id" class="form-select form-select-sm" required>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Type:*</label>
                <select name="type" class="form-select form-select-sm" required>
                    <option value="add">Add Stock</option>
                    <option value="subtract">Subtract Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Quantity:*</label>
                <input name="quantity" type="number" step="0.0001" min="0.0001" class="form-control form-control-sm" placeholder="Qty" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Reason:*</label>
                <input name="reason" class="form-control form-control-sm" placeholder="Reason for adjustment" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Adjust Stock</button>
            </div>
        </div>
    </form>
</div>

<!-- Stock Balances Table -->
<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Warehouse</th>
                    <th class="text-end">Current Stock</th>
                    <th class="text-end">Avg Cost</th>
                    <th class="text-end">Stock Value</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balances as $b)
                    <tr>
                        <td class="fw-semibold">{{ $b->product->name ?? '-' }}</td>
                        <td>{{ $b->product->sku ?? '-' }}</td>
                        <td>{{ $b->warehouse->name ?? '-' }}</td>
                        <td class="text-end {{ $b->quantity <= ($b->product->min_stock_level ?? 0) ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($b->quantity, 2) }}
                        </td>
                        <td class="text-end">KSh {{ number_format($b->avg_cost ?? 0, 2) }}</td>
                        <td class="text-end fw-medium">KSh {{ number_format(($b->quantity ?? 0) * ($b->avg_cost ?? 0), 2) }}</td>
                        <td class="text-center">
                            @if($b->quantity <= 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Out of Stock</span>
                            @elseif($b->quantity <= ($b->product->min_stock_level ?? 0))
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Low Stock</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">In Stock</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No stock balances found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $balances->links() }}
    </div>
</div>
@endsection
