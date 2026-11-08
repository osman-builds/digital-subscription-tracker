<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubShield — Privacy-First Digital Subscription Tracker</title>
    <meta name="description" content="A modern, privacy-first subscription tracker that automatically normalises foreign currencies and alerts you before renewal deadlines. Zero bank credentials required.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <style>
        :root {
            --bg-base: #090d16;
            --bg-surface: #0f172a;
            --bg-card: rgba(18, 24, 38, 0.7);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(99, 102, 241, 0.4);
            --indigo: #6366f1;
            --indigo-light: #818cf8;
            --emerald: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-main);
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* ── Sticky Navbar ── */
        .saas-nav {
            background: rgba(9, 13, 22, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.85rem 0;
            transition: all 0.2s ease;
        }
        .saas-nav .navbar-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .saas-nav .nav-link {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        .saas-nav .nav-link:hover {
            color: #fff;
        }

        /* ── Modern Hero ── */
        .hero {
            position: relative;
            padding-top: 130px;
            padding-bottom: 90px;
            overflow: hidden;
        }
        /* Subtle architectural grid */
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 70% 60% at 50% 30%, #000 60%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 30%, #000 60%, transparent 100%);
            pointer-events: none;
        }
        /* Ambient glow */
        .hero-glow {
            position: absolute;
            top: 5%;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 450px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, rgba(16, 185, 129, 0.06) 40%, transparent 70%);
            filter: blur(70px);
            pointer-events: none;
            z-index: 0;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 9999px;
            padding: 0.35rem 0.95rem;
            color: #c7d2fe;
            font-size: 0.82rem;
            font-weight: 500;
        }
        .badge-dot {
            width: 7px;
            height: 7px;
            background: var(--emerald);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--emerald);
        }

        .hero h1 {
            font-size: clamp(2.4rem, 5vw, 3.8rem);
            font-weight: 800;
            line-height: 1.12;
            color: #ffffff;
            letter-spacing: -0.03em;
        }
        .text-gradient {
            background: linear-gradient(135deg, #ffffff 40%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p.lead {
            color: var(--text-muted);
            font-size: 1.15rem;
            line-height: 1.6;
            max-width: 540px;
        }

        /* ── Modern Buttons ── */
        .btn-primary-saas {
            background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .btn-primary-saas:hover {
            background: linear-gradient(180deg, #4f46e5 0%, #4338ca 100%);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }
        .btn-secondary-saas {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            color: #f1f5f9;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .btn-secondary-saas:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ── Product Window Mockup ── */
        .mockup-window {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .mockup-window:hover {
            border-color: rgba(99, 102, 241, 0.35);
        }
        .window-header {
            background: rgba(255, 255, 255, 0.04);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.75rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .window-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .window-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        /* Mini Currency Switcher inside mockup */
        .currency-pill-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .currency-pill-btn.active, .currency-pill-btn:hover {
            background: var(--indigo);
            border-color: var(--indigo);
            color: #fff;
        }

        .stat-card-saas {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s ease;
        }
        .stat-card-saas .stat-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
        }
        .stat-card-saas .stat-val {
            font-size: 1.45rem;
            font-weight: 700;
            color: #fff;
            margin: 0.2rem 0;
        }

        .subscription-row {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 0.8rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.04);
            margin-bottom: 0.45rem;
            transition: background 0.15s ease;
        }
        .subscription-row:hover {
            background: rgba(255, 255, 255, 0.04);
        }
        .brand-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* ── Bento Grid Section ── */
        .section-wrap {
            padding: 90px 0;
            position: relative;
        }
        .bento-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 2rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        .bento-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-2px);
        }
        .bento-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1.25rem;
        }

        /* ── How it works steps ── */
        .step-pill {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--indigo-light);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        /* ── Final CTA Card ── */
        .cta-banner {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 27, 75, 0.75) 100%);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 22px;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.7);
        }
        .cta-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 500px;
            height: 250px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
            filter: blur(40px);
            pointer-events: none;
        }

        /* ── Professional Clean Footer ── */
        .saas-footer {
            border-top: 1px solid var(--border-subtle);
            padding: 2.5rem 0 2rem;
            background: #060911;
        }
    </style>
</head>
<body>

