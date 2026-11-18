@extends('layouts.app')
@section('title','System Settings')
@section('content')
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link active" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-1"></i>System Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.receipt') }}"><i class="bi bi-printer me-1"></i>Receipt Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.staff') }}"><i class="bi bi-people me-1"></i>Staff Management</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.currencies') }}"><i class="bi bi-currency-exchange me-1"></i>Currencies</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.access') }}"><i class="bi bi-shield-lock me-1"></i>Access Management</a></li>
</ul>

<div class="card p-4 mb-3">
    <h5 class="mb-3">Business Logo</h5>
    @php $logo = \App\Models\Setting::where('key','business_logo')->value('value'); @endphp
    @if($logo)
        <div class="mb-2"><img src="{{ asset('storage/'.$logo) }}" alt="Logo" style="max-height:80px"></div>
        <form method="POST" action="{{ route('settings.logo.remove') }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Remove</button></form>
    @endif
    <form method="POST" action="{{ route('settings.logo.upload') }}" enctype="multipart/form-data" class="mt-2">
        @csrf
        <div class="input-group">
            <input type="file" name="logo" class="form-control" accept="image/*" required>
            <button class="btn btn-primary">Upload</button>
        </div>
    </form>
</div>
<div class="card p-4">
    <h5 class="mb-3">System Settings</h5>
    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        <div class="row g-3">
            @foreach($settings as $s)
                <div class="col-md-6">
                    <label class="form-label text-capitalize">{{ str_replace('_', ' ', $s->key) }}</label>
                    <input name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                </div>
            @endforeach
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection
