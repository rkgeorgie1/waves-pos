@extends('layouts.app')
@section('title','Import Products')
@section('content')
<h4>Import Products (CSV)</h4>
<p>CSV columns: name, sku, barcode, category_id, brand_id, unit_id, purchase_price, retail_price, wholesale_price</p>
<form method="POST" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
@csrf
<div class="mb-3"><input type="file" name="file" class="form-control" required></div>
<button class="btn btn-primary">Import</button>
</form>
@endsection
