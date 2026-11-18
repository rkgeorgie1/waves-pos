@extends('layouts.app')
@section('title','Suppliers')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Suppliers</h4><a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add</a></div>
<table class="table bg-white"><thead><tr><th>Name</th><th>Phone</th><th>Balance</th><th></th></tr></thead><tbody>
@foreach($suppliers as $s)<tr><td>{{ $s->name }}</td><td>{{ $s->phone }}</td><td>{{ number_format($s->balance,2) }}</td><td><a href="{{ route('suppliers.edit',$s) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>@endforeach
</tbody></table>
{{ $suppliers->links() }}
@endsection