@extends('layouts.app')

@section('title', '{{ $subscription->name }} – SubShield')

@section('content')
<div class="d-flex align-items-center flex-wrap gap-2 mb-4">
    <div class="d-flex align-items-center gap-2 min-w-0 me-auto">
        <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold text-break">{{ $subscription->name }}</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($subscription->status === 'active')
            <form action="{{ route('subscriptions.renew', $subscription) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm" title="Advance to next billing cycle">
                    <i class="bi bi-arrow-repeat me-1"></i>Advance Cycle
                </button>
            </form>
        @endif
        <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST"
              data-confirm="Delete this subscription?">
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
                <div class="d-flex align-items-center flex-wrap mb-3 gap-3">
                    <div class="flex-shrink-0">
                        <x-brand-logo :subscription="$subscription" :size="52" rounded="rounded-3" />
                    </div>
                    <div class="min-w-0">
                        <h5 class="mb-0 fw-bold text-break">{{ $subscription->name }}</h5>
                        @if($subscription->category)
                            <span class="badge rounded-pill" style="background: {{ $subscription->category->color }}25; color: #f8fafc; border: 1px solid {{ $subscription->category->color }}55; font-weight: 600;">
                                {{ $subscription->category->name }}
                            </span>
                        @endif
                    </div>
                    <div class="ms-sm-auto d-flex align-items-center flex-wrap gap-2">
                        @if($subscription->input_source && $subscription->input_source !== 'manual')
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                <i class="bi bi-stars me-1"></i>{{ strtoupper(str_replace('_', ' ', $subscription->input_source)) }}
                            </span>
                        @endif
                        @if($subscription->is_trial)
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-gift me-1"></i>Free Trial
                            </span>
                        @endif
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
                        <div class="fw-bold fs-5">{{ $subscription->currency ?? 'MYR' }} {{ number_format($subscription->amount, 2) }}</div>
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
                        @php
                            $safeWebsiteUrl = $subscription->website_url && in_array(strtolower((string) parse_url($subscription->website_url, PHP_URL_SCHEME)), ['http', 'https'], true)
                                ? $subscription->website_url
                                : null;
                        @endphp
                        @if($safeWebsiteUrl)
                            <a href="{{ $safeWebsiteUrl }}" target="_blank" rel="noopener noreferrer" class="text-truncate d-block">
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
                    @php
                        $safeCancelUrl = $subscription->cancellation_url && in_array(strtolower((string) parse_url($subscription->cancellation_url, PHP_URL_SCHEME)), ['http', 'https'], true)
                            ? $subscription->cancellation_url
                            : null;
                    @endphp
                    @if($safeCancelUrl)
                        <div class="col-12 mt-3 pt-2 border-top">
                            <div class="text-muted small fw-semibold mb-1">CANCELLATION PAGE</div>
                            <a href="{{ $safeCancelUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Direct Cancellation Page
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Cost breakdown --}}
    <div class="col-12 col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Cost Breakdown</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Weekly</span>
                        <strong>{{ $subscription->currency ?? 'MYR' }} {{ number_format($subscription->amount * (match($subscription->billing_cycle) {
                            'weekly' => 1, 'monthly' => 12/52, 'quarterly' => 4/52, 'semi_annual' => 2/52, 'annual' => 1/52, default => 12/52
                        }), 2) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Monthly</span>
                        <strong>{{ $subscription->currency ?? 'MYR' }} {{ number_format($subscription->monthlyAmount(), 2) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Quarterly</span>
                        <strong>{{ $subscription->currency ?? 'MYR' }} {{ number_format($subscription->monthlyAmount() * 3, 2) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="fw-semibold">Yearly</span>
                        <strong class="text-primary">{{ $subscription->currency ?? 'MYR' }} {{ number_format($subscription->yearlyAmount(), 2) }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if($subscription->priceHistories->isNotEmpty() || $subscription->billingEvents->isNotEmpty())
<div class="row g-3 mt-1">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-warning me-2"></i>Price Change History</h6>
            </div>
            <div class="card-body">
                @forelse($subscription->priceHistories as $history)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 border-bottom small">
                        <div class="min-w-0">
                            <span class="fw-semibold">{{ $history->currency }} {{ number_format((float) $history->old_amount, 2) }} &rarr; {{ number_format((float) $history->new_amount, 2) }}</span>
                            <div class="text-muted">{{ $history->recorded_at?->format('M d, Y') }} ({{ str_replace('_', ' ', $history->detected_via) }})</div>
                        </div>
                        <span class="badge bg-{{ $history->change_amount > 0 ? 'danger' : 'success' }}">
                            {{ $history->change_amount > 0 ? '+' : '' }}{{ number_format((float) $history->change_percent, 1) }}%
                        </span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No price changes recorded for this subscription yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-receipt text-primary me-2"></i>Billing Ledger History</h6>
            </div>
            <div class="card-body">
                @forelse($subscription->billingEvents as $event)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 border-bottom small">
                        <div class="min-w-0">
                            <span class="fw-semibold">{{ $event->currency }} {{ number_format((float) $event->amount, 2) }}</span>
                            <span class="text-muted">({{ $event->base_currency }} {{ number_format((float) $event->amount_base, 2) }})</span>
                            <div class="text-muted">{{ $event->billed_at?->format('M d, Y') }} &bull; {{ str_replace('_', ' ', $event->source) }}</div>
                        </div>
                        <span class="text-muted font-monospace">FX {{ number_format((float) $event->fx_rate_snapshot, 4) }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No billing events recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
@endsection
