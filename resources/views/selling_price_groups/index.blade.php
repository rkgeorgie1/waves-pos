@extends('layouts.app')
@section('title','Selling Price Groups')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Selling Price Groups</h4><a href="{{ route('selling_price_groups.create') }}" class="btn btn-primary">New</a></div>
<table class="table table-striped bg-white"><thead><tr><th>Name</th><th>Description</th><th></th></tr></thead><tbody>
@foreach($groups as $g)<tr><td>{{ $g->name }}</td><td>{{ $g->description }}</td><td><a href="{{ route('selling_price_groups.show',$g) }}" class="btn btn-sm btn-outline-secondary">Manage</a></td></tr>@endforeach
</tbody></table>
{{ $groups->links() }}
@endsection
