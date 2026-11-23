@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Customers</h4>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">+ Add Customer</a>
</div>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('customers.index') }}" data-auto-filter>
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Name, Phone, Business Name..." value="{{ $q ?? '' }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn upos-filter-btn btn-sm px-3">Filter</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm px-3">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card p-3" style="overflow: visible;">
    <div class="table-responsive" style="overflow: visible;">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem; overflow: visible;">
            <thead class="table-light">
                <tr>
                    <th style="width: 16%;">Name</th>
                    <th style="width: 14%;">Business Name</th>
                    <th style="width: 12%;">Contact</th>
                    <th style="width: 18%;">Email</th>
                    <th style="width: 12%;">Group</th>
                    <th class="text-end" style="width: 12%;">Balance</th>
                    <th class="text-end" style="width: 16%;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('customers.show', $c) }}" class="text-decoration-none">{{ $c->name }}</a></td>
                        <td>{{ $c->business_name ?? '-' }}</td>
                        <td>{{ $c->phone ?? '-' }}</td>
                        <td>{{ $c->email ?? '-' }}</td>
                        <td>{{ $c->customerGroup->name ?? '-' }}</td>
                        <td class="text-end {{ $c->balance > 0 ? 'text-danger fw-semibold' : '' }}">KSh {{ number_format($c->balance ?? 0, 2) }}</td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.82rem;">
                                    <li><a class="dropdown-item py-1" href="{{ route('customers.show', $c) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item py-1" href="{{ route('customers.edit', $c) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('customers.destroy', $c) }}" onsubmit="return confirm('Delete this customer?')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item py-1 text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $customers->links() }}
    </div>
</div>
@endsection
