@extends('layouts.app')
@section('title','Billing & Plans')
@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Billing &amp; Plans</h4>
</div>

<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-start gap-3">
        <div class="min-w-0">
            <div class="text-muted small">Current plan</div>
            <div class="fs-4 fw-semibold">{{ $plan?->name ?? 'No active plan' }}</div>
            @if($plan)
                <div class="text-muted small">{{ $plan->description }}</div>
            @endif
        </div>
        <div class="text-start text-md-end">
            @if($subscription && $subscription->isCurrentlyActive())
                <span class="badge bg-success">Active ({{ ucfirst($subscription->status) }})</span>
            @else
                <span class="badge bg-warning text-dark">Inactive</span>
            @endif
            @if($subscription?->trial_ends_at)
                <div class="text-muted small mt-1">Trial ends {{ $subscription->trial_ends_at->format('Y-m-d') }}</div>
            @elseif($subscription?->current_period_end)
                <div class="text-muted small mt-1">Renews {{ $subscription->current_period_end->format('Y-m-d') }}</div>
            @endif
        </div>
    </div>
</div>

<div class="card p-3 p-md-4 mb-4">
    <h5 class="mb-3">Usage this plan</h5>
    <div class="row g-3 g-md-4">
        @foreach($usage as $key => $value)
            @php
                $label = ['users' => 'Users', 'products' => 'Products', 'warehouses' => 'Warehouses', 'sales_this_month' => 'Sales this month'][$key];
                $percent = $value['limit'] ? min(100, (int) round($value['used'] / $value['limit'] * 100)) : 0;
                $overLimit = $value['limit'] !== null && $value['used'] > $value['limit'];
            @endphp
            <div class="col-12 col-lg-6">
                <div class="d-flex justify-content-between gap-2 small">
                    <span class="fw-semibold">{{ $label }}</span>
                    <span class="{{ $overLimit ? 'text-danger fw-semibold' : '' }}">{{ $value['used'] }} / {{ $value['limit'] ?? 'Unlimited' }}</span>
                </div>
                @if($value['limit'])
                    <div class="progress mt-1" style="height:6px">
                        <div class="progress-bar {{ $overLimit || $percent >= 90 ? 'bg-danger' : 'bg-primary' }}" style="width:{{ $percent }}%"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="card p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Available plans</h5>
        <span class="text-muted small"><i class="bi bi-whatsapp me-1"></i>Chat with the admin to arrange payment</span>
    </div>

    <div class="row g-3">
        @foreach($plans as $option)
            @php
                $isCurrent = $plan?->id === $option->id;
            @endphp
            <div class="col-12 col-sm-6 col-xl-3">
                @if($isCurrent)
                    <div class="card h-100 p-3 border-primary">
                        <div class="fw-semibold">{{ $option->name }}</div>
                        <div class="text-muted small mb-2">{{ $option->description }}</div>
                        <div class="fs-5 fw-semibold mb-3">{{ $option->currency }} {{ number_format((float) $option->monthly_price, 2) }}<small class="text-muted fw-normal">/mo</small></div>
                        <ul class="list-unstyled small mb-3">
                            <li>{{ $option->max_users ?? 'Unlimited' }} users</li>
                            <li>{{ $option->max_products ?? 'Unlimited' }} products</li>
                            <li>{{ $option->max_warehouses ?? 'Unlimited' }} warehouses</li>
                            <li>{{ $option->max_sales_per_month ?? 'Unlimited' }} sales / month</li>
                        </ul>
                        <div class="mt-auto small fw-semibold text-primary"><i class="bi bi-check-circle me-1"></i>Current plan</div>
                    </div>
                @else
                    <div class="card h-100 p-3 d-flex flex-column">
                        <div class="fw-semibold">{{ $option->name }}</div>
                        <div class="text-muted small mb-2">{{ $option->description }}</div>
                        <div class="fs-5 fw-semibold mb-3">{{ $option->currency }} {{ number_format((float) $option->monthly_price, 2) }}<small class="text-muted fw-normal">/mo</small></div>
                        <ul class="list-unstyled small mb-3">
                            <li>{{ $option->max_users ?? 'Unlimited' }} users</li>
                            <li>{{ $option->max_products ?? 'Unlimited' }} products</li>
                            <li>{{ $option->max_warehouses ?? 'Unlimited' }} warehouses</li>
                            <li>{{ $option->max_sales_per_month ?? 'Unlimited' }} sales / month</li>
                        </ul>
                        <div class="mt-auto d-grid gap-2">
                            @if($whatsappConfigured)
                                <a href="{{ route('billing.pay', ['plan' => $option, 'interval' => 'monthly']) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-whatsapp me-1"></i>Chat about {{ $option->name }}
                                </a>
                                @if((float) $option->yearly_price > 0)
                                    <a href="{{ route('billing.pay', ['plan' => $option, 'interval' => 'yearly']) }}" class="btn btn-outline-success btn-sm">
                                        Ask about yearly ({{ $option->currency }} {{ number_format((float) $option->yearly_price, 2) }})
                                    </a>
                                @endif
                            @else
                                <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>WhatsApp payments are not configured yet.</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
        <a href="{{ route('billing.invoices') }}" class="btn btn-outline-secondary btn-sm">Invoices</a>
        @if($subscription && $subscription->status !== 'cancelled')
            <form method="POST" action="{{ route('billing.cancel') }}">
                @csrf
                <button class="btn btn-outline-danger btn-sm">Cancel subscription</button>
            </form>
        @endif
    </div>
</div>
@endsection
