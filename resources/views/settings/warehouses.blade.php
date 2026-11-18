@extends('layouts.app')
@section('title','Warehouses')
@section('content')
<h4>Warehouses</h4>
<form method="POST" action="{{ route('settings.warehouses.store') }}" class="row g-2 mb-3">
@csrf
<div class="col-md-3"><input name="name" class="form-control" placeholder="Name" required></div>
<div class="col-md-2"><input name="code" class="form-control" placeholder="Code"></div>
<div class="col-md-3"><input name="address" class="form-control" placeholder="Address"></div>
<div class="col-md-2"><label class="form-check mt-2"><input type="checkbox" name="is_default" value="1" class="form-check-input"> Default</label></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
</form>
<table class="table table-striped bg-white">
    <thead><tr><th>Name</th><th>Code</th><th>Address</th><th>Default</th><th>Active</th><th></th></tr></thead>
    <tbody>
    @foreach($warehouses as $w)
        <tr>
            <form method="POST" action="{{ route('settings.warehouses.update', $w) }}">
            @csrf @method('PUT')
            <td><input name="name" class="form-control form-control-sm" value="{{ $w->name }}" required></td>
            <td><input name="code" class="form-control form-control-sm" value="{{ $w->code }}"></td>
            <td><input name="address" class="form-control form-control-sm" value="{{ $w->address }}"></td>
            <td class="text-center"><input type="checkbox" name="is_default" value="1" {{ $w->is_default?'checked':'' }}></td>
            <td class="text-center"><input type="checkbox" name="is_active" value="1" {{ $w->is_active?'checked':'' }}></td>
            <td><button class="btn btn-sm btn-primary">Save</button></td>
            </form>
        </tr>
    @endforeach
    </tbody>
</table>
<a href="{{ route('settings.index') }}" class="btn btn-secondary">Back</a>
@endsection
