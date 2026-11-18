@extends('layouts.app')
@section('title','Staff Management')
@section('content')
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-1"></i>System Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.receipt') }}"><i class="bi bi-printer me-1"></i>Receipt Settings</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('settings.staff') }}"><i class="bi bi-people me-1"></i>Staff Management</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.currencies') }}"><i class="bi bi-currency-exchange me-1"></i>Currencies</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.access') }}"><i class="bi bi-shield-lock me-1"></i>Access Management</a></li>
</ul>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h5 class="mb-3">Add Staff Member</h5>
            <form method="POST" action="{{ route('settings.staff.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Full Name</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <input name="password" type="password" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Department</label>
                    <input name="department" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <input name="position" class="form-control">
                </div>
                <button class="btn btn-primary w-100">Add Staff</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3">
            <h5 class="mb-3">Staff List</h5>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department / Position</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        @php $emp = $employees[$u->id] ?? null; @endphp
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-secondary">{{ $u->role->name ?? 'Unassigned' }}</span></td>
                            <td>{{ $emp ? trim(($emp->department ?? '').' / '.($emp->position ?? ''), ' /') ?: '-' : '-' }}</td>
                            <td>
                                @if($u->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('settings.staff.toggle', $u) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">
                                        {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
