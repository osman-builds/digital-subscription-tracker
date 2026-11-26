@props([
    'dueSoon',
    'currencySymbol' => 'RM',
    'baseCurrency' => 'MYR',
])

<div class="card stat-card h-100">
    <div class="card-header bg-white border-0 pt-3 pb-0">
        <h2 class="h6 fw-bold mb-0"><i class="bi bi-alarm text-warning me-2"></i>Upcoming Renewals (next 7 days)</h2>
    </div>
    <div class="card-body p-0">
        @forelse($dueSoon as $sub)
            <div class="d-flex align-items-center flex-wrap flex-sm-nowrap gap-2 px-3 py-3 border-bottom">
                <div class="flex-shrink-0 me-1">
                    <x-brand-logo :subscription="$sub" :size="38" rounded="rounded-2" />
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate">
                        {{ $sub->name }}
                        @if($sub->is_trial)
                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;"><i class="bi bi-gift me-1"></i>Trial</span>
                        @endif
                    </div>
                    <small class="text-muted">{{ $sub->next_billing_date->format('M d, Y') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                    <div class="text-end">
                        <div class="fw-semibold text-success text-nowrap">{{ $currencySymbol }} {{ number_format($sub->effectiveAmountIn($baseCurrency), 2) }}</div>
                        @if($sub->currency !== $baseCurrency)
                            <small class="text-muted text-nowrap">{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</small>
                        @endif
                        @php $days = $sub->daysUntilRenewal(); @endphp
                        <div><small class="{{ $days <= 1 ? 'text-danger fw-semibold' : 'text-muted' }} text-nowrap">
                            {{ $days === 0 ? 'Today!' : ($days === 1 ? 'Tomorrow' : "in {$days} days") }}
                        </small></div>
                    </div>
                    <form action="{{ route('subscriptions.renew', $sub) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 text-nowrap" title="Mark as Paid & Advance Cycle">
                            <i class="bi bi-check2"></i> <span class="d-none d-sm-inline">Paid</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-all fs-2 d-block mb-2"></i>No renewals in the next 7 days
            </div>
        @endforelse
    </div>
</div>
