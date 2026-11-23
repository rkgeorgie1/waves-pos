@extends('layouts.app')
@section('title', 'Products')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="fw-bold mb-0">Products</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">+ Add Product</a>
    </div>
</div>

<!-- Filters Card -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('products.index') }}" data-auto-filter>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Name, SKU, Barcode..." value="{{ $q ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Brand</label>
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">All Brands</option>
                    @foreach(\App\Models\Brand::orderBy('name')->get() as $b)
                        <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Unit</label>
                <select name="unit_id" class="form-select form-select-sm">
                    <option value="">All Units</option>
                    @foreach(\App\Models\Unit::orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 70px;">Image</th>
                    <th>Product</th>
                    <th>Unit</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Purchase Price</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-end">Current Stock</th>
                    <th>Type</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $idx => $p)
                    <tr>
                        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $idx + 1 }}</td>
                        <td>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; overflow: hidden;">
                                <i class="bi bi-box-seam text-secondary fs-4"></i>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <div class="text-muted small">SKU: {{ $p->sku }} | Barcode: {{ $p->barcode ?? '-' }}</div>
                        </td>
                        <td>{{ $p->unit->name ?? '-' }}</td>
                        <td>{{ $p->category->name ?? '-' }}</td>
                        <td>{{ $p->brand->name ?? '-' }}</td>
                        <td class="text-end">{{ number_format($p->vat_rate ?? 0, 2) }}%</td>
                        <td class="text-end">KSh {{ number_format($p->purchase_price ?? 0, 2) }}</td>
                        <td class="text-end fw-semibold">KSh {{ number_format($p->retail_price ?? 0, 2) }}</td>
                        <td class="text-end {{ $p->stockBalances->sum('quantity') <= ($p->min_stock_level ?? 0) ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($p->stockBalances->sum('quantity'), 2) }}
                        </td>
                        <td>
                            @if($p->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.82rem;">
                                    <li><a class="dropdown-item py-1" href="{{ route('products.show', $p) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item py-1" href="{{ route('products.edit', $p) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Delete this product?')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item py-1 text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $products->links() }}
    </div>
</div>
@endsection
