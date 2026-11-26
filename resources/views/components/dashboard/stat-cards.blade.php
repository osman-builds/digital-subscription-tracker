@props([
    'currencySymbol' => 'RM',
    'baseCurrency' => 'MYR',
    'totalMonthly' => 0,
    'totalYearly' => 0,
    'totalActive' => 0,
    'dueSoonCount' => 0,
    'budgetInfo' => null,
])

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">MONTHLY SPEND</span>
                    <span class="bg-primary bg-opacity-10 text-primary rounded-2 p-2"><i class="bi bi-calendar-month fs-5"></i></span>
                </div>
                <div class="fs-3 fw-bold mb-0">{{ $currencySymbol }} {{ number_format((float) $totalMonthly, 2) }}</div>
                <small class="text-muted">per month ({{ $baseCurrency }})</small>
                @if($budgetInfo && $budgetInfo['monthly_budget'] !== null)
                    @php
                        $pct = min(100, (float) $budgetInfo['utilization_percent']);
                        $barClass = $budgetInfo['status'] === 'exceeded' ? 'bg-danger' : ($budgetInfo['status'] === 'warning' ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="mt-2">
                        <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.72rem;">
                            <span>Budget: {{ $currencySymbol }} {{ number_format((float) $budgetInfo['monthly_budget'], 0) }}</span>
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
                <div class="fs-3 fw-bold mb-0">{{ $currencySymbol }} {{ number_format((float) $totalYearly, 2) }}</div>
                <small class="text-muted">per year ({{ $baseCurrency }})</small>
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
                <div class="fs-3 fw-bold mb-0">{{ $dueSoonCount }}</div>
                <small class="text-muted">renewals in 7 days</small>
            </div>
        </div>
    </div>
</div>
