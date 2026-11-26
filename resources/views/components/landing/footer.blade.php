{{-- ── Clean Footer ────────────────────────────────────────────────────── --}}
<footer class="saas-footer">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="/favicon.svg" width="24" height="24" alt="SubShield">
                    <span class="text-white fw-bold fs-5">SubShield</span>
                </div>
                <p class="text-white-50 small mb-0">
                    A privacy-first digital subscription tracker and intelligence engine built for individuals with global recurring services.
                </p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <div class="d-flex flex-column align-items-md-end gap-1">
                    <span class="text-white-50 small">
                        <i class="bi bi-shield-check text-success me-1"></i>Cloudflare Edge SSL/TLS Active
                    </span>
                    <span class="text-white-50 small">
                        Security Contact: <a href="mailto:security@mysubshield.me" class="text-white text-decoration-none">security@mysubshield.me</a>
                    </span>
                </div>
            </div>
        </div>
        <hr style="border-color: var(--border-subtle);" class="my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center small text-white-50 gap-2">
            <div>
                &copy; {{ date('Y') }} SubShield. All rights reserved.
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <a href="{{ route('demo.show') }}" class="text-white-50 text-decoration-none">Interactive Sandbox</a>
                <span class="opacity-25">&bull;</span>
                <a href="{{ route('login') }}" class="text-white-50 text-decoration-none">Sign In</a>
                <span class="opacity-25">&bull;</span>
                <a href="{{ route('register') }}" class="text-white-50 text-decoration-none">Create Account</a>
            </div>
        </div>
    </div>
</footer>
