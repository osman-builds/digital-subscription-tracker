@extends('settings.layout')

@section('settings_content')
<div class="d-flex flex-column gap-4">

    {{-- 1. SUBSHIELD OVERVIEW --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-4 about-banner">
                <div class="d-flex align-items-center gap-3">
                    <img src="/favicon.svg" width="52" height="52" alt="SubShield icon">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="mb-0 fw-bold text-white">SubShield</h4>
                            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 small">v{{ config('app.version', '1.0.0') }}</span>
                        </div>
                        <span class="text-white-50 small">Digital Subscription &amp; Renewal Manager</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <p class="text-muted mb-0 lead fs-6">
                    SubShield helps you track recurring software, cloud, and entertainment subscriptions across currencies with automatic exchange rate conversion, renewal reminders, and spending insights.
                </p>
            </div>
        </div>
    </div>

    {{-- 2. CORE PRINCIPLES --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-stars text-primary me-2"></i>Built for Clarity &amp; Privacy
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0">
                            <i class="bi bi-shield-check fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">No Bank Logins Required</div>
                            <p class="text-muted small mb-0">Track every subscription without linking bank accounts or sharing card numbers.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning flex-shrink-0">
                            <i class="bi bi-currency-exchange fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">Automatic Multi-Currency Conversion</div>
                            <p class="text-muted small mb-0">Enter bills in their original currency and view unified monthly and annual totals in your preferred currency.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-success bg-opacity-10 text-success flex-shrink-0">
                            <i class="bi bi-bell fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">Proactive Renewal Alerts</div>
                            <p class="text-muted small mb-0">Get ahead of upcoming renewals, expiring free trials, and unexpected price increases.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary h-100">
                        <span class="p-2 rounded-2 bg-danger bg-opacity-10 text-danger flex-shrink-0">
                            <i class="bi bi-trash3-fill fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-semibold mb-1">Full Control of Your Data</div>
                            <p class="text-muted small mb-0">Export your records anytime or permanently delete your account and all data in one step.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. CURRENCY & FX STATUS --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-currency-exchange text-secondary me-2"></i>Currency Status
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block mb-1">Base Currency</small>
                        <div class="fw-bold fs-6">
                            {{ $settings->base_currency }} ({{ app(\App\Services\FxRateService::class)->symbol($settings->base_currency) }})
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block mb-1">Exchange Rates</small>
                        @php
                            $fxService = app(\App\Services\FxRateService::class);
                            $fxTs = $fxService->lastUpdated();
                            $isFallback = $fxService->isFallback();
                        @endphp
                        <div class="fw-bold fs-6 {{ $isFallback ? 'text-warning' : 'text-success' }}">
                            <i class="bi {{ $isFallback ? 'bi-exclamation-triangle-fill' : 'bi-check2-circle' }} me-1"></i>
                            {{ $isFallback ? 'Offline Fallback Rates' : ($fxTs ? 'Updated ' . \Carbon\Carbon::parse($fxTs)->diffForHumans() : 'Up to date') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. SUPPORT --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold mb-1">Support &amp; Feedback</h6>
                    <p class="text-muted small mb-0">
                        Questions, suggestions, or issues? Reach out anytime.
                    </p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="mailto:support@mysubshield.me" class="btn btn-outline-primary px-3 py-2 fw-semibold">
                        <i class="bi bi-envelope me-1"></i>support@mysubshield.me
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
