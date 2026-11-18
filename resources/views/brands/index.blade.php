@extends('layouts.app')
@section('title','Brands')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Brands</h4><a href="{{ route('brands.create') }}" class="btn btn-primary">Add</a></div>
<table class="table bg-white"><thead><tr><th>Name</th><th></th></tr></thead><tbody>
@foreach($brands as $b)<tr><td>{{ $b->name }}</td><td><a href="{{ route('brands.edit',$b) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>@endforeach
</tbody></table>
@endsection