@extends('layouts.app')
@section('title','Import Sales')
@section('content')
<h4>Import Sales (CSV)</h4>
<p>CSV columns: reference, sku, product_id, customer_id, warehouse_id, quantity, unit_price</p>
<form method="POST" action="{{ route('sales.import.store') }}" enctype="multipart/form-data">
@csrf
<div class="mb-3"><input type="file" name="file" class="form-control" required></div>
<button class="btn btn-primary">Import</button>
</form>
@endsection
