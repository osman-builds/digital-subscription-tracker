@extends('layouts.app')

@section('title', '{{ $subscription->name }} – SubShield')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-bold">{{ $subscription->name }}</h4>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST"
              onsubmit="return confirm('Delete this subscription?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    {{-- Main details card --}}
    <div class="col-12 col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3 gap-3">
                    <x-brand-logo :subscription="$subscription" :size="52" rounded="rounded-3" />
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $subscription->name }}</h5>
                        @if($subscription->category)
                            <span class="badge" style="background:{{ $subscription->category->color }};">
                                {{ $subscription->category->name }}
                            </span>
                        @endif
                    </div>
                    <div class="ms-auto">
                        @if($subscription->status === 'active')
                            <span class="badge bg-success fs-6">Active</span>
                        @elseif($subscription->status === 'inactive')
                            <span class="badge bg-secondary fs-6">Inactive</span>
                        @else
                            <span class="badge bg-danger fs-6">Cancelled</span>
                        @endif
                    </div>
                </div>

                @if($subscription->description)
                    <p class="text-muted">{{ $subscription->description }}</p>
                @endif

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small fw-semibold">AMOUNT</div>
                        <div class="fw-bold fs-5">${{ number_format($subscription->amount, 2) }}</div>
                        <div class="text-muted small">{{ $subscription->billingCycleLabel() }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-semibold">NEXT BILLING</div>
                        <div class="fw-bold fs-5">{{ $subscription->next_billing_date->format('M d, Y') }}</div>
                        @php $days = $subscription->daysUntilRenewal(); @endphp
                        <div class="{{ $days <= 3 ? 'text-danger' : 'text-muted' }} small">
                            {{ $days === 0 ? 'Due today!' : ($days === 1 ? 'Due tomorrow' : "In {$days} days") }}
                        </div>
                    </div>
                    <div class="col-6 mt-2">
                        <div class="text-muted small fw-semibold">START DATE</div>
                        <div>{{ $subscription->start_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-6 mt-2">
                        <div class="text-muted small fw-semibold">WEBSITE</div>
                        @if($subscription->website_url)
                            <a href="{{ $subscription->website_url }}" target="_blank" rel="noopener" class="text-truncate d-block">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Visit site
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    @if($subscription->notes)
                        <div class="col-12 mt-2">
                            <div class="text-muted small fw-semibold">NOTES</div>
                            <div>{{ $subscription->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Cost breakdown --}}
    <div class="col-12 col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Cost Breakdown</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Weekly</span>
                        <strong>${{ number_format($subscription->amount * (match($subscription->billing_cycle) {
                            'weekly' => 1, 'monthly' => 12/52, 'quarterly' => 4/52, 'semi_annual' => 2/52, 'annual' => 1/52, default => 12/52
                        }), 2) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Monthly</span>
                        <strong>${{ number_format($subscription->monthlyAmount(), 2) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Quarterly</span>
                        <strong>${{ number_format($subscription->monthlyAmount() * 3, 2) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="fw-semibold">Yearly</span>
                        <strong class="text-primary">${{ number_format($subscription->yearlyAmount(), 2) }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
