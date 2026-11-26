@extends('layouts.app')

@section('title', 'Dashboard – SubShield')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold">Dashboard</h1>
        <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
        @if($fxUpdatedAt)
            <span class="badge bg-light text-muted border">
                <i class="bi bi-currency-exchange me-1"></i>FX updated {{ \Carbon\Carbon::parse($fxUpdatedAt)->diffForHumans() }}
            </span>
        @else
            <span class="badge bg-warning text-dark border">
                <i class="bi bi-exclamation-triangle me-1"></i>Using fallback FX rates
            </span>
        @endif
        <a href="{{ route('subscriptions.create') }}#receiptScanner" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-medical me-1"></i>Scan Receipt / PDF
        </a>
        <a href="{{ route('subscriptions.create') }}" class="btn btn-primary btn-sm">
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
<x-dashboard.stat-cards
    :currency-symbol="$currencySymbol ?? 'RM'"
    :base-currency="$baseCurrency ?? 'MYR'"
    :total-monthly="$totalMonthly ?? $totalMonthlyMyr"
    :total-yearly="$totalYearly ?? $totalYearlyMyr"
    :total-active="$totalActive"
    :due-soon-count="$dueSoon->count()"
    :budget-info="$intelligence['budget'] ?? null"
/>

{{-- SubShield Financial Intelligence & Health Score --}}
<x-dashboard.health-panel :intelligence="$intelligence ?? []" />

<div class="row g-3 mb-3">
    {{-- Category breakdown doughnut --}}
    <div class="col-12 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Monthly by Category</h2>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($categoryBreakdown->isNotEmpty())
                    <div style="position: relative; width: 100%; max-width: 260px; margin: 0 auto;">
                        <canvas id="categoryChart" style="max-height:230px;"></canvas>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No data yet</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Due Soon --}}
    <div class="col-12 col-lg-8">
        <x-dashboard.upcoming-renewals
            :due-soon="$dueSoon"
            :currency-symbol="$currencySymbol ?? 'RM'"
            :base-currency="$baseCurrency ?? 'MYR'"
        />
    </div>
</div>

{{-- Active subscriptions table --}}
<div class="card stat-card">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-start flex-wrap gap-2">
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
