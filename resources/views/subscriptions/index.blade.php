@extends('layouts.app')

@section('title', 'Subscriptions – SubShield')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Subscriptions</h4>
    <a href="{{ route('subscriptions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Subscription
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('subscriptions.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name…"
                       value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Subscriptions list --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Subscription</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Cycle</th>
                        <th>Next Billing</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <x-brand-logo :subscription="$sub" :size="34" rounded="rounded-2" />
                                    <div>
                                        <div class="fw-semibold">{{ $sub->name }}</div>
                                        @if($sub->description)
                                            <small class="text-muted">{{ Str::limit($sub->description, 40) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($sub->category)
                                    <span class="badge rounded-pill" style="background:{{ $sub->category->color }};">
                                        {{ $sub->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</div>
                                @if($sub->currency !== 'MYR')
                                    <small class="text-success">≈ RM {{ number_format($sub->effectiveMyr(), 2) }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $sub->billingCycleLabel() }}</span></td>
                            <td>
                                {{ $sub->next_billing_date->format('M d, Y') }}
                                @php $days = $sub->daysUntilRenewal(); @endphp
                                @if($days >= 0 && $days <= 7 && $sub->status === 'active')
                                    <span class="badge bg-warning text-dark ms-1">
                                        {{ $days === 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : "{$days}d") }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sub->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($sub->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('subscriptions.show', $sub) }}"
                                   class="btn btn-sm btn-outline-secondary me-1" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('subscriptions.edit', $sub) }}"
                                   class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('subscriptions.destroy', $sub) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete {{ addslashes($sub->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No subscriptions found.
                                <a href="{{ route('subscriptions.create') }}" class="d-block mt-2">Add your first subscription</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($subscriptions->hasPages())
        <div class="card-footer border-0 py-3">
            {{ $subscriptions->links() }}
        </div>
    @endif
</div>
@endsection
