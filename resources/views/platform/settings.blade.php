@extends('layouts.app')
@section('title','Platform WhatsApp Settings')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-whatsapp me-2"></i>WhatsApp Payments</h4>
    <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to console</a>
</div>

<div class="card p-4" style="max-width: 720px;">
    <p class="text-muted small">Clients use this number to chat with you about subscription payments. Leave the number blank to hide all WhatsApp buttons.</p>

    <form method="POST" action="{{ route('platform.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="whatsapp_number" class="form-label">Admin WhatsApp number</label>
            <input id="whatsapp_number" name="whatsapp_number" type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $whatsappNumber) }}" placeholder="0712345678 or 254712345678" maxlength="30">
            @error('whatsapp_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Use international format, or a local number starting with 0.</div>
        </div>

        <div class="mb-3">
            <label for="whatsapp_country_code" class="form-label">Country code for local numbers</label>
            <input id="whatsapp_country_code" name="whatsapp_country_code" type="text" class="form-control @error('whatsapp_country_code') is-invalid @enderror" value="{{ old('whatsapp_country_code', $whatsappCountryCode) }}" placeholder="254" maxlength="5">
            @error('whatsapp_country_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Used only when the number above starts with 0. Defaults to 254.</div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-success btn-sm">Save WhatsApp settings</button>
            @if(\App\WhatsApp::configured())
                <span class="badge bg-success">Buttons enabled</span>
            @else
                <span class="badge bg-secondary">Buttons hidden</span>
            @endif
        </div>
    </form>
</div>
@endsection