{{-- ── Navbar ──────────────────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg saas-nav fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('splash') }}">
            <img src="/favicon.svg" width="28" height="28" alt="SubShield">
            <span>SubShield</span>
        </a>
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#splashNav">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="splashNav">
            <ul class="navbar-nav mx-auto gap-3">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#currencies">Currencies</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#security">Security</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-secondary-saas btn-sm px-3 py-2">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary-saas btn-sm px-3 py-2">Get Started Free</a>
            </div>
        </div>
    </div>
</nav>

{{-- ── Hero Section ────────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center g-5">

            {{-- Left: Crisp Value Proposition --}}
            <div class="col-12 col-lg-6">
                <div class="badge-pill mb-4">
                    <span class="badge-dot"></span>
                    <span>Privacy-First · Zero Bank Scraping</span>
                </div>

                <h1 class="mb-3">
                    Take Control of<br>
                    <span class="text-gradient">Recurring Subscriptions</span><br>
                    Before They Charge.
                </h1>

                <p class="lead mb-4">
                    A zero-setup tracker that automatically normalises foreign currencies (USD, EUR, GBP, SGD, and 160+ more) into your home currency, alerting you before trial and billing deadlines.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="{{ route('register') }}" class="btn btn-primary-saas btn-lg px-4 py-3">
                        <i class="bi bi-shield-check me-2"></i>Start Free — No Card Needed
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary-saas btn-lg px-4 py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Live Demo Sign In
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 text-muted small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2 text-success fs-5"></i>
                        <span>No bank logins required</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2 text-success fs-5"></i>
                        <span>160+ live FX rates</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2 text-success fs-5"></i>
                        <span>100% private & exportable</span>
                    </div>
                </div>
            </div>

            {{-- Right: Interactive Product Preview Window --}}
            <div class="col-12 col-lg-6">
                <div class="mockup-window">
                    {{-- Chrome Bar with Live Interactive Currency Toggle --}}
                    <div class="window-header">
                        <div class="window-dots">
                            <span class="window-dot" style="background: #ff5f56;"></span>
                            <span class="window-dot" style="background: #ffbd2e;"></span>
                            <span class="window-dot" style="background: #27c93f;"></span>
                            <span class="ms-2 text-muted small fw-semibold">SubShield · Dashboard</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small me-1 d-none d-sm-inline" style="font-size:0.7rem;">Base Currency:</span>
                            <button type="button" class="currency-pill-btn" data-curr="USD">$ USD</button>
                            <button type="button" class="currency-pill-btn active" data-curr="MYR">RM MYR</button>
                            <button type="button" class="currency-pill-btn" data-curr="EUR">€ EUR</button>
                            <button type="button" class="currency-pill-btn" data-curr="GBP">£ GBP</button>
                        </div>
                    </div>

                    <div class="p-3 p-sm-4">
                        {{-- Stat Cards --}}
                        <div class="row g-2 g-sm-3 mb-3">
                            <div class="col-6">
                                <div class="stat-card-saas">
                                    <div class="stat-label">Monthly Spend</div>
                                    <div class="stat-val text-success" id="stat-monthly">RM 842.00</div>
                                    <small class="text-muted" style="font-size:0.75rem;">normalised total</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card-saas">
                                    <div class="stat-label">Yearly Run Rate</div>
                                    <div class="stat-val text-primary" id="stat-yearly">RM 10,104.00</div>
                                    <small class="text-muted" style="font-size:0.75rem;">projected annual</small>
                                </div>
                            </div>
                        </div>

                        {{-- Upcoming Subscriptions with Live Converted Amounts --}}
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-subtle);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem; letter-spacing:0.05em;">
                                    <i class="bi bi-alarm text-warning me-1"></i>Upcoming Renewals
                                </span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size:0.7rem;">
                                    ● Live FX Sync Active
                                </span>
                            </div>

                            <div class="subscription-row">
                                <div class="brand-avatar" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">N</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small text-white">Netflix Premium</div>
                                    <small class="text-muted" style="font-size:0.75rem;">Billed: USD 15.49 · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small text-success sub-amount" data-usd="15.49" data-myr="69.24" data-eur="14.28" data-gbp="12.24">RM 69.24</div>
                                    <span class="badge bg-danger bg-opacity-15 text-danger small" style="font-size:0.65rem;">Renews Today</span>
                                </div>
                            </div>

                            <div class="subscription-row">
                                <div class="brand-avatar" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">S</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small text-white">Spotify Duo</div>
                                    <small class="text-muted" style="font-size:0.75rem;">Billed: EUR 9.99 · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small text-success sub-amount" data-usd="10.84" data-myr="48.45" data-eur="9.99" data-gbp="8.57">RM 48.45</div>
                                    <span class="text-muted small" style="font-size:0.7rem;">in 2 days</span>
                                </div>
                            </div>

                            <div class="subscription-row mb-0">
                                <div class="brand-avatar" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">G</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small text-white">GitHub Copilot Pro</div>
                                    <small class="text-muted" style="font-size:0.75rem;">Billed: USD 10.00 · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small text-success sub-amount" data-usd="10.00" data-myr="44.70" data-eur="9.22" data-gbp="7.90">RM 44.70</div>
                                    <span class="text-muted small" style="font-size:0.7rem;">in 5 days</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── Bento Grid Features Section ───────────────────────────────────────── --}}
