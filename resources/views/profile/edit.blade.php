@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Account Settings</h4>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="mb-1">Profile information</h5>
            <p class="text-muted small mb-3">Update your name and login email address.</p>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-4 mb-3">
            <h5 class="mb-1">Change password</h5>
            <p class="text-muted small mb-3">Use your current password to confirm the change.</p>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password">
                    @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">
                    @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Update password</button>
            </form>
        </div>

        <div class="card p-4 border-danger">
            <h5 class="mb-1 text-danger">Delete account</h5>
            <p class="text-muted small mb-3">Permanently remove your account and all of its data.</p>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="mb-3">
                    <label class="form-label" for="delete_password">Password</label>
                    <input id="delete_password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" required autocomplete="current-password">
                    @error('password', 'userDeletion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-outline-danger">Delete account</button>
            </form>
        </div>
    </div>
</div>
@endsection
