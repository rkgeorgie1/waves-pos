@extends('layouts.app')
@section('title','Update Price')
@section('content')
<h4>Update Price</h4>
<form method="POST" action="{{ route('products.update_price.store') }}">
@csrf
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Purchase</th><th>Retail</th><th>Wholesale</th></tr></thead>
    <tbody>
    @foreach($products as $i => $p)
        <tr>
            <td>{{ $p->name }}<input type="hidden" name="prices[{{ $i }}][id]" value="{{ $p->id }}"></td>
            <td><input name="prices[{{ $i }}][purchase_price]" type="number" step="0.01" class="form-control" value="{{ $p->purchase_price }}"></td>
            <td><input name="prices[{{ $i }}][retail_price]" type="number" step="0.01" class="form-control" value="{{ $p->retail_price }}"></td>
            <td><input name="prices[{{ $i }}][wholesale_price]" type="number" step="0.01" class="form-control" value="{{ $p->wholesale_price }}"></td>
        </tr>
    @endforeach
    </tbody>
</table>
<button class="btn btn-primary">Update</button>
</form>
@endsection
