@extends('layouts.app')
@section('title','Warranties')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Warranties</h4><a href="{{ route('warranties.create') }}" class="btn btn-primary">New</a></div>
<table class="table table-striped bg-white"><thead><tr><th>Name</th><th>Duration</th><th>Description</th></tr></thead><tbody>
@foreach($warranties as $w)<tr><td>{{ $w->name }}</td><td>{{ $w->duration }} {{ $w->duration_type }}</td><td>{{ $w->description }}</td></tr>@endforeach
</tbody></table>
{{ $warranties->links() }}
@endsection
