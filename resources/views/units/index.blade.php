@extends('layouts.app')
@section('title','Units')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Units</h4><a href="{{ route('units.create') }}" class="btn btn-primary">Add</a></div>
<table class="table bg-white"><thead><tr><th>Name</th><th>Short</th><th></th></tr></thead><tbody>
@foreach($units as $u)<tr><td>{{ $u->name }}</td><td>{{ $u->short_name }}</td><td><a href="{{ route('units.edit',$u) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>@endforeach
</tbody></table>
@endsection