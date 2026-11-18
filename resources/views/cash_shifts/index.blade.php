@extends('layouts.app')
@section('title', 'Cash Shifts')
@section('content')
<h4>Cash Shifts (Z-Report)</h4>
<form method="POST" action="{{ route('cash_shifts.open') }}" class="row g-2 mb-3">
@csrf
<div class="col-md-4">
    <select name="cash_register_id" class="form-select">@foreach($registers as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select>
</div>
<div class="col-md-4"><input name="opening_balance" type="number" step="0.01" class="form-control" placeholder="Opening balance" required></div>
<div class="col-md-4"><button class="btn btn-primary">Open Shift</button></div>
</form>
<table class="table table-striped bg-white">
    <thead><tr><th>Register</th><th>User</th><th>Opened</th><th>Closed</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($shifts as $s)
        <tr>
            <td>{{ $s->cashRegister->name ?? '-' }}</td>
            <td>{{ $s->user->name ?? '-' }}</td>
            <td>{{ $s->opened_at }}</td>
            <td>{{ $s->closed_at ?? '-' }}</td>
            <td>{{ $s->status }}</td>
            <td><a href="{{ route('cash_shifts.show', $s) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $shifts->links() }}
@endsection
