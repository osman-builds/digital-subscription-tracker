{{-- ── Fast Onboarding Section ("Up and Running in 60 Seconds") ────────── --}}
<section class="section-wrap" id="how-it-works" style="background: #090d16; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3 fw-bold" style="border-radius: 100px; background: rgba(99, 102, 241, 0.2); color: #c7d2fe; border: 1px solid rgba(99, 102, 241, 0.4);">
                Fast Onboarding
            </span>
            <h2 class="section-title">Up and Running in 60 Seconds</h2>
            <p class="section-lead mx-auto" style="max-width: 600px;">
                Get started in seconds with zero bank logins, zero card details, and zero setup friction.
            </p>
        </div>

        <div class="row g-4 text-start">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">1</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Start Instantly</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        Explore the <strong>Interactive Demo</strong> in one click with sample subscriptions, or create your own free account in seconds.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">2</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Scan or Add</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        Upload a receipt image or PDF invoice for instant auto-fill, or enter your subscriptions in their original currencies.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">3</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Automated Intelligence</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        SubShield instantly normalises exchange rates, detects redundant category overlaps (e.g. 2 cloud plans), and generates your 0–100 Health Score.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">4</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Zero-Surprise Defense</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        Receive proactive 7-day and 3-day notifications, impending trial shock warnings, and stealth price inflation alerts before any card is debited.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA Section ─────────────────────────────────────────────────────── --}}
<section class="section-wrap" id="security">
    <div class="container">
        <div class="cta-banner">
            <img src="/favicon.svg" width="60" height="60" class="mb-3" alt="SubShield">
            <h2 class="fw-bold text-white fs-1 mb-3">
                Ready to take total control of your recurring spend?
            </h2>
            <p class="section-lead mb-4 mx-auto" style="max-width: 580px;">
                Experience SubShield today. Explore the live sandbox without signing up, or create your private encrypted account in under a minute.
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-center mb-4">
                <a href="{{ route('register') }}" class="btn btn-primary-saas btn-lg px-5 py-3">
                    <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
                </a>
                <a href="{{ route('demo.show') }}" class="btn btn-secondary-saas btn-lg px-5 py-3">
                    <i class="bi bi-play-circle me-2"></i>Launch 1-Hour Sandbox
                </a>
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-4 text-white-50 small">
                <span><i class="bi bi-check-circle-fill text-success me-1"></i>No credit card required</span>
                <span><i class="bi bi-check-circle-fill text-success me-1"></i>Zero bank scraping</span>
                <span><i class="bi bi-check-circle-fill text-success me-1"></i>100% data sovereignty</span>
            </div>
        </div>
    </div>
</section>
