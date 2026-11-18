@extends('layouts.app')
@section('title','Access Management')
@section('content')
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-1"></i>System Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.receipt') }}"><i class="bi bi-printer me-1"></i>Receipt Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.staff') }}"><i class="bi bi-people me-1"></i>Staff Management</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.currencies') }}"><i class="bi bi-currency-exchange me-1"></i>Currencies</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('settings.access') }}"><i class="bi bi-shield-lock me-1"></i>Access Management</a></li>
</ul>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3 mb-3">
            <h5 class="mb-3">Create Role</h5>
            <form method="POST" action="{{ route('settings.roles.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Role Name</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input name="description" class="form-control">
                </div>
                <button class="btn btn-primary w-100">Add Role</button>
            </form>
        </div>

        <div class="card p-3">
            <h5 class="mb-3">Create Permission</h5>
            <form method="POST" action="{{ route('settings.permissions.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Permission Name</label>
                    <input name="name" class="form-control" placeholder="e.g. Manage Products" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Module</label>
                    <input name="module" class="form-control" placeholder="e.g. Products">
                </div>
                <button class="btn btn-secondary w-100">Add Permission</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-3">
            <h5 class="mb-3">Roles & Permissions</h5>
            @foreach($roles as $role)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $role->name }}</strong>
                            <span class="text-muted small ms-2">({{ $role->users_count }} staff)</span>
                            @if($role->description)
                                <div class="text-muted small">{{ $role->description }}</div>
                            @endif
                        </div>
                        <span class="badge bg-light text-dark">{{ $role->slug }}</span>
                    </div>
                    @if($permissions->isNotEmpty())
                        <form method="POST" action="{{ route('settings.roles.permissions', $role) }}">
                            @csrf
                            <div class="row g-2 mb-2">
                                @foreach($permissions as $perm)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="role_{{ $role->id }}_perm_{{ $perm->id }}" {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="role_{{ $role->id }}_perm_{{ $perm->id }}">
                                                {{ $perm->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Save Permissions</button>
                        </form>
                    @else
                        <div class="text-muted small">No permissions defined yet. Add permissions on the left to assign them to roles.</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
