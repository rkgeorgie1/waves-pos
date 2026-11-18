@extends('layouts.app')
@section('title', 'Product ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Product Details</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-primary">Edit Product</a>
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">All Products</a>
    </div>
</div>

<div class="card p-4 mb-3">
    <div class="row g-4 mb-4 pb-3 border-bottom">
        <div class="col-md-3">
            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; overflow: hidden;">
                <i class="bi bi-box-seam text-secondary" style="font-size: 3rem;"></i>
            </div>
        </div>
        <div class="col-md-9">
            <h4 class="fw-bold mb-1">{{ $product->name }}</h4>
            <div class="text-muted small mb-2">SKU: <strong class="text-dark">{{ $product->sku }}</strong> | Barcode: {{ $product->barcode ?? '-' }}</div>
            <div class="text-muted small mb-3">{{ $product->description ?? 'No description available.' }}</div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Category</div>
                    <div>{{ $product->category->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Brand</div>
                    <div>{{ $product->brand->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Unit</div>
                    <div>{{ $product->unit->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Status</div>
                    <div>
                        @if($product->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <h6 class="fw-semibold text-uppercase text-muted mb-3">Pricing & Tax</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted">Purchase Price</td>
                    <td class="text-end fw-medium">KSh {{ number_format($product->purchase_price ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Retail Price</td>
                    <td class="text-end fw-medium">KSh {{ number_format($product->retail_price ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Wholesale Price</td>
                    <td class="text-end fw-medium">KSh {{ number_format($product->wholesale_price ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Contractor Price</td>
                    <td class="text-end fw-medium">KSh {{ number_format($product->contractor_price ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">VAT Rate</td>
                    <td class="text-end fw-medium">{{ number_format($product->vat_rate ?? 0, 2) }}%</td>
                </tr>
                <tr>
                    <td class="text-muted">Min Stock Level</td>
                    <td class="text-end fw-medium">{{ number_format($product->min_stock_level ?? 0) }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <h6 class="fw-semibold text-uppercase text-muted mb-3">Stock Balances by Warehouse</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Warehouse</th>
                            <th class="text-end">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->stockBalances as $bal)
                            <tr>
                                <td>{{ $bal->warehouse->name ?? 'Unknown' }}</td>
                                <td class="text-end {{ $bal->quantity <= ($product->min_stock_level ?? 0) ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($bal->quantity, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-2">No stock records found.</td>
                            </tr>
                        @endforelse
                        <tr class="table-light">
                            <td class="fw-semibold">Total Stock</td>
                            <td class="text-end fw-bold">{{ number_format($product->stockBalances->sum('quantity'), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
