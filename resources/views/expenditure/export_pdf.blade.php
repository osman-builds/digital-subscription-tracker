<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenditure Report {{ $year }} – {{ now()->format('Y-m-d') }} – SubShield</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                <span class="fw-semibold small">Annual Expenditure PDF Report ({{ $year }})</span>
                <span class="badge bg-secondary">A4 Landscape Layout</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white-50 small d-none d-md-inline">Tip: In the print dialog, select <strong>"Save as PDF"</strong> as Destination.</span>
                <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i>Print / Save as PDF
                </button>
                <a href="{{ route('expenditure.index', ['year' => $year]) }}" class="btn btn-outline-light btn-sm">
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
                    <h1 class="h5 fw-bold mb-0 text-dark">Annual Expenditure &amp; Billing Audit ({{ $year }})</h1>
                </div>
                <div class="text-muted small">Historical spend analysis, category distribution, and immutable FX ledger</div>
            </div>
            <div class="text-end small">
                <div><span class="text-muted">Generated:</span> <strong>{{ now()->format('M d, Y') }} &bull; {{ now()->format('H:i') }}</strong></div>
                <div><span class="text-muted">Account:</span> <strong>{{ $user->name }}</strong> ({{ $user->email }})</div>
                <div><span class="text-muted">Base Currency:</span> <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $baseCurrency }}</span></div>
            </div>
        </div>

        {{-- Executive KPI summary cards --}}
        <div class="row g-2 mb-4">
            <div class="col-3">
                <div class="stat-badge">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Yearly Spend Total</div>
                    <div class="h5 fw-bold mb-0 text-primary mt-1">{{ $currencySymbol }} {{ number_format($yearlyTotal, 2) }}</div>
                </div>
            </div>
            <div class="col-3">
                <div class="stat-badge">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Average Monthly</div>
                    <div class="h5 fw-bold mb-0 text-dark mt-1">{{ $currencySymbol }} {{ number_format($yearlyTotal / 12, 2) }} <span class="text-muted small fw-normal">/mo</span></div>
                </div>
            </div>
            <div class="col-3">
                <div class="stat-badge">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Average Weekly</div>
                    <div class="h5 fw-bold mb-0 text-dark mt-1">{{ $currencySymbol }} {{ number_format($yearlyTotal / 52, 2) }} <span class="text-muted small fw-normal">/wk</span></div>
                </div>
            </div>
            <div class="col-3">
                <div class="stat-badge">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">Average Daily</div>
                    <div class="h5 fw-bold mb-0 text-dark mt-1">{{ $currencySymbol }} {{ number_format($yearlyTotal / 365, 2) }} <span class="text-muted small fw-normal">/day</span></div>
                </div>
            </div>
        </div>

        {{-- Side-by-side Tables: Monthly Breakdown and Category Breakdown --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-calendar3 text-primary me-1"></i>Monthly Spend Breakdown ({{ $year }})</h6>
                <table class="table table-bordered table-striped table-sm align-middle" style="font-size: 0.8rem;">
                    <thead class="table-light text-muted text-uppercase">
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Spend ({{ $baseCurrency }})</th>
                            <th style="width: 140px;">Share of Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthly as $m)
                            @php
                                $share = $yearlyTotal > 0 ? round(($m['total'] / $yearlyTotal) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $m['month'] }} {{ $year }}</td>
                                <td class="text-end font-monospace">{{ $currencySymbol }} {{ number_format($m['total'], 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-primary" style="width: {{ min(100, $share * 3) }}%;"></div>
                                        </div>
                                        <span class="small font-monospace" style="width: 38px; text-align: right;">{{ $share }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total (12 Months)</td>
                            <td class="text-end text-primary font-monospace">{{ $currencySymbol }} {{ number_format($yearlyTotal, 2) }}</td>
                            <td>100.0%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="col-12 col-md-6">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-pie-chart text-success me-1"></i>Yearly Category Distribution</h6>
                <table class="table table-bordered table-striped table-sm align-middle" style="font-size: 0.8rem;">
                    <thead class="table-light text-muted text-uppercase">
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Annual Spend ({{ $baseCurrency }})</th>
                            <th style="width: 140px;">Share of Spend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoryBreakdown as $catName => $catTotal)
                            @php
                                $catShare = $yearlyTotal > 0 ? round(($catTotal / $yearlyTotal) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $catName }}</td>
                                <td class="text-end font-monospace">{{ $currencySymbol }} {{ number_format($catTotal, 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $catShare }}%;"></div>
                                        </div>
                                        <span class="small font-monospace" style="width: 38px; text-align: right;">{{ $catShare }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No category data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Annual Cost per Subscription Table --}}
        <div class="mb-4">
            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-table text-primary me-1"></i>Annual Commitment per Subscription</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 0.8rem;">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Subscription</th>
                            <th>Category</th>
                            <th>Cycle</th>
                            <th class="text-end">Original Price</th>
                            <th class="text-end">{{ $baseCurrency }}/Mo</th>
                            <th class="text-end">{{ $baseCurrency }}/Yr</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptionYearly as $i => $sub)
                            <tr>
                                <td class="text-muted text-center">{{ $i + 1 }}</td>
                                <td class="fw-semibold text-dark">{{ $sub->name }}</td>
                                <td>{{ $sub->category->name ?? 'Uncategorised' }}</td>
                                <td>{{ $sub->billingCycleLabel() }}</td>
                                <td class="text-end font-monospace">{{ $sub->currency }} {{ number_format((float) $sub->amount, 2) }}</td>
                                <td class="text-end font-monospace">{{ $currencySymbol }} {{ number_format($sub->monthlyAmountIn($baseCurrency), 2) }}</td>
                                <td class="text-end fw-semibold text-dark font-monospace">{{ $currencySymbol }} {{ number_format($sub->yearlyAmountIn($baseCurrency), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">No active subscriptions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Billing Ledger Audit Trail --}}
        @if($billingEvents->isNotEmpty())
            <div class="mb-3">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-receipt text-primary me-1"></i>Recent Billing Ledger Events (FX Snapshots)</h6>
                <table class="table table-bordered table-sm align-middle" style="font-size: 0.75rem;">
                    <thead class="table-light text-muted text-uppercase">
                        <tr>
                            <th>Billed Date</th>
                            <th>Subscription</th>
                            <th class="text-end">Original Amount</th>
                            <th class="text-end">FX Rate Snapshot</th>
                            <th class="text-end">Recorded Base ({{ $baseCurrency }})</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingEvents as $ev)
                            <tr>
                                <td class="text-muted">{{ $ev->billed_at?->format('M d, Y') }}</td>
                                <td class="fw-semibold">{{ $ev->subscription?->name ?? 'Subscription' }}</td>
                                <td class="text-end font-monospace">{{ $ev->currency }} {{ number_format((float) $ev->amount, 2) }}</td>
                                <td class="text-end font-monospace">{{ number_format((float) $ev->fx_rate_snapshot, 4) }}</td>
                                <td class="text-end font-monospace fw-semibold text-success">{{ $ev->base_currency }} {{ number_format((float) $ev->amount_base, 2) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $ev->source) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Report Footer --}}
        <div class="border-top pt-3 mt-4 d-flex justify-content-between align-items-center text-muted" style="font-size: 0.7rem;">
            <span>SubShield &bull; Proactive Subscription Defense &amp; Financial Intelligence</span>
            <span>Report Hash: {{ substr(hash('sha256', $user->id . $year . now()->toDateString()), 0, 16) }}</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

</body>
</html>
