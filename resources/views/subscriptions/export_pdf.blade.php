<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Report – {{ now()->format('Y-m-d') }} – SubShield</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <style>
        :root {
            --bs-body-font-size: 0.875rem;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .report-page {
            max-width: 1140px;
            margin: 20px auto 40px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            padding: 32px 36px;
        }
        .stat-badge {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .table > :not(caption) > * > * {
            padding: 8px 10px;
        }
        .category-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #0f172a !important;
            }
            .report-page {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            @page {
                size: A4 landscape;
                margin: 10mm 12mm 10mm 12mm;
            }
            tr {
                page-break-inside: avoid !important;
            }
            .stat-kpi-col {
                flex: 0 0 auto !important;
                width: 25% !important;
            }
            .table-striped > tbody > tr:nth-of-type(odd) > * {
                background-color: #f8fafc !important;
            }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>

    {{-- Screen-only toolbar --}}
    <div class="no-print bg-dark text-white py-2 px-3 sticky-top shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                <span class="fw-semibold small">Subscription Portfolio PDF Report</span>
                <span class="badge bg-secondary">A4 Landscape Layout</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white-50 small d-none d-md-inline">Tip: In the print dialog, select <strong>"Save as PDF"</strong> as Destination.</span>
                <button type="button" id="printReportBtn" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-printer-fill me-1"></i>Print / Save as PDF
                </button>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="report-page">
        {{-- Report Header --}}
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4 flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="bg-primary text-white rounded px-2 py-1 fw-bold fs-6">
                        <i class="bi bi-shield-lock-fill me-1"></i>SubShield
                    </span>
                    <h1 class="h5 fw-bold mb-0 text-dark">Subscription Portfolio Report</h1>
                </div>
                <div class="text-muted small">Financial commitment audit and recurring renewal schedule</div>
            </div>
            <div class="text-end small">
                <div><span class="text-muted">Generated:</span> <strong>{{ now()->format('M d, Y') }} &bull; {{ now()->format('H:i') }}</strong></div>
                <div><span class="text-muted">Account:</span> <strong>{{ $user->name }}</strong> ({{ $user->email }})</div>
                <div><span class="text-muted">Base Currency:</span> <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $baseCurrency }}</span></div>
            </div>
        </div>

        {{-- Executive KPI summary cards --}}
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3 stat-kpi-col">
                <div class="stat-badge h-100">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Active Services</div>
                    <div class="h5 fw-bold mb-0 text-dark mt-1">{{ $activeCount }} <span class="text-muted small fw-normal">of {{ $subscriptions->count() }}</span></div>
                </div>
            </div>
            <div class="col-6 col-md-3 stat-kpi-col">
                <div class="stat-badge h-100">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Monthly Commitment</div>
                    <div class="h5 fw-bold mb-0 text-primary mt-1">{{ $currencySymbol }} {{ number_format($totalMonthly, 2) }} <span class="text-muted small fw-normal">/mo</span></div>
                </div>
            </div>
            <div class="col-6 col-md-3 stat-kpi-col">
                <div class="stat-badge h-100">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Annual Commitment</div>
                    <div class="h5 fw-bold mb-0 text-success mt-1">{{ $currencySymbol }} {{ number_format($totalYearly, 2) }} <span class="text-muted small fw-normal">/yr</span></div>
                </div>
            </div>
            <div class="col-6 col-md-3 stat-kpi-col">
                <div class="stat-badge h-100">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Average Monthly / Sub</div>
                    <div class="h5 fw-bold mb-0 text-dark mt-1">{{ $currencySymbol }} {{ number_format($avgMonthly, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Main subscriptions data table --}}
        <div class="mb-4">
            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-table text-primary me-1"></i>Subscription Schedule &amp; Financial Equivalents</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 0.8rem;">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th style="width: 30px;">#</th>
                            <th>Service Name</th>
                            <th>Category</th>
                            <th>Cycle</th>
                            <th class="text-end">Original Price</th>
                            <th class="text-end">{{ $baseCurrency }}/Mo</th>
                            <th class="text-end">{{ $baseCurrency }}/Yr</th>
                            <th>Next Renewal</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $i => $sub)
                            @php
                                $isInactive = $sub->status !== 'active';
                                $isTrial = (bool) $sub->is_trial;
                            @endphp
                            <tr class="{{ $isInactive ? 'text-muted bg-light' : '' }}">
                                <td class="text-muted text-center">{{ $i + 1 }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $sub->name }}</span>
                                    @if($sub->website_url)
                                        <div class="text-muted small" style="font-size: 0.7rem;">{{ parse_url($sub->website_url, PHP_URL_HOST) ?? $sub->website_url }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($sub->category)
                                        <span class="category-dot" style="background-color: {{ $sub->category->color }};"></span>
                                        <span>{{ $sub->category->name }}</span>
                                    @else
                                        <span class="text-muted">Uncategorised</span>
                                    @endif
                                </td>
                                <td>{{ $sub->billingCycleLabel() }}</td>
                                <td class="text-end font-monospace">
                                    {{ $sub->currency }} {{ number_format((float) $sub->amount, 2) }}
                                </td>
                                <td class="text-end fw-semibold text-dark font-monospace">
                                    {{ $currencySymbol }} {{ number_format($sub->monthlyAmountIn($baseCurrency), 2) }}
                                </td>
                                <td class="text-end text-muted font-monospace">
                                    {{ $currencySymbol }} {{ number_format($sub->yearlyAmountIn($baseCurrency), 2) }}
                                </td>
                                <td>
                                    @if($sub->next_billing_date)
                                        <div>{{ $sub->next_billing_date->format('M d, Y') }}</div>
                                        @if(!$isInactive && $sub->daysUntilRenewal() !== null && $sub->daysUntilRenewal() <= 7 && $sub->daysUntilRenewal() >= 0)
                                            <span class="badge bg-warning text-dark py-0" style="font-size: 0.65rem;">in {{ $sub->daysUntilRenewal() }}d</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isTrial)
                                        <span class="badge bg-info text-dark" style="font-size: 0.65rem;">TRIAL</span>
                                    @elseif($sub->status === 'active')
                                        <span class="badge bg-success" style="font-size: 0.65rem;">ACTIVE</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ strtoupper($sub->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No subscriptions found in this portfolio.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-uppercase text-dark">Portfolio Active Total ({{ $activeCount }} services)</td>
                            <td></td>
                            <td class="text-end text-primary font-monospace">{{ $currencySymbol }} {{ number_format($totalMonthly, 2) }}</td>
                            <td class="text-end text-success font-monospace">{{ $currencySymbol }} {{ number_format($totalYearly, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Category Spend Breakdown Section --}}
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-pie-chart text-success me-1"></i>Category Spend Allocation</h6>
                <table class="table table-bordered table-sm align-middle" style="font-size: 0.75rem;">
                    <thead class="table-light text-muted text-uppercase">
                        <tr>
                            <th>Category</th>
                            <th class="text-center">Subscriptions</th>
                            <th class="text-end">Monthly ({{ $baseCurrency }})</th>
                            <th class="text-end">Annual ({{ $baseCurrency }})</th>
                            <th style="width: 140px;">Share of Spend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryBreakdown as $cat)
                            <tr>
                                <td>
                                    <span class="category-dot" style="background-color: {{ $cat['color'] }};"></span>
                                    <span class="fw-semibold">{{ $cat['name'] }}</span>
                                </td>
                                <td class="text-center">{{ $cat['count'] }}</td>
                                <td class="text-end font-monospace">{{ $currencySymbol }} {{ number_format($cat['monthly'], 2) }}</td>
                                <td class="text-end font-monospace">{{ $currencySymbol }} {{ number_format($cat['yearly'], 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar" style="width: {{ $cat['share'] }}%; background-color: {{ $cat['color'] }};"></div>
                                        </div>
                                        <span class="small font-monospace" style="width: 36px; text-align: right;">{{ $cat['share'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 bg-light p-3 rounded-2 h-100">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-shield-check text-primary me-1"></i>Audit Metadata</h6>
                    <ul class="list-unstyled small mb-0 text-muted" style="font-size: 0.75rem; line-height: 1.6;">
                        <li><strong>Engine:</strong> SubShield Financial Intelligence</li>
                        <li><strong>Normalization:</strong> Live Daily ECB FX Feed</li>
                        <li><strong>Currency Base:</strong> {{ $baseCurrency }}</li>
                        <li><strong>Confidentiality:</strong> Internal Personal / Corporate Accounting Use Only</li>
                        <li><strong>Verification:</strong> Generated with server-side validation</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Report Footer --}}
        <div class="border-top pt-3 mt-4 d-flex justify-content-between align-items-center text-muted" style="font-size: 0.7rem;">
            <span>SubShield &bull; Proactive Subscription Defense &amp; Intelligence Platform</span>
            <span>Report Hash: {{ substr(hash('sha256', $user->id . now()->toDateString()), 0, 16) }}</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

    <script nonce="{{ Vite::cspNonce() }}">
        document.getElementById('printReportBtn')?.addEventListener('click', function () {
            window.print();
        });
    </script>
</body>
</html>
