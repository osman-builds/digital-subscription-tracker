@extends('settings.layout')

@section('settings_content')
<div class="d-flex flex-column gap-4">

    {{-- 1. SUBSHIELD IDENTITY & OVERVIEW BANNER --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="d-flex align-items-center justify-content-between p-4 about-banner">
                <div class="d-flex align-items-center gap-3">
                    <img src="/favicon.svg" width="52" height="52" alt="SubShield icon">
                    <div>
                        <h4 class="mb-0 fw-bold text-white">SubShield</h4>
                        <span class="text-white-50 small">Digital Subscription & Renewal Manager</span>
                    </div>
                </div>
                <span class="badge bg-success bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-2">
                    <i class="bi bi-shield-fill-check me-1"></i>Production Grade
                </span>
            </div>

            <div class="p-4">
                <p class="text-muted mb-0 lead fs-6">
                    SubShield is an engineering-first, privacy-preserving digital subscription tracking platform. It empowers users to monitor recurring software, cloud, and entertainment subscriptions across multiple world currencies with automated FX normalization and proactive renewal notifications.
                </p>
            </div>
        </div>
    </div>

    {{-- 2. FOUR ARCHITECTURAL GUARANTEES --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-shield-lock-fill text-primary me-2"></i>Engineering & Privacy Guarantees
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0">
                            <i class="bi bi-shield-x fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">Zero Credential Scraping</div>
                            <p class="text-muted small mb-0">No banking credentials, credit card numbers, or third-party financial logins are ever requested or stored.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-success bg-opacity-10 text-success flex-shrink-0">
                            <i class="bi bi-lock-fill fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">End-to-End Edge Encryption</div>
                            <p class="text-muted small mb-0">All traffic is secured via Cloudflare Anycast CDN with enforced TLS 1.3 and HSTS preloading headers.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning flex-shrink-0">
                            <i class="bi bi-currency-exchange fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">Real-Time FX Normalization</div>
                            <p class="text-muted small mb-0">Multi-currency subscriptions automatically convert using fresh market rates cached and validated daily.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-danger bg-opacity-10 text-danger flex-shrink-0">
                            <i class="bi bi-trash3-fill fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">Data Sovereignty Guarantee</div>
                            <p class="text-muted small mb-0">Instant, complete, cascading database purge upon account closure. Your data remains strictly yours.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SYSTEM & ENGINE TELEMETRY --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-activity text-secondary me-2"></i>System & Engine Telemetry
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block mb-1">Base Currency Preference</small>
                        <div class="fw-bold fs-6">
                            {{ $settings->base_currency }} ({{ app(\App\Services\FxRateService::class)->symbol($settings->base_currency) }})
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block mb-1">FX Rates Engine Status</small>
                        @php $fxTs = cache('fx_rates_myr_updated_at'); @endphp
                        <div class="fw-bold fs-6 text-success">
                            <i class="bi bi-check2-circle me-1"></i>{{ $fxTs ? \Carbon\Carbon::parse($fxTs)->diffForHumans() : 'Live Synced' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block mb-1">Origin Network Protection</small>
                        <div class="fw-bold fs-6 text-primary">
                            <i class="bi bi-cloud-check-fill me-1"></i>Isolated via NSG & WAF
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. SECURITY & RESPONSIBLE DISCLOSURE --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold mb-1">Security & Responsible Disclosure</h6>
                    <p class="text-muted small mb-0">
                        SubShield adheres to strict vulnerability disclosure practices under RFC 9116.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="mailto:support@mysubshield.me" class="btn btn-outline-primary px-3 py-2 fw-semibold">
                        <i class="bi bi-envelope me-1"></i>support@mysubshield.me
                    </a>
                    <a href="/.well-known/security.txt" class="btn btn-outline-secondary px-3 py-2 fw-semibold" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-text me-1"></i>security.txt
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
