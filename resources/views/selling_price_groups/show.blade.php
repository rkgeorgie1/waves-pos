@extends('layouts.app')
@section('title','Selling Price Group')
@section('content')
<h4>{{ $sellingPriceGroup->name }}</h4>
<form method="POST" action="{{ route('selling_price_groups.assign', $sellingPriceGroup) }}" class="row g-2 mb-3">
@csrf
<div class="col-md-6"><select name="product_id" class="form-select">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-3"><input name="price" type="number" step="0.01" class="form-control" placeholder="Price" required></div>
<div class="col-md-3"><button class="btn btn-primary">Assign</button></div>
</form>
<table class="table table-striped bg-white"><thead><tr><th>Product</th><th>Price</th></tr></thead><tbody>
@foreach($sellingPriceGroup->products as $p)<tr><td>{{ $p->name }}</td><td>{{ number_format($p->pivot->price,2) }}</td></tr>@endforeach
</tbody></table>
<a href="{{ route('selling_price_groups.index') }}" class="btn btn-secondary">Back</a>
@endsection
