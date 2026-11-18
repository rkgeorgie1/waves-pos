@extends('layouts.app')
@section('title','New Serial')
@section('content')
<h4>New Serial Number</h4>
<form method="POST" action="{{ route('serial_numbers.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-4"><label>Product</label><select name="product_id" class="form-select">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Warehouse</label><select name="warehouse_id" class="form-select">@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label>Serial</label><input name="serial" class="form-control" required></div>
    <div class="col-md-4"><label>Status</label><select name="status" class="form-select"><option value="in_stock">In Stock</option><option value="sold">Sold</option></select></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection
