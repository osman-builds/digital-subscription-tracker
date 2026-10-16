@extends('layouts.app')

@section('title', 'Expenditure – SubShield')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Expenditure Tracker</h4>
        <small class="text-muted">All amounts in Malaysian Ringgit (MYR)</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($fxUpdatedAt)
            <span class="badge bg-light text-muted border">
                <i class="bi bi-currency-exchange me-1"></i>FX {{ \Carbon\Carbon::parse($fxUpdatedAt)->diffForHumans() }}
            </span>
        @endif
        {{-- Year selector --}}
        <form method="GET" action="{{ route('expenditure.index') }}" class="d-flex gap-2">
            <select name="year" class="form-select form-select-sm" style="width:100px" onchange="this.form.submit()">
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
                <h3 class="fw-bold mb-0">RM {{ number_format($yearlyTotal, 2) }}</h3>
                <small class="text-muted">{{ $year }}</small>
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
                <h3 class="fw-bold mb-0">RM {{ number_format($yearlyTotal / 12, 2) }}</h3>
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
                <h3 class="fw-bold mb-0">RM {{ number_format($yearlyTotal / 52, 2) }}</h3>
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
                <h3 class="fw-bold mb-0">RM {{ number_format($yearlyTotal / 365, 2) }}</h3>
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
                <canvas id="monthlyChart" style="height:280px;"></canvas>
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
                        <th>MYR / Month</th>
                        <th class="pe-4">MYR / Year</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptionYearly as $i => $sub)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $sub->name }}</td>
                            <td>
                                @if($sub->category)
                                    <span class="badge rounded-pill" style="background:{{ $sub->category->color }};">{{ $sub->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $sub->billingCycleLabel() }}</span></td>
                            <td>{{ $sub->currency }} {{ number_format($sub->amount, 2) }}</td>
                            <td class="text-success fw-semibold">RM {{ number_format($sub->monthlyAmountMyr(), 2) }}</td>
                            <td class="pe-4 text-primary fw-bold">RM {{ number_format($sub->yearlyAmountMyr(), 2) }}</td>
                        </tr>
                    @endforeach
                    @if($subscriptionYearly->isNotEmpty())
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="ps-4 text-end">TOTAL</td>
                            <td class="text-success">RM {{ number_format($subscriptionYearly->sum(fn($s) => $s->monthlyAmountMyr()), 2) }}</td>
                            <td class="pe-4 text-primary">RM {{ number_format($yearlyTotal, 2) }}</td>
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
@endsection

@push('scripts')
<script>
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
                labels: {!! json_encode($monthly->pluck('month')) !!},
                datasets: [{
                    label: 'Monthly Spend (RM)',
                    data: {!! json_encode($monthly->pluck('total')) !!},
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
                    tooltip: { callbacks: { label: ctx => ' RM ' + ctx.parsed.y.toFixed(2) } }
                },
                scales: {
                    x: { ticks: { color: c.text }, grid: { color: c.grid } },
                    y: {
                        beginAtZero: true,
                        ticks: { color: c.text, callback: v => 'RM ' + v.toFixed(0) },
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
                labels: {!! json_encode($categoryBreakdown->keys()->values()) !!},
                datasets: [{
                    data: {!! json_encode($categoryBreakdown->values()) !!},
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
                    tooltip: { callbacks: { label: ctx => ' RM ' + ctx.parsed.toFixed(2) + '/yr' } }
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
