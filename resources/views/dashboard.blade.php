@extends('layouts.app')

@section('title', 'Dashboard – SubShield')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold">Dashboard</h1>
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
        <a href="{{ route('subscriptions.create') }}#receiptScanner" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-medical me-1"></i>Scan Receipt / PDF
        </a>
        <a href="{{ route('subscriptions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Subscription
        </a>
    </div>
</div>

@if($totalActive === 0)
    {{-- New User Onboarding Welcome Card --}}
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #fff; border-radius: 16px;">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(99, 102, 241, 0.2); border: 1px solid rgba(99, 102, 241, 0.4); font-size: 0.82rem; font-weight: 600; color: #c7d2fe;">
                        <i class="bi bi-stars text-warning"></i> Quick-Start Onboarding Guide
                    </div>
                    <h2 class="h3 fw-bold text-white mb-2">Welcome to SubShield, {{ auth()->user()->name }}!</h2>
                    <p class="text-white-50 mb-4" style="max-width: 620px;">
                        Take full control of your recurring commitments in seconds. Upload an invoice to let AI extract the details, add your first subscription manually, or set your monthly budget threshold.
                    </p>
                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <a href="{{ route('subscriptions.create') }}#receiptScanner" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold">
                                <i class="bi bi-file-earmark-medical"></i> AI Receipt Scan
                            </a>
                        </div>
                        <div class="col-12 col-sm-4">
                            <a href="{{ route('subscriptions.create') }}" class="btn btn-outline-light w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold">
                                <i class="bi bi-plus-circle"></i> Add Manually
                            </a>
                        </div>
                        <div class="col-12 col-sm-4">
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-light w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold">
                                <i class="bi bi-sliders"></i> Set Budget Cap
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 d-none d-lg-block text-center">
                    <div class="p-4 rounded-4" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <i class="bi bi-shield-check text-success display-4 mb-2 d-block"></i>
                        <div class="fw-bold text-white">Zero Bank Logins</div>
                        <small class="text-white-50">100% private &amp; client-controlled. Your financial credentials never leave your custody.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">MONTHLY SPEND</span>
                    <span class="bg-primary bg-opacity-10 text-primary rounded-2 p-2"><i class="bi bi-calendar-month fs-5"></i></span>
                </div>
                <div class="fs-3 fw-bold mb-0">{{ $currencySymbol ?? 'RM' }} {{ number_format($totalMonthly ?? $totalMonthlyMyr, 2) }}</div>
                <small class="text-muted">per month ({{ $baseCurrency ?? 'MYR' }})</small>
                @php $budgetInfo = $intelligence['budget'] ?? null; @endphp
                @if($budgetInfo && $budgetInfo['monthly_budget'] !== null)
                    @php
                        $pct = min(100, (float) $budgetInfo['utilization_percent']);
                        $barClass = $budgetInfo['status'] === 'exceeded' ? 'bg-danger' : ($budgetInfo['status'] === 'warning' ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="mt-2">
                        <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.72rem;">
                            <span>Budget: {{ $currencySymbol ?? 'RM' }} {{ number_format($budgetInfo['monthly_budget'], 0) }}</span>
                            <span class="fw-semibold">{{ $budgetInfo['utilization_percent'] }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%"
                                 aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Budget Utilization"></div>
                        </div>
                    </div>
                @endif
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
                <div class="fs-3 fw-bold mb-0">{{ $currencySymbol ?? 'RM' }} {{ number_format($totalYearly ?? $totalYearlyMyr, 2) }}</div>
                <small class="text-muted">per year ({{ $baseCurrency ?? 'MYR' }})</small>
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
                <div class="fs-3 fw-bold mb-0">{{ $totalActive }}</div>
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
                <div class="fs-3 fw-bold mb-0">{{ $dueSoon->count() }}</div>
                <small class="text-muted">renewals in 7 days</small>
            </div>
        </div>
    </div>
</div>

{{-- SubShield Financial Intelligence & Health Score --}}
@php
    $healthScore = (int) ($intelligence['health_score'] ?? 100);
    $healthGrade = $intelligence['health_grade'] ?? 'Excellent';
    $healthColor = $healthScore >= 85 ? 'success' : ($healthScore >= 70 ? 'primary' : ($healthScore >= 50 ? 'warning' : 'danger'));
    $alerts = $intelligence['alerts'] ?? collect();
    $overlaps = $intelligence['category_overlaps'] ?? collect();
    $spikes = $intelligence['renewal_spikes'] ?? collect();
@endphp
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-shield-check text-primary me-2"></i>Optimization Score</h2>
                <span class="badge bg-{{ $healthColor }}">{{ $healthGrade }}</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="display-6 fw-bold text-{{ $healthColor }}">{{ $healthScore }}</span>
                    <span class="text-muted small">/ 100</span>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-{{ $healthColor }}" style="width: {{ $healthScore }}%"
                         aria-valuenow="{{ $healthScore }}" aria-valuemin="0" aria-valuemax="100" aria-label="Health Score"></div>
                </div>
                @if(!empty($intelligence['score_factors']))
                    <ul class="list-unstyled small mb-2">
                        @foreach($intelligence['score_factors'] as $factor)
                            <li class="d-flex justify-content-between text-muted mb-1">
                                <span><i class="bi bi-dot"></i>{{ $factor['label'] }}</span>
                                <span class="text-danger fw-semibold">{{ $factor['impact'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="small text-muted mb-2"><i class="bi bi-check-circle text-success me-1"></i>Zero budget breaches, trial shocks, or redundant plans detected.</p>
                @endif
                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                    <a href="{{ route('settings.index') }}" class="text-decoration-none">Configure Budget Cap</a>
                    <a href="{{ route('expenditure.index') }}" class="text-decoration-none">Spike Forecast <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Proactive Financial Insights</h2>
                <div class="d-flex gap-2">
                    @if($overlaps->isNotEmpty())
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $overlaps->count() }} Overlap(s)</span>
                    @endif
                    @if($spikes->isNotEmpty())
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $spikes->count() }} Cashflow Spike(s)</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($alerts as $alert)
                    @php
                        $sevClass = match($alert->severity) {
                            'critical' => 'danger',
                            'warning' => 'warning',
                            default => 'info',
                        };
                    @endphp
                    <div class="d-flex align-items-start justify-content-between gap-2 px-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-{{ $sevClass }} bg-opacity-10 text-{{ $sevClass }} border border-{{ $sevClass }} border-opacity-25 text-uppercase" style="font-size: 0.65rem;">
                                    {{ $alert->severity }}
                                </span>
                                <span class="fw-semibold small">{{ $alert->title }}</span>
                            </div>
                            <p class="text-muted small mb-0">{{ $alert->message }}</p>
                        </div>
                        @if($alert->id)
                            <div class="d-flex gap-1 flex-shrink-0">
                                <form action="{{ route('insights.dismiss', $alert) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-muted p-1" title="Dismiss insight">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-stars fs-4 d-block mb-1 text-success"></i>
                        All clear — no active price hikes, budget breaches, or trial shocks right now.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Category breakdown doughnut --}}
    <div class="col-12 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Monthly by Category</h2>
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
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-alarm text-warning me-2"></i>Upcoming Renewals (next 7 days)</h2>
            </div>
            <div class="card-body p-0">
                @forelse($dueSoon as $sub)
                    <div class="d-flex align-items-center px-3 py-3 border-bottom">
                        <div class="me-3">
                            <x-brand-logo :subscription="$sub" :size="38" rounded="rounded-2" />
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">
                                {{ $sub->name }}
                                @if($sub->is_trial)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;"><i class="bi bi-gift me-1"></i>Trial</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $sub->next_billing_date->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold text-success">{{ $currencySymbol ?? 'RM' }} {{ number_format($sub->effectiveAmountIn($baseCurrency ?? 'MYR'), 2) }}</div>
                            @if($sub->currency !== ($baseCurrency ?? 'MYR'))
                                <small class="text-muted">{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</small>
                            @endif
                            @php $days = $sub->daysUntilRenewal(); @endphp
                            <div><small class="{{ $days <= 1 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $days === 0 ? 'Today!' : ($days === 1 ? 'Tomorrow' : "in {$days} days") }}
                            </small></div>
                        </div>
                        <form action="{{ route('subscriptions.renew', $sub) }}" method="POST" class="ms-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2" title="Mark as Paid & Advance Cycle">
                                <i class="bi bi-check2"></i> <span class="d-none d-sm-inline">Paid</span>
                            </button>
                        </form>
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
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-list-check text-primary me-2"></i>Active Subscriptions</h2>
            <small class="text-muted">All amounts normalised to {{ $baseCurrency ?? 'MYR' }}</small>
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
                    <th>{{ $baseCurrency ?? 'MYR' }}</th>
                    <th class="pe-3">Next Bill</th>
                </tr></thead>
                <tbody>
                    @forelse($subscriptions->take(8) as $sub)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <x-brand-logo :subscription="$sub" :size="30" rounded="rounded-2" />
                                    <span class="fw-semibold">{{ $sub->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($sub->category)
                                    <span class="badge rounded-pill" style="background: {{ $sub->category->color }}25; color: #f8fafc; border: 1px solid {{ $sub->category->color }}55; font-weight: 600;">{{ $sub->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</td>
                            <td class="fw-semibold text-success">{{ $currencySymbol ?? 'RM' }} {{ number_format($sub->effectiveAmountIn($baseCurrency ?? 'MYR'), 2) }}</td>
                            <td class="pe-3">{{ $sub->next_billing_date->format('M d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-2">
                                    <i class="bi bi-inbox text-muted display-6 d-block mb-2"></i>
                                    <div class="fw-semibold text-secondary mb-1">No active subscriptions registered yet</div>
                                    <p class="text-muted small mb-3">Add recurring services manually or drop an invoice for automated AI extraction.</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('subscriptions.create') }}#receiptScanner" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark-medical me-1"></i>Scan Receipt with AI
                                        </a>
                                        <a href="{{ route('subscriptions.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>Add Manually
                                        </a>
                                    </div>
                                </div>
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
<script nonce="{{ Vite::cspNonce() }}" src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" integrity="sha384-JUh163oCRItcbPme8pYnROHQMC6fNKTBWtRG3I3I0erJkzNgL7uxKlNwcrcFKeqF" crossorigin="anonymous"></script>
<script nonce="{{ Vite::cspNonce() }}">
(function() {
    const palette = ['#6366f1','#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#a855f7','#ec4899','#10b981','#3b82f6'];

    function isDark() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    function chartColors() {
        return {
            text:    isDark() ? '#8b949e' : '#6c757d',
            border:  isDark() ? '#161b22' : '#ffffff',
        };
    }

    let donutChart = null;

    function buildDonut() {
        const c = chartColors();
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
                    tooltip: {
                        callbacks: { label: ctx => ' {{ $currencySymbol ?? 'RM' }} ' + ctx.parsed.toFixed(2) + '/mo' }
                    }
                }
            }
        });
    }

    buildDonut();

    // Rebuild chart when theme switches
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.attributeName === 'data-bs-theme') buildDonut();
        });
    });
    observer.observe(document.documentElement, { attributes: true });
})();
</script>
@endif
@endpush
