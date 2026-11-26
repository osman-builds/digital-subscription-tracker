{{-- ── Core Intelligence Features (Bento Grid) ─────────────────────────── --}}
<section class="section-wrap" id="intelligence">
    <div id="features" style="position: relative; top: -85px; visibility: hidden;"></div>
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-20 text-indigo-light px-3 py-2 mb-3 fw-bold" style="border-radius: 100px;">
                Platform Capabilities
            </span>
            <h2 class="section-title">Engineered for Transparency. Zero Compromise on Privacy.</h2>
            <p class="section-lead mx-auto" style="max-width: 650px;">
                Everything you need to track, forecast, audit, and optimize multi-currency subscriptions without ever exposing bank credentials or card numbers.
            </p>
        </div>

        <div class="row g-4">
            {{-- Bento 1: Multimodal AI Scanner --}}
            <div class="col-12 col-md-7">
                <div class="bento-card">
                    <div class="bento-icon bg-primary bg-opacity-15 text-primary">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h3 class="fw-bold text-white mb-0 fs-5">Multimodal AI Receipt &amp; Invoice Scanner</h3>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 small">Gemini Powered</span>
                    </div>
                    <p class="bento-desc mb-3">
                        Snap a photo or upload a PDF invoice. Our multimodal AI extracts service name, billing frequency, currency, and renewal dates in volatile RAM—never persisting your receipt images or personal card details to disk.
                    </p>
                    <div class="p-3 rounded-3" style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25);">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                            <span class="text-white"><i class="bi bi-cpu text-primary me-1"></i>Zero-Disk Memory Processing</span>
                            <span class="text-white"><i class="bi bi-fingerprint text-info me-1"></i>SHA-256 Duplicate Protection</span>
                            <span class="text-white"><i class="bi bi-person-check text-success me-1"></i>Human-in-the-Loop Audit</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bento 2: Financial Health Score --}}
            <div class="col-12 col-md-5">
                <div class="bento-card">
                    <div class="bento-icon bg-success bg-opacity-15 text-success">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">0–100 Optimization Health Score</h3>
                    <p class="bento-desc mb-3">
                        SubShield constantly audits your portfolio across 5 critical dimensions: budget guardrails, unannounced price hikes, expiring trials, and redundant category overlaps.
                    </p>
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 px-3 py-2">88 / 100 Good</span>
                        <span class="text-muted small">Dynamic actionable deductions</span>
                    </div>
                </div>
            </div>

            {{-- Bento 3: Price Creep Defense --}}
            <div class="col-12 col-md-5">
                <div class="bento-card">
                    <div class="bento-icon bg-danger bg-opacity-15 text-danger">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Stealth Price Creep Tracker</h3>
                    <p class="bento-desc mb-0">
                        When Netflix or Spotify quietly raises rates, SubShield automatically compares billing events across cycles, calculating your exact annualized inflation impact (+RM 80.10/yr).
                    </p>
                </div>
            </div>

            {{-- Bento 4: 12-Month Spike Forecasting --}}
            <div class="col-12 col-md-7">
                <div class="bento-card">
                    <div class="bento-icon bg-warning bg-opacity-15 text-warning">
                        <i class="bi bi-calendar2-range-fill"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">12-Month Renewal Spike Forecasting</h3>
                    <p class="bento-desc mb-3">
                        Never suffer from year-end cashflow shock. SubShield models irregular annual, semi-annual, and quarterly renewals to flag months with spend spikes exceeding 1.5&times; your baseline.
                    </p>
                    <div class="p-2 px-3 rounded-2" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25);">
                        <span class="small text-white"><i class="bi bi-exclamation-triangle text-warning me-1"></i>Spike Forecast: JetBrains annual renewal will increase Month 3 cashflow by 3.2&times;.</span>
                    </div>
                </div>
            </div>

            {{-- Bento 5: Currency Normalization --}}
            <div class="col-12 col-md-6">
                <div class="bento-card">
                    <div class="bento-icon bg-info bg-opacity-15 text-info">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Live Cross-Currency Normalisation</h3>
                    <p class="bento-desc mb-3">
                        Subscribed to Canva in SGD, Adobe in USD, and Spotify in EUR? Daily European Central Bank exchange rates convert everything automatically into your primary base currency.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['USD ($)', 'EUR (€)', 'GBP (£)', 'MYR (RM)', 'SGD (S$)', 'JPY (¥)', 'AUD (A$)'] as $curr)
                            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 px-2 py-1 small">
                                {{ $curr }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bento 6: Executive CSV & PDF Exports --}}
            <div class="col-12 col-md-6">
                <div class="bento-card">
                    <div class="bento-icon bg-primary bg-opacity-15 text-indigo-light">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Executive CSV &amp; A4 PDF Reports</h3>
                    <p class="bento-desc mb-3">
                        Export arranged, formula-sanitized CSV spreadsheets with category breakdowns, or generate executive A4 landscape PDF audit reports for personal accounting and business expense claims in 1 click.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25"><i class="bi bi-filetype-csv me-1"></i>Structured CSV</span>
                        <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25"><i class="bi bi-filetype-pdf me-1"></i>Printable A4 PDF</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
