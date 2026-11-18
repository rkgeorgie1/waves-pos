@extends('layouts.app')
@section('title','Product Variations')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Product Variations</h4><a href="{{ route('product_variants.create') }}" class="btn btn-primary">New</a></div>
<table class="table table-striped bg-white"><thead><tr><th>Product</th><th>Name</th><th>SKU</th><th>Retail</th><th>Wholesale</th></tr></thead><tbody>
@foreach($variants as $v)<tr><td>{{ $v->product->name ?? '-' }}</td><td>{{ $v->name }}</td><td>{{ $v->sku }}</td><td>{{ number_format($v->retail_price,2) }}</td><td>{{ number_format($v->wholesale_price,2) }}</td></tr>@endforeach
</tbody></table>
{{ $variants->links() }}
@endsection
