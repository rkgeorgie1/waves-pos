@extends('layouts.app')
@section('title','Currencies')
@section('content')
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-1"></i>System Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.receipt') }}"><i class="bi bi-printer me-1"></i>Receipt Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.staff') }}"><i class="bi bi-people me-1"></i>Staff Management</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('settings.currencies') }}"><i class="bi bi-currency-exchange me-1"></i>Currencies</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.access') }}"><i class="bi bi-shield-lock me-1"></i>Access Management</a></li>
</ul>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3 mb-3">
            <h5 class="mb-3">Default Currency</h5>
            <form method="POST" action="{{ route('settings.currencies.default') }}">
                @csrf
                <div class="mb-3">
                    <select name="currency" class="form-select">
                        @foreach($currencies as $c)
                            <option value="{{ $c['code'] }}" {{ $defaultCurrency === $c['code'] ? 'selected' : '' }}>
                                {{ $c['code'] }} - {{ $c['name'] }} ({{ $c['symbol'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary w-100">Set Default</button>
            </form>
        </div>

        <div class="card p-3">
            <h5 class="mb-3">Add / Update Currency</h5>
            <form method="POST" action="{{ route('settings.currencies.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Code (e.g. USD)</label>
                    <input name="code" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Symbol</label>
                    <input name="symbol" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Exchange Rate</label>
                    <input name="rate" type="number" step="0.000001" class="form-control" value="1" required>
                </div>
                <button class="btn btn-primary w-100">Save Currency</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3">
            <h5 class="mb-3">Configured Currencies</h5>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currencies as $c)
                        <tr>
                            <td><strong>{{ $c['code'] }}</strong></td>
                            <td>{{ $c['name'] }}</td>
                            <td>{{ $c['symbol'] }}</td>
                            <td>{{ $c['rate'] }}</td>
                            <td>
                                @if($defaultCurrency === $c['code'])
                                    <span class="badge bg-success">Default</span>
                                @else
                                    <span class="badge bg-light text-dark">Active</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
