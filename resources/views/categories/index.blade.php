@extends('layouts.app')
@section('title','Categories')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Categories</h4><a href="{{ route('categories.create') }}" class="btn btn-primary">Add</a></div>
<table class="table bg-white"><thead><tr><th>Name</th><th></th></tr></thead><tbody>
@foreach($categories as $c)<tr><td>{{ $c->name }}</td><td><a href="{{ route('categories.edit',$c) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>@endforeach
</tbody></table>
@endsection