<section class="section-wrap" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3" style="border-radius: 100px;">
                Platform Capabilities
            </span>
            <h2 class="fw-bold fs-1 text-white">Everything You Need. Pure Financial Privacy.</h2>
            <p class="text-muted lead mx-auto" style="max-width: 580px;">
                Designed specifically for users subscribed to international platforms in mixed currencies who want honest visibility without sacrificing privacy.
            </p>
        </div>

        <div class="row g-4">
            {{-- Bento 1: Cross-Currency Engine --}}
            <div class="col-12 col-md-7">
                <div class="bento-card">
                    <div class="bento-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-2">Real-Time Multi-Currency Normalisation</h4>
                    <p class="text-muted mb-4">
                        Subscribed to Canva in SGD, Adobe in USD, and Spotify in EUR? SubShield automatically fetches live exchange rates and normalises all expenditures into your chosen home currency.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['USD ($)', 'EUR (€)', 'GBP (£)', 'MYR (RM)', 'SGD (S$)', 'AUD (A$)', 'CAD (C$)', 'JPY (¥)', 'IDR (Rp)', 'PHP (₱)', 'SAR', 'AED'] as $c)
                            <span class="badge bg-secondary bg-opacity-10 text-light border border-white border-opacity-10 px-2 py-1 small">
                                {{ $c }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bento 2: Zero Bank Scraping --}}
            <div class="col-12 col-md-5">
                <div class="bento-card">
                    <div class="bento-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-2">Zero Bank Exposure</h4>
                    <p class="text-muted mb-3">
                        Unlike traditional budget apps that demand your banking credentials or card logins, SubShield never touches your financial accounts.
                    </p>
                    <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div class="d-flex align-items-center gap-2 text-success small fw-semibold">
                            <i class="bi bi-check-circle-fill"></i>No Plaid · No OAuth · No Bank Logins
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bento 3: Proactive Renewal Alerts --}}
            <div class="col-12 col-md-5">
                <div class="bento-card">
                    <div class="bento-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-2">Proactive Renewal Alerts</h4>
                    <p class="text-muted mb-0">
                        7-day early warnings and 3-day urgent notifications surface directly on your dashboard. Cancel trials before credit cards get billed.
                    </p>
                </div>
            </div>

            {{-- Bento 4: Data Sovereignty --}}
            <div class="col-12 col-md-7">
                <div class="bento-card">
                    <div class="bento-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-2">Instant Data Sovereignty & Purge</h4>
                    <p class="text-muted mb-0">
                        Your data belongs entirely to you. You can export records or permanently wipe your account, categories, and subscription records from the database in seconds with our built-in Danger Zone.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── How It Works ────────────────────────────────────────────────────── --}}
