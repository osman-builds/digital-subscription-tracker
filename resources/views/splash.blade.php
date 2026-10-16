<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubShield — Take Control of Your Subscriptions</title>
    <meta name="description" content="A privacy-first subscription tracker that normalises foreign currencies to MYR and alerts you before renewal deadlines.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --indigo: #6366f1;
            --indigo-dark: #4f46e5;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
        }

        /* ── General ── */
        body { font-family: system-ui, -apple-system, sans-serif; overflow-x: hidden; }
        .btn-indigo {
            background: var(--indigo);
            border-color: var(--indigo);
            color: #fff;
        }
        .btn-indigo:hover { background: var(--indigo-dark); border-color: var(--indigo-dark); color: #fff; }

        /* ── Navbar ── */
        .splash-nav {
            background: rgba(15,23,42,.95);
            backdrop-filter: blur(10px);
            padding: .9rem 0;
        }
        .splash-nav .navbar-brand { font-weight: 700; font-size: 1.15rem; color: #fff; }
        .splash-nav .nav-link     { color: rgba(255,255,255,.7); font-size: .9rem; }
        .splash-nav .nav-link:hover { color: #fff; }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--slate-900) 0%, var(--slate-800) 60%, #1e1b4b 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        /* Subtle grid lines decorative bg */
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,.08) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        /* Glow blob */
        .hero::after {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(99,102,241,.15);
            border: 1px solid rgba(99,102,241,.35);
            border-radius: 100px;
            padding: .4rem 1rem;
            color: #a5b4fc;
            font-size: .85rem;
            font-weight: 500;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.5rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
        }
        .hero h1 .highlight {
            background: linear-gradient(135deg, #a5b4fc, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero .lead { color: rgba(255,255,255,.7); font-size: 1.15rem; max-width: 560px; }

        /* ── Dashboard preview card ── */
        .dashboard-preview {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .preview-bar { background: rgba(255,255,255,.06); padding: .6rem 1rem; display: flex; align-items: center; gap: .5rem; }
        .preview-dot { width: 10px; height: 10px; border-radius: 50%; }
        .stat-mini {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        .stat-mini .label { color: rgba(255,255,255,.5); font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
        .stat-mini .value { color: #fff; font-size: 1.5rem; font-weight: 700; }
        .stat-mini .sub   { color: rgba(255,255,255,.4); font-size: .75rem; }

        .renewal-item {
            display: flex; align-items: center; gap: .75rem;
            padding: .6rem 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .renewal-item:last-child { border: none; }
        .renewal-logo {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .8rem;
            flex-shrink: 0;
        }

        /* ── Feature cards ── */
        .features { background: #f8fafc; }
        .feature-card {
            border: none;
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            transition: transform .2s, box-shadow .2s;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,.1); }
        .feature-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1.25rem;
        }

        /* ── CTA section ── */
        .cta-section {
            background: linear-gradient(135deg, var(--slate-900) 0%, #1e1b4b 100%);
            position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,.06) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* ── Footer ── */
        .splash-footer {
            background: var(--slate-900);
            border-top: 1px solid rgba(255,255,255,.05);
        }
    </style>
</head>
<body>

{{-- ── Sticky Navbar ──────────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg splash-nav fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('splash') }}">
            <img src="/favicon.svg" width="28" height="28" alt="SubShield">
            SubShield
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#splashNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="splashNav">
            <ul class="navbar-nav ms-auto me-3 gap-1">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-indigo btn-sm px-3">Get Started</a>
            </div>
        </div>
    </div>
</nav>

{{-- ── Hero Section ────────────────────────────────────────────────────── --}}
<section class="hero" style="padding-top:80px;">
    <div class="container position-relative" style="z-index:1;">
        <div class="row align-items-center g-5">

            {{-- Left copy --}}
            <div class="col-12 col-lg-6">
                <div class="hero-badge mb-4">
                    <i class="bi bi-shield-check"></i>
                    Privacy-First · No Bank Login Required
                </div>
                <h1 class="mb-4">
                    Take Control of<br>
                    <span class="highlight">Recurring Subscriptions</span><br>
                    — Before They Charge.
                </h1>
                <p class="lead mb-4">
                    A zero-setup tracker that normalises USD, EUR, GBP and more
                    to <strong class="text-white">Malaysian Ringgit (MYR)</strong>,
                    and reminds you before renewal deadlines so you never get
                    surprised by a charge.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-indigo btn-lg px-4 py-3">
                        <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
                <p class="mt-3" style="color:rgba(255,255,255,.4); font-size:.85rem;">
                    <i class="bi bi-lock me-1"></i>No credit card · No bank credentials · 100% private
                </p>
            </div>

            {{-- Right: Dashboard preview ── --}}
            <div class="col-12 col-lg-6">
                <div class="dashboard-preview shadow-lg">
                    {{-- Fake window chrome --}}
                    <div class="preview-bar">
                        <div class="preview-dot" style="background:#ef4444;"></div>
                        <div class="preview-dot" style="background:#eab308;"></div>
                        <div class="preview-dot" style="background:#22c55e;"></div>
                        <span class="ms-2" style="color:rgba(255,255,255,.3);font-size:.75rem;">SubShield — Dashboard</span>
                    </div>
                    <div class="p-3">
                        {{-- Stat cards --}}
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="stat-mini">
                                    <div class="label">Monthly</div>
                                    <div class="value">RM 842</div>
                                    <div class="sub">per month</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini">
                                    <div class="label">Yearly</div>
                                    <div class="value">RM 9,876</div>
                                    <div class="sub">per year</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini">
                                    <div class="label">Active</div>
                                    <div class="value">15</div>
                                    <div class="sub">subscriptions</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini" style="border-color:rgba(234,179,8,.3);">
                                    <div class="label" style="color:rgba(234,179,8,.7);">⚠ Due Soon</div>
                                    <div class="value" style="color:#fbbf24;">3</div>
                                    <div class="sub">renewing in 7 days</div>
                                </div>
                            </div>
                        </div>
                        {{-- Upcoming renewals list --}}
                        <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.75rem 1rem;">
                            <p style="color:rgba(255,255,255,.5);font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;margin-bottom:.5rem;">
                                <i class="bi bi-alarm me-1" style="color:#fbbf24;"></i>Upcoming Renewals
                            </p>
                            @foreach([
                                ['N','Netflix','USD 15.49','RM 69.24','Today!','#ef4444','#fef2f2'],
                                ['S','Spotify','EUR 9.99','RM 48.45','2 days','#1db954','#f0fdf4'],
                                ['G','GitHub Copilot','USD 10.00','RM 44.70','5 days','#6366f1','#eef2ff'],
                            ] as $r)
                            <div class="renewal-item">
                                <div class="renewal-logo" style="background:{{ $r[5] }}22;color:{{ $r[5] }};">
                                    {{ $r[0] }}
                                </div>
                                <div class="flex-grow-1">
                                    <div style="color:#fff;font-size:.85rem;font-weight:600;">{{ $r[1] }}</div>
                                    <div style="color:rgba(255,255,255,.4);font-size:.75rem;">{{ $r[2] }}</div>
                                </div>
                                <div class="text-end">
                                    <div style="color:#4ade80;font-size:.85rem;font-weight:600;">{{ $r[3] }}</div>
                                    <div style="color:{{ $loop->first ? '#f87171' : 'rgba(255,255,255,.4)' }};font-size:.7rem;">{{ $r[4] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── Trusted-by strip ────────────────────────────────────────────────── --}}
<div style="background:#f1f5f9;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
    <div class="container py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 text-muted" style="font-size:.85rem;">
            <span><i class="bi bi-check-circle-fill text-success me-1"></i>17+ currencies supported</span>
            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Bcrypt-secured accounts</span>
            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Live FX rates every 6 hours</span>
            <span><i class="bi bi-check-circle-fill text-success me-1"></i>No data shared with third parties</span>
        </div>
    </div>
</div>

{{-- ── Features Section ────────────────────────────────────────────────── --}}
<section class="features py-6" id="features" style="padding:80px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3" style="border-radius:100px;">
                Why SubShield?
            </span>
            <h2 class="fw-bold fs-1">Everything you need, nothing you don't</h2>
            <p class="text-muted lead mx-auto" style="max-width:540px;">
                Built for Malaysians who subscribe to global services in mixed currencies and want
                honest visibility into what they're actually spending.
            </p>
        </div>

        <div class="row g-4">

            {{-- Card 1 --}}
            <div class="col-12 col-md-4">
                <div class="feature-card bg-white shadow-sm">
                    <div class="feature-icon" style="background:#fef9c3;">
                        <i class="bi bi-bell-fill" style="color:#ca8a04;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Proactive Alerts</h5>
                    <p class="text-muted mb-0">
                        7-day and 3-day renewal reminders surface automatically on your dashboard.
                        Never again miss the cancellation window for a trial you forgot about.
                    </p>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="col-12 col-md-4">
                <div class="feature-card bg-white shadow-sm">
                    <div class="feature-icon" style="background:#ede9fe;">
                        <i class="bi bi-currency-exchange" style="color:#7c3aed;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Currency Normalisation</h5>
                    <p class="text-muted mb-0">
                        Automatically converts USD, EUR, GBP, SGD, AUD and 12 more currencies
                        to <strong>Malaysian Ringgit</strong> using live FX rates refreshed every 6 hours.
                    </p>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="col-12 col-md-4">
                <div class="feature-card bg-white shadow-sm">
                    <div class="feature-icon" style="background:#dcfce7;">
                        <i class="bi bi-shield-lock-fill" style="color:#16a34a;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Zero Bank Exposure</h5>
                    <p class="text-muted mb-0">
                        Complete financial privacy — you enter amounts manually. No bank logins,
                        no credential scraping, no OAuth to payment providers.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── How it Works ────────────────────────────────────────────────────── --}}
<section style="background:#f8fafc;padding:80px 0;" id="about">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold fs-1">Up and running in 60 seconds</h2>
            <p class="text-muted lead">No bank connection. No complex setup.</p>
        </div>
        <div class="row g-4 text-center">
            @foreach([
                ['1', 'bi-person-plus', '#6366f1', 'Create Account', 'Register with email and a secure password. Your data stays on your own server.'],
                ['2', 'bi-plus-circle', '#10b981', 'Add Subscriptions', 'Enter the service name, amount, currency, and billing cycle. SubShield converts to MYR automatically.'],
                ['3', 'bi-bell', '#f59e0b', 'Get Alerts', 'Upcoming renewals appear on your dashboard 7 and 3 days early — so you\'re always prepared.'],
            ] as $step)
            <div class="col-12 col-md-4">
                <div class="d-flex flex-column align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 text-white"
                         style="width:64px;height:64px;background:{{ $step[2] }};">
                        {{ $step[0] }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $step[3] }}</h5>
                    <p class="text-muted" style="max-width:280px;">{{ $step[4] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA Section ─────────────────────────────────────────────────────── --}}
<section class="cta-section py-5" style="padding:80px 0 !important;">
    <div class="container text-center position-relative" style="z-index:1;">
        <img src="/favicon.svg" width="60" height="60" class="mb-4" alt="SubShield">
        <h2 class="fw-bold text-white fs-1 mb-3">
            Ready to take control?
        </h2>
        <p class="text-white-50 lead mb-5 mx-auto" style="max-width:480px;">
            Join SubShield and get a clear picture of your recurring spend in MYR —
            starting today.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ route('register') }}" class="btn btn-indigo btn-lg px-5 py-3">
                <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5 py-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </a>
        </div>
    </div>
</section>

{{-- ── Footer ──────────────────────────────────────────────────────────── --}}
<footer class="splash-footer py-4">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img src="/favicon.svg" width="20" height="20" alt="SubShield">
                    <span class="text-white fw-semibold">SubShield</span>
                    <span class="badge bg-primary ms-1">v1.0</span>
                </div>
                <p class="text-muted small mb-0">
                    Digital Subscription Tracker — Final Year Project
                </p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <p class="text-muted small mb-0">
                    Developed by <span class="text-white">Osman Abdullahi Ahmed</span><br>
                    Bachelor of Software Engineering (Honours), <span class="text-white">UNITEN</span>
                </p>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,.08);" class="my-3">
        <p class="text-center text-muted small mb-0">
            Built with Laravel 13 · Bootstrap 5 · MySQL &nbsp;·&nbsp;
            <a href="{{ route('login') }}" class="text-muted">Sign In</a> &nbsp;·&nbsp;
            <a href="{{ route('register') }}" class="text-muted">Register</a>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
