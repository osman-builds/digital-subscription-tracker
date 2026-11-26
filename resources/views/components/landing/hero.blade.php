{{-- ── Hero Section ────────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center g-5">

            {{-- Left: High-Contrast Value Proposition --}}
            <div class="col-12 col-lg-6">
                <div class="badge-pill mb-4">
                    <span class="badge-dot"></span>
                    <span>Zero Bank Logins Required · Privacy-First Architecture</span>
                </div>

                <h1 class="mb-3">
                    Take Control of<br>
                    <span class="text-gradient">Recurring Subscriptions</span><br>
                    With Proactive Defense.
                </h1>

                <p class="lead mb-4">
                    A modern, privacy-preserving defense against silent price creep, expiring trial shocks, and currency exchange surprises. Zero bank credentials needed.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="{{ route('register') }}" class="btn btn-primary-saas btn-lg px-4 py-3">
                        <i class="bi bi-shield-check me-2"></i>Start Free — No Card Needed
                    </a>
                    <a href="{{ route('demo.show') }}" class="btn btn-secondary-saas btn-lg px-4 py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Launch 1-Hour Sandbox
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 text-white small" style="font-weight: 500;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <span>Multimodal AI Receipt Scanner</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <span>160+ World Currencies via ECB</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <span>Price Creep &amp; Trial-Shock Guard</span>
                    </div>
                </div>
            </div>

            {{-- Right: 3D Product Mockup with Live Interactive Preview --}}
            <div class="col-12 col-lg-6 mockup-wrapper">
                <div class="mockup-window">
                    {{-- Window Header with Live Currency Switcher --}}
                    <div class="window-header">
                        <div class="window-dots">
                            <span class="window-dot" style="background: #ff5f56;"></span>
                            <span class="window-dot" style="background: #ffbd2e;"></span>
                            <span class="window-dot" style="background: #27c93f;"></span>
                            <span class="ms-2 text-white small fw-bold">SubShield · Intelligence Dashboard</span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            <button type="button" class="currency-pill-btn active" data-curr="MYR">RM MYR</button>
                            <button type="button" class="currency-pill-btn" data-curr="USD">$ USD</button>
                            <button type="button" class="currency-pill-btn" data-curr="EUR">€ EUR</button>
                            <button type="button" class="currency-pill-btn" data-curr="GBP">£ GBP</button>
                        </div>
                    </div>

                    <div class="p-3 p-sm-4">
                        {{-- Top KPI Cards with Health Score --}}
                        <div class="row g-2 g-sm-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <div class="stat-card-saas">
                                    <div class="stat-label">Monthly Commitment</div>
                                    <div class="stat-val text-success" id="stat-monthly">RM 460.54</div>
                                    <small class="text-white-50" style="font-size:0.75rem;">Cap: <span id="stat-budget">RM 520.00</span> (88.6%)</small>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="stat-card-saas">
                                    <div class="stat-label">Optimization Score</div>
                                    <div class="stat-val text-primary d-flex align-items-baseline gap-1">
                                        <span>88</span><small class="fs-6 text-white-50 fw-normal">/ 100</small>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-25 text-indigo-light" style="font-size:0.72rem;">Good · 2 Factor Deductions</span>
                                </div>
                            </div>
                        </div>

                        {{-- Proactive Intelligence Insight Banner --}}
                        <div class="p-2 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <div class="d-flex align-items-center flex-wrap gap-2 small min-w-0">
                                <span class="badge bg-danger text-uppercase py-1" style="font-size: 0.65rem;">Price Creep</span>
                                <span class="text-white" style="font-size: 0.78rem;">Netflix increased +10.7% (+$2.50 USD) detected via AI Scan</span>
                            </div>
                            <span class="badge bg-dark text-white-50 small border border-secondary flex-shrink-0" style="font-size: 0.65rem;">+RM 80.10/yr</span>
                        </div>

                        {{-- Subscriptions Rows --}}
                        <div class="p-3 rounded-3" style="background: #0d1322; border: 1px solid var(--border-subtle);">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <span class="text-white small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
                                    <i class="bi bi-shield-check text-success me-1"></i>Active Portfolio
                                </span>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-35" style="font-size:0.75rem;">
                                    <span class="badge-dot me-1" style="display:inline-block;width:6px;height:6px;"></span>Live ECB FX Sync
                                </span>
                            </div>

                            {{-- Netflix Row (Price Creep) --}}
                            <div class="subscription-row">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://netflix.com&size=64" alt="Netflix">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">Netflix Premium</div>
                                    <small class="text-white-50" style="font-size:0.75rem;">USD 15.49 · Entertainment</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success sub-amount" data-usd="15.49" data-myr="68.93" data-eur="14.36" data-gbp="12.31">RM 68.93</div>
                                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25" style="font-size:0.68rem;">+10.7% Creep</span>
                                </div>
                            </div>

                            {{-- ChatGPT Plus Row (Trial Shock) --}}
                            <div class="subscription-row">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://openai.com&size=64" alt="ChatGPT">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">ChatGPT Plus</div>
                                    <small class="text-white-50" style="font-size:0.75rem;">USD 20.00 · Free Trial Ending</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-warning sub-amount" data-usd="20.00" data-myr="89.00" data-eur="18.54" data-gbp="15.89">RM 89.00</div>
                                    <span class="badge bg-warning text-dark" style="font-size:0.68rem;">Trial: 3d Left</span>
                                </div>
                            </div>

                            {{-- iCloud+ Row (Overlap) --}}
                            <div class="subscription-row">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://apple.com&size=64" alt="iCloud+">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">iCloud+ 2TB</div>
                                    <small class="text-white-50" style="font-size:0.75rem;">MYR 44.90 · Cloud Storage</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success sub-amount" data-usd="10.09" data-myr="44.90" data-eur="9.35" data-gbp="8.02">RM 44.90</div>
                                    <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25" style="font-size:0.68rem;">Overlap with Google One</span>
                                </div>
                            </div>

                            {{-- Spotify Row (Due Soon) --}}
                            <div class="subscription-row mb-0">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://spotify.com&size=64" alt="Spotify">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">Spotify Family</div>
                                    <small class="text-white-50" style="font-size:0.75rem;">EUR 10.99 · Music</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success sub-amount" data-usd="11.85" data-myr="51.65" data-eur="10.99" data-gbp="9.42">RM 51.65</div>
                                    <span class="text-white-50 small" style="font-size:0.72rem;">in 5 days</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── Infinite Scrolling Brand Marquee ── --}}
<section class="marquee-section">
    <div class="container mb-3 text-center">
        <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.08em;">
            Seamlessly monitor recurring subscriptions from global digital providers
        </span>
    </div>
    <div class="marquee-track">
        @php
            $marqueeBrands = [
                ['Netflix', 'netflix.com'],
                ['Spotify', 'spotify.com'],
                ['GitHub', 'github.com'],
                ['ChatGPT Plus', 'openai.com'],
                ['Adobe Creative Cloud', 'adobe.com'],
                ['Apple iCloud+', 'apple.com'],
                ['Google One', 'google.com'],
                ['JetBrains', 'jetbrains.com'],
                ['Notion', 'notion.so'],
                ['Canva', 'canva.com'],
                ['Disney+', 'disneyplus.com'],
                ['Microsoft 365', 'microsoft.com'],
            ];
            $allMarquee = array_merge($marqueeBrands, $marqueeBrands);
        @endphp
        @foreach($allMarquee as $b)
            <div class="marquee-item">
                <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://{{ $b[1] }}&size=64" alt="{{ $b[0] }}">
                <span>{{ $b[0] }}</span>
            </div>
        @endforeach
    </div>
</section>
