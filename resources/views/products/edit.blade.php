@extends('layouts.app')
@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Edit Product</h4>
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">All Products</a>
</div>

<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-3">
    <!-- Card 1: Product Information -->
    <div class="col-12">
        <div class="card p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Product Name:*</label>
                    <input name="name" class="form-control form-control-sm" value="{{ $product->name }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">SKU:</label>
                    <input name="sku" class="form-control form-control-sm" value="{{ $product->sku }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Barcode:</label>
                    <input name="barcode" class="form-control form-control-sm" value="{{ $product->barcode }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Default Selling Price Tax:</label>
                    <select name="vat_category" class="form-select form-select-sm">
                        <option value="inclusive" @selected($product->vat_category === 'inclusive')>Inclusive of Tax</option>
                        <option value="exclusive" @selected($product->vat_category === 'exclusive')>Exclusive of Tax</option>
                        <option value="no_tax" @selected($product->vat_category === 'no_tax')>No Tax</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Category:</label>
                    <div class="input-group input-group-sm">
                        <select name="category_id" class="form-select">
                            <option value="">Please Select</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected($product->category_id == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('categories.create') }}" class="btn btn-outline-primary" title="Add Category"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Brand:</label>
                    <div class="input-group input-group-sm">
                        <select name="brand_id" class="form-select">
                            <option value="">Please Select</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" @selected($product->brand_id == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('brands.create') }}" class="btn btn-outline-primary" title="Add Brand"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Unit:</label>
                    <div class="input-group input-group-sm">
                        <select name="unit_id" class="form-select">
                            <option value="">Please Select</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" @selected($product->unit_id == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('units.create') }}" class="btn btn-outline-primary" title="Add Unit"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Tax:</label>
                    <select name="vat_rate" class="form-select form-select-sm">
                        <option value="0" @selected($product->vat_rate == 0)>None</option>
                        <option value="16" @selected($product->vat_rate == 16)>VAT 16%</option>
                        <option value="8" @selected($product->vat_rate == 8)>VAT 8%</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Alert Quantity:</label>
                    <input type="number" name="min_stock_level" class="form-control form-control-sm" value="{{ $product->min_stock_level }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Item Quantity:</label>
                    <input type="number" step="0.0001" min="0" name="quantity" class="form-control form-control-sm" value="{{ rtrim(rtrim(number_format($product->stockBalances->sum('quantity'), 4, '.', ''), '0'), '.') ?: 0 }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Warehouse / Location:</label>
                    <select name="warehouse_id" class="form-select form-select-sm">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected($w->is_default)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Default Supplier:</label>
                    <div class="input-group input-group-sm">
                        <select name="default_supplier_id" class="form-select">
                            <option value="">Please Select</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected($product->default_supplier_id == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('suppliers.create') }}" class="btn btn-outline-primary" title="Add Supplier"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold small">Description:</label>
                    <textarea name="description" class="form-control form-control-sm" rows="2">{{ $product->description }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Pricing -->
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3">Pricing</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Purchase Price:</label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control form-control-sm" value="{{ $product->purchase_price }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Retail Price:</label>
                    <input type="number" step="0.01" name="retail_price" class="form-control form-control-sm" value="{{ $product->retail_price }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Wholesale Price:</label>
                    <input type="number" step="0.01" name="wholesale_price" class="form-control form-control-sm" value="{{ $product->wholesale_price }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Contractor Price:</label>
                    <input type="number" step="0.01" name="contractor_price" class="form-control form-control-sm" value="{{ $product->contractor_price }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Product Image -->
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3">Product Image</h6>
            <div class="d-flex flex-column align-items-center justify-content-center border border-dashed rounded p-4 text-center" style="min-height: 180px;">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                @else
                    <i class="bi bi-image text-secondary mb-2" style="font-size: 2.5rem;"></i>
                @endif
                <div class="small text-muted mb-2">Upload product image</div>
                <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                <div class="text-muted mt-2" style="font-size: 0.7rem;">Max 2MB (jpg, png, jpeg, webp)</div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary mt-3 px-4 fw-semibold">Update</button>
</form>
@endsection
