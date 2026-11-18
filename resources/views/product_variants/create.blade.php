@extends('layouts.app')
@section('title','New Variation')
@section('content')
<h4>New Product Variation</h4>
<form method="POST" action="{{ route('product_variants.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-4"><label>Product</label><select name="product_id" class="form-select">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-4"><label>SKU</label><input name="sku" class="form-control"></div>
    <div class="col-md-4"><label>Barcode</label><input name="barcode" class="form-control"></div>
    <div class="col-md-4"><label>Purchase Price</label><input name="purchase_price" type="number" step="0.01" class="form-control" value="0"></div>
    <div class="col-md-4"><label>Retail Price</label><input name="retail_price" type="number" step="0.01" class="form-control" value="0"></div>
    <div class="col-md-4"><label>Wholesale Price</label><input name="wholesale_price" type="number" step="0.01" class="form-control" value="0"></div>
    <div class="col-md-4"><label>Attributes (JSON)</label><input name="attributes" class="form-control"></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection
