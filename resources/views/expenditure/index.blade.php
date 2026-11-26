@extends('layouts.app')

@section('title', 'Expenditure – SubShield')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Expenditure Tracker</h4>
        <small class="text-muted">All amounts normalised to {{ $baseCurrency ?? 'MYR' }}</small>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export expenditure data">
                <i class="bi bi-download"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('expenditure.export_csv', ['year' => $year]) }}">
                        <i class="bi bi-filetype-csv text-success fs-6"></i>
                        <div>
                            <div class="fw-semibold small">Export CSV</div>
                            <div class="text-muted" style="font-size: 0.72rem;">Arranged expenditure ledger</div>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('expenditure.export_pdf', ['year' => $year]) }}" target="_blank">
                        <i class="bi bi-file-earmark-pdf text-danger fs-6"></i>
                        <div>
                            <div class="fw-semibold small">Export PDF / Print</div>
                            <div class="text-muted" style="font-size: 0.72rem;">Printable A4 landscape audit</div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        @if($fxUpdatedAt)
            <span class="badge bg-light text-muted border">
                <i class="bi bi-currency-exchange me-1"></i>FX {{ \Carbon\Carbon::parse($fxUpdatedAt)->diffForHumans() }}
            </span>
        @endif
        {{-- Year selector --}}
        <form method="GET" action="{{ route('expenditure.index') }}" class="d-flex gap-2">
            <label for="expenditure_year" class="visually-hidden">Select Year</label>
            <select id="expenditure_year" name="year" class="form-select form-select-sm" style="width:100px" data-auto-submit="true">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Yearly summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">YEARLY TOTAL</span>
                    <span class="bg-primary bg-opacity-10 text-primary rounded-2 p-2"><i class="bi bi-calendar2-range fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $currencySymbol ?? 'RM' }} {{ number_format($yearlyTotal, 2) }}</h3>
                <small class="text-muted">{{ $year }} ({{ $baseCurrency ?? 'MYR' }})</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">AVG / MONTH</span>
                    <span class="bg-success bg-opacity-10 text-success rounded-2 p-2"><i class="bi bi-calculator fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $currencySymbol ?? 'RM' }} {{ number_format($yearlyTotal / 12, 2) }}</h3>
                <small class="text-muted">per month average</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">AVG / WEEK</span>
                    <span class="bg-info bg-opacity-10 text-info rounded-2 p-2"><i class="bi bi-calendar-week fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $currencySymbol ?? 'RM' }} {{ number_format($yearlyTotal / 52, 2) }}</h3>
                <small class="text-muted">per week average</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">AVG / DAY</span>
                    <span class="bg-warning bg-opacity-10 text-warning rounded-2 p-2"><i class="bi bi-calendar-day fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $currencySymbol ?? 'RM' }} {{ number_format($yearlyTotal / 365, 2) }}</h3>
                <small class="text-muted">per day average</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Monthly bar chart --}}
    <div class="col-12 col-lg-8">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Monthly Spend — {{ $year }}</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Category doughnut --}}
    <div class="col-12 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart text-success me-2"></i>Yearly by Category</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($categoryBreakdown->isNotEmpty())
                    <canvas id="categoryChart" style="max-height:240px;"></canvas>
                @else
                    <p class="text-muted">No data</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Per-subscription table --}}
