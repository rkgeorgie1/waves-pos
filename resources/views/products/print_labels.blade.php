@extends('layouts.app')
@section('title','Print Labels')
@section('content')
<h4>Print Labels</h4>
<div class="row">
@foreach($products as $p)
    <div class="col-md-3 mb-3">
        <div class="card p-2 text-center">
            <div>{{ $p->name }}</div>
            <div class="fw-bold">{{ $p->sku }}</div>
            <div style="font-family:monospace;letter-spacing:2px">{{ $p->barcode ?? $p->sku }}</div>
            <div>{{ number_format($p->retail_price,2) }}</div>
        </div>
    </div>
@endforeach
</div>
<button onclick="window.print()" class="btn btn-primary">Print</button>
@endsection