<section class="section-wrap" id="how-it-works" style="background: rgba(255, 255, 255, 0.01); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-secondary bg-opacity-10 text-white-50 px-3 py-2 mb-3" style="border-radius: 100px;">
                Fast Onboarding
            </span>
            <h2 class="fw-bold fs-1 text-white">Up and Running in 60 Seconds</h2>
            <p class="text-muted lead">No financial permissions or bank linking required.</p>
        </div>

        <div class="row g-4 text-start">
            <div class="col-12 col-md-4">
                <div class="p-4 rounded-4 h-100" style="background: var(--bg-card); border: 1px solid var(--border-subtle);">
                    <div class="step-pill">1</div>
                    <h5 class="fw-bold text-white mb-2">Create Account</h5>
                    <p class="text-muted mb-0 small">
                        Register with your email and password. Your personal data is stored securely with bcrypt hashing.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-4 rounded-4 h-100" style="background: var(--bg-card); border: 1px solid var(--border-subtle);">
                    <div class="step-pill">2</div>
                    <h5 class="fw-bold text-white mb-2">Add Subscriptions</h5>
                    <p class="text-muted mb-0 small">
                        Enter service name, original billing currency, and renewal cycle. SubShield normalises amounts instantly.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-4 rounded-4 h-100" style="background: var(--bg-card); border: 1px solid var(--border-subtle);">
                    <div class="step-pill">3</div>
                    <h5 class="fw-bold text-white mb-2">Receive Proactive Alerts</h5>
                    <p class="text-muted mb-0 small">
                        Upcoming renewal notices show up on your dashboard 7 days and 3 days prior so you're never caught off guard.
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
            <img src="/favicon.svg" width="56" height="56" class="mb-3" alt="SubShield">
            <h2 class="fw-bold text-white fs-1 mb-3">
                Ready to take control of your recurring spend?
            </h2>
            <p class="text-muted lead mb-4 mx-auto" style="max-width: 520px;">
                Join SubShield today and enjoy total financial visibility across all your international services.
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('register') }}" class="btn btn-primary-saas btn-lg px-5 py-3">
                    <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary-saas btn-lg px-5 py-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ── Sanitized SaaS Footer (No PII / No Framework Fingerprinting) ─────── --}}
<footer class="saas-footer">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img src="/favicon.svg" width="22" height="22" alt="SubShield">
                    <span class="text-white fw-bold">SubShield</span>
                    <span class="badge bg-primary bg-opacity-75 ms-1" style="font-size:0.7rem;">v1.0 Production</span>
                </div>
                <p class="text-muted small mb-0">
                    A privacy-first digital subscription tracker built for individuals with global recurring services.
                </p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <div class="d-flex flex-column align-items-md-end gap-1">
                    <span class="text-muted small">
                        <i class="bi bi-shield-check text-success me-1"></i>Cloudflare Anycast SSL/TLS Enforced
                    </span>
                    <span class="text-muted small">
                        Security Contact: <a href="mailto:security@mysubshield.me" class="text-light text-decoration-none">security@mysubshield.me</a>
                    </span>
                </div>
            </div>
        </div>
        <hr style="border-color: var(--border-subtle);" class="my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted gap-2">
            <div>
                &copy; {{ date('Y') }} SubShield. All rights reserved.
            </div>
            <div class="d-flex gap-3 align-items-center">
                <a href="{{ route('login') }}" class="text-muted text-decoration-none">Sign In</a>
                <span class="opacity-25">&bull;</span>
                <a href="{{ route('register') }}" class="text-muted text-decoration-none">Create Account</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    // Interactive Currency Toggle for Product Preview Window
    document.querySelectorAll('.currency-pill-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.currency-pill-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            var curr = this.getAttribute('data-curr');
            var monthly = document.getElementById('stat-monthly');
            var yearly = document.getElementById('stat-yearly');

            if (curr === 'USD') {
                monthly.textContent = '$ 188.37';
                yearly.textContent = '$ 2,260.40';
            } else if (curr === 'EUR') {
                monthly.textContent = '€ 173.61';
                yearly.textContent = '€ 2,083.30';
            } else if (curr === 'GBP') {
                monthly.textContent = '£ 147.72';
                yearly.textContent = '£ 1,772.63';
            } else { // MYR
                monthly.textContent = 'RM 842.00';
                yearly.textContent = 'RM 10,104.00';
            }

            document.querySelectorAll('.sub-amount').forEach(function(row) {
                var sym = curr === 'USD' ? '$' : (curr === 'EUR' ? '€' : (curr === 'GBP' ? '£' : 'RM'));
                var val = row.getAttribute('data-' + curr.toLowerCase());
                row.textContent = sym + ' ' + val;
            });
        });
    });
</script>
</body>
</html>
