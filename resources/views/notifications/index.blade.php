@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Notifications</h4>
    @if(auth()->user()->unreadNotificationsCount() > 0)
        <form method="POST" action="{{ route('notifications.read_all') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">Mark All as Read</button>
        </form>
    @endif
</div>

<div class="card p-3">
    @forelse($notifications as $n)
        <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ $n->is_read ? 'opacity-75' : 'bg-light' }}" style="border-radius: 8px; margin-bottom: 8px;">
            <div class="upos-icon-circle {{ $n->type === 'success' ? 'circle-emerald' : ($n->type === 'warning' ? 'circle-amber' : ($n->type === 'danger' ? 'circle-red' : 'circle-sky')) }}" style="width: 36px; height: 36px; font-size: 0.9rem; flex-shrink: 0;">
                <i class="bi {{ $n->type === 'success' ? 'bi-check-circle' : ($n->type === 'warning' ? 'bi-exclamation-triangle' : ($n->type === 'danger' ? 'bi-x-circle' : 'bi-info-circle')) }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-semibold text-dark">{{ $n->title }}</div>
                    <div class="small text-muted">{{ $n->created_at->diffForHumans() }}</div>
                </div>
                <div class="small text-muted mt-1">{{ $n->message }}</div>
            </div>
            @if(! $n->is_read)
                <form method="POST" action="{{ route('notifications.read', $n) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.7rem;">Mark Read</button>
                </form>
            @endif
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-bell-slash fs-1 mb-2"></i>
            <div>No notifications.</div>
        </div>
    @endforelse

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
