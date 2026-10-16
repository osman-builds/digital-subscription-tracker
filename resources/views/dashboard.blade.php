@extends('layouts.app')

@section('title', 'Dashboard – SubShield')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Dashboard</h4>
        <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($fxUpdatedAt)
            <span class="badge bg-light text-muted border">
                <i class="bi bi-currency-exchange me-1"></i>FX updated {{ \Carbon\Carbon::parse($fxUpdatedAt)->diffForHumans() }}
            </span>
        @else
            <span class="badge bg-warning text-dark border">
                <i class="bi bi-exclamation-triangle me-1"></i>Using fallback FX rates
            </span>
        @endif
        <a href="{{ route('subscriptions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Subscription
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">MONTHLY SPEND</span>
                    <span class="bg-primary bg-opacity-10 text-primary rounded-2 p-2"><i class="bi bi-calendar-month fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">RM {{ number_format($totalMonthlyMyr, 2) }}</h3>
                <small class="text-muted">per month</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">YEARLY SPEND</span>
                    <span class="bg-success bg-opacity-10 text-success rounded-2 p-2"><i class="bi bi-calendar-check fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">RM {{ number_format($totalYearlyMyr, 2) }}</h3>
                <small class="text-muted">per year</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">ACTIVE SUBS</span>
                    <span class="bg-info bg-opacity-10 text-info rounded-2 p-2"><i class="bi bi-check2-circle fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $totalActive }}</h3>
                <small class="text-muted">subscriptions</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">DUE THIS WEEK</span>
                    <span class="bg-warning bg-opacity-10 text-warning rounded-2 p-2"><i class="bi bi-bell fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $dueSoon->count() }}</h3>
                <small class="text-muted">renewals in 7 days</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Category breakdown doughnut --}}
    <div class="col-12 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Monthly by Category</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($categoryBreakdown->isNotEmpty())
                    <canvas id="categoryChart" style="max-height:230px;"></canvas>
                @else
                    <p class="text-muted text-center py-4">No data yet</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Due Soon --}}
    <div class="col-12 col-lg-8">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-alarm text-warning me-2"></i>Upcoming Renewals (next 7 days)</h6>
            </div>
            <div class="card-body p-0">
                @forelse($dueSoon as $sub)
                    <div class="d-flex align-items-center px-3 py-3 border-bottom">
                        <div class="me-3">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                  style="width:38px;height:38px;background:{{ $sub->category->color ?? '#6366f1' }}22;color:{{ $sub->category->color ?? '#6366f1' }};">
                                <i class="bi bi-app-indicator"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $sub->name }}</div>
                            <small class="text-muted">{{ $sub->next_billing_date->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold text-success">RM {{ number_format($sub->effectiveMyr(), 2) }}</div>
                            @if($sub->currency !== 'MYR')
                                <small class="text-muted">{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</small>
                            @endif
                            @php $days = $sub->daysUntilRenewal(); @endphp
                            <div><small class="{{ $days <= 1 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $days === 0 ? 'Today!' : ($days === 1 ? 'Tomorrow' : "in {$days} days") }}
                            </small></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-all fs-2 d-block mb-2"></i>No renewals in the next 7 days
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Active subscriptions table --}}
<div class="card stat-card">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-start">
        <div>
            <h6 class="fw-bold mb-0"><i class="bi bi-list-check text-primary me-2"></i>Active Subscriptions</h6>
            <small class="text-muted">All amounts shown in MYR</small>
        </div>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Name</th>
                    <th>Category</th>
                    <th>Original</th>
                    <th>MYR</th>
                    <th class="pe-3">Next Bill</th>
                </tr></thead>
                <tbody>
                    @forelse($subscriptions->take(8) as $sub)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $sub->name }}</td>
                            <td>
                                @if($sub->category)
                                    <span class="badge rounded-pill" style="background:{{ $sub->category->color }};">{{ $sub->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</td>
                            <td class="fw-semibold text-success">RM {{ number_format($sub->effectiveMyr(), 2) }}</td>
                            <td class="pe-3">{{ $sub->next_billing_date->format('M d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No subscriptions yet. <a href="{{ route('subscriptions.create') }}">Add one!</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($categoryBreakdown->isNotEmpty())
<script>
const ctx = document.getElementById('categoryChart');
const palette = ['#6366f1','#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#a855f7','#ec4899','#10b981','#3b82f6'];
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($categoryBreakdown->keys()->values()) !!},
        datasets: [{
            data: {!! json_encode($categoryBreakdown->values()) !!},
            backgroundColor: palette.slice(0, {{ $categoryBreakdown->count() }}),
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ' RM ' + ctx.parsed.toFixed(2) + '/mo'
                }
            }
        }
    }
});
</script>
@endif
@endpush