<div class="card stat-card">
    <div class="card-header bg-white border-0 pt-3 pb-0">
        <h6 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Yearly Cost per Subscription</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Subscription</th>
                        <th>Category</th>
                        <th>Cycle</th>
                        <th>Original Price</th>
                        <th>{{ $baseCurrency ?? 'MYR' }} / Month</th>
                        <th class="pe-4">{{ $baseCurrency ?? 'MYR' }} / Year</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptionYearly as $i => $sub)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $sub->name }}</td>
                            <td>
                                @if($sub->category)
                                    <span class="badge rounded-pill" style="background: {{ $sub->category->color }}25; color: #f8fafc; border: 1px solid {{ $sub->category->color }}55; font-weight: 600;">{{ $sub->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $sub->billingCycleLabel() }}</span></td>
                            <td>{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</td>
                            <td class="text-success fw-semibold">{{ $currencySymbol ?? 'RM' }} {{ number_format($sub->monthlyAmountIn($baseCurrency ?? 'MYR'), 2) }}</td>
                            <td class="pe-4 text-primary fw-bold">{{ $currencySymbol ?? 'RM' }} {{ number_format($sub->yearlyAmountIn($baseCurrency ?? 'MYR'), 2) }}</td>
                        </tr>
                    @endforeach
                    @if($subscriptionYearly->isNotEmpty())
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="ps-4 text-end">TOTAL</td>
                            <td class="text-success">{{ $currencySymbol ?? 'RM' }} {{ number_format($subscriptionYearly->sum(fn($s) => $s->monthlyAmountIn($baseCurrency ?? 'MYR')), 2) }}</td>
                            <td class="pe-4 text-primary">{{ $currencySymbol ?? 'RM' }} {{ number_format($yearlyTotal, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($subscriptionYearly->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No active subscriptions found.
                <a href="{{ route('subscriptions.create') }}" class="d-block mt-1">Add one!</a>
            </div>
        @endif
    </div>
</div>

{{-- Cashflow Spike Forecast & Price Creep Intelligence --}}
@php
    $spikes = $intelligence['renewal_spikes'] ?? collect();
    $priceCreep = $intelligence['price_creep'] ?? ['items' => collect(), 'extra_yearly_cost' => 0];
@endphp
<div class="row g-3 mt-3">
    <div class="col-12 col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow text-warning me-2"></i>12-Month Cashflow Spike Forecast</h6>
            </div>
            <div class="card-body">
                @forelse($spikes as $spike)
                    <div class="p-3 rounded-2 bg-body-tertiary border mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">{{ $spike['month_label'] }}</span>
                            <span class="badge bg-warning text-dark">{{ $spike['ratio_to_average'] }}x monthly avg — {{ $currencySymbol ?? 'RM' }} {{ number_format($spike['projected_amount'], 2) }}</span>
                        </div>
                        <small class="text-muted">
                            Driven by: {{ collect($spike['events'])->map(fn($e) => "{$e['name']} ({$e['cycle']})")->implode(', ') }}
                        </small>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No abnormal multi-month renewal spikes forecasted over the next 12 months.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0"><i class="bi bi-arrow-up-right-circle text-danger me-2"></i>Price Creep Tracker</h6>
                @if(($priceCreep['extra_yearly_cost'] ?? 0) > 0)
                    <span class="badge bg-danger bg-opacity-10 text-danger">+{{ $currencySymbol ?? 'RM' }} {{ number_format($priceCreep['extra_yearly_cost'], 2) }}/yr</span>
                @endif
            </div>
            <div class="card-body">
                @forelse($priceCreep['items'] as $hike)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 border-bottom">
                        <div class="min-w-0">
                            <div class="fw-semibold small">{{ $hike['name'] }}</div>
                            <small class="text-muted">
                                {{ $hike['currency'] }} {{ number_format($hike['old_amount'], 2) }} &rarr; {{ number_format($hike['new_amount'], 2) }}
                                <span class="badge bg-light text-dark border ms-1">{{ str_replace('_', ' ', $hike['detected_via']) }}</span>
                            </small>
                        </div>
                        <span class="badge bg-danger">+{{ number_format($hike['change_percent'], 1) }}%</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No silent subscription price hikes detected yet. Price edits and AI receipt scans automatically track inflation here.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Immutable Billing Ledger with FX Snapshots --}}
<div class="card stat-card mt-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Recent Billing Ledger (FX Snapshots)</h6>
            <small class="text-muted">Immutable historical charges preserving exact FX rates on each billing date</small>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Billed Date</th>
                        <th>Subscription</th>
                        <th>Original Amount</th>
                        <th>FX Rate Snapshot</th>
                        <th>Recorded Base</th>
                        <th class="pe-4">Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($billingEvents ?? collect()) as $event)
                        <tr>
                            <td class="ps-4 text-muted">{{ $event->billed_at?->format('M d, Y') }}</td>
                            <td class="fw-semibold">{{ $event->subscription?->name ?? 'Removed Subscription' }}</td>
                            <td>{{ $event->currency }} {{ number_format((float) $event->amount, 2) }}</td>
                            <td class="text-muted small font-monospace">{{ number_format((float) $event->fx_rate_snapshot, 4) }}</td>
                            <td class="fw-semibold text-success">{{ $event->base_currency }} {{ number_format((float) $event->amount_base, 2) }}</td>
                            <td class="pe-4"><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $event->source) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">
                                No billing ledger entries recorded yet. Creating, renewing, or scanning a subscription receipt records an FX-snapshotted ledger entry automatically.
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
<script nonce="{{ Vite::cspNonce() }}" src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" integrity="sha384-JUh163oCRItcbPme8pYnROHQMC6fNKTBWtRG3I3I0erJkzNgL7uxKlNwcrcFKeqF" crossorigin="anonymous"></script>
<script nonce="{{ Vite::cspNonce() }}">
(function () {
    const palette = ['#6366f1','#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#a855f7','#ec4899','#10b981','#3b82f6'];

    function isDark() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }
    function cc() {
        return {
            text:    isDark() ? '#8b949e' : '#6c757d',
            grid:    isDark() ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.07)',
            border:  isDark() ? '#161b22' : '#ffffff',
        };
    }

    let barChart = null, donutChart = null;

    function buildBar() {
        const c = cc();
        const ctx = document.getElementById('monthlyChart');
        if (!ctx) return;
        if (barChart) barChart.destroy();

        barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($monthly->pluck('month')),
                datasets: [{
                    label: 'Monthly Spend ({{ $currencySymbol ?? 'RM' }})',
                    data: @json($monthly->pluck('total')),
                    backgroundColor: 'rgba(99,102,241,0.8)',
                    borderColor: 'rgba(99,102,241,1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' {{ $currencySymbol ?? 'RM' }} ' + ctx.parsed.y.toFixed(2) } }
                },
                scales: {
                    x: { ticks: { color: c.text }, grid: { color: c.grid } },
                    y: {
                        beginAtZero: true,
                        ticks: { color: c.text, callback: v => '{{ $currencySymbol ?? 'RM' }} ' + v.toFixed(0) },
                        grid: { color: c.grid }
                    }
                }
            }
        });
    }

    @if($categoryBreakdown->isNotEmpty())
    function buildDonut() {
        const c = cc();
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;
        if (donutChart) donutChart.destroy();

        donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($categoryBreakdown->keys()->values()),
                datasets: [{
                    data: @json($categoryBreakdown->values()),
                    backgroundColor: palette.slice(0, {{ $categoryBreakdown->count() }}),
                    borderColor: c.border,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 }, color: c.text, boxWidth: 14 }
                    },
                    tooltip: { callbacks: { label: ctx => ' {{ $currencySymbol ?? 'RM' }} ' + ctx.parsed.toFixed(2) + '/yr' } }
                }
            }
        });
    }
    @endif

    // Initial render
    buildBar();
    @if($categoryBreakdown->isNotEmpty()) buildDonut(); @endif

    // Re-render on theme switch
    new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.attributeName === 'data-bs-theme') {
                buildBar();
                @if($categoryBreakdown->isNotEmpty()) buildDonut(); @endif
            }
        });
    }).observe(document.documentElement, { attributes: true });
})();
</script>
@endpush
