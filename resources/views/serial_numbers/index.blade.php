@extends('layouts.app')
@section('title', 'Serial Numbers')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Serial Numbers</h4>
    <a href="{{ route('serial_numbers.create') }}" class="btn btn-primary">New Serial</a>
</div>
<table class="table table-striped bg-white">
    <thead><tr><th>Product</th><th>Warehouse</th><th>Serial</th><th>Status</th><th>Sale</th></tr></thead>
    <tbody>
    @foreach($serials as $s)
        <tr>
            <td>{{ $s->product->name ?? '-' }}</td>
            <td>{{ $s->warehouse->name ?? '-' }}</td>
            <td>{{ $s->serial }}</td>
            <td>{{ $s->status }}</td>
            <td>{{ $s->sale_id ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $serials->links() }}
@endsection
