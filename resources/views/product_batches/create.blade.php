@extends('layouts.app')
@section('title','New Batch')
@section('content')
<h4>New Product Batch</h4>
<form method="POST" action="{{ route('product_batches.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-4"><label>Product</label><select name="product_id" class="form-select">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Warehouse</label><select name="warehouse_id" class="form-select">@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Batch Number</label><input name="batch_number" class="form-control" required></div>
    <div class="col-md-4"><label>Expiry Date</label><input name="expiry_date" type="date" class="form-control"></div>
    <div class="col-md-4"><label>Quantity</label><input name="quantity" type="number" step="0.0001" class="form-control" value="0" required></div>
    <div class="col-md-4"><label>Cost</label><input name="cost" type="number" step="0.01" class="form-control" value="0"></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection
