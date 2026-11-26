@props(['intelligence' => []])

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
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
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
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top small">
                    <a href="{{ route('settings.index') }}" class="text-decoration-none">Configure Budget Cap</a>
                    <a href="{{ route('expenditure.index') }}" class="text-decoration-none">Spike Forecast <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card stat-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Proactive Financial Insights</h2>
                <div class="d-flex flex-wrap gap-2">
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
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
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
