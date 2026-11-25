<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubShield — Digital Subscription Tracker &amp; Financial Intelligence</title>
    <meta name="description" content="A modern, privacy-first subscription tracker with multimodal AI receipt scanning, live foreign currency normalisation, proactive renewal countdowns, and inflation creep defense. Zero bank logins required.">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://t2.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://t2.gstatic.com">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <style>
        :root {
            --bg-base: #0b0f19;
            --bg-surface: #111827;
            --bg-card: #151e32;
            --bg-card-hover: #1b2640;
            --border-subtle: rgba(255, 255, 255, 0.12);
            --border-hover: rgba(99, 102, 241, 0.5);
            --indigo: #6366f1;
            --indigo-light: #818cf8;
            --emerald: #10b981;
            --rose: #f43f5e;
            --amber: #f59e0b;
            --text-main: #ffffff;
            --text-high: #f1f5f9;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;
        }

        html {
            scroll-behavior: smooth;
        }

        .section-wrap {
            scroll-margin-top: 85px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-high);
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* ── Sticky Navbar ── */
        .saas-nav {
            background: rgba(11, 15, 25, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.9rem 0;
            z-index: 1050;
        }
        .saas-nav .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .saas-nav .nav-link {
            color: var(--text-body);
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        .saas-nav .nav-link:hover {
            color: #fff;
        }

        /* ── Hero Section ── */
        .hero {
            position: relative;
            padding-top: 135px;
            padding-bottom: 85px;
            overflow: hidden;
        }
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 52px 52px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, #000 60%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, #000 60%, transparent 100%);
            pointer-events: none;
        }
        .hero-glow {
            position: absolute;
            top: 2%;
            left: 50%;
            transform: translateX(-50%);
            width: 850px;
            height: 520px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.24) 0%, rgba(16, 185, 129, 0.09) 45%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.35);
            border-radius: 9999px;
            padding: 0.45rem 1.15rem;
            color: #e0e7ff;
            font-size: 0.88rem;
            font-weight: 600;
        }
        .badge-dot {
            width: 8px;
            height: 8px;
            background: var(--emerald);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--emerald);
            animation: pulseDot 2s infinite ease-in-out;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.7; }
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5.2vw, 4rem);
            font-weight: 800;
            line-height: 1.12;
            color: #ffffff;
            letter-spacing: -0.03em;
        }
        .text-gradient {
            background: linear-gradient(135deg, #ffffff 40%, #c7d2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p.lead {
            color: var(--text-body);
            font-size: 1.18rem;
            line-height: 1.65;
            max-width: 550px;
        }

        /* ── Modern Buttons ── */
        .btn-primary-saas {
            background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.25);
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-primary-saas:hover {
            background: linear-gradient(180deg, #4f46e5 0%, #4338ca 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.55);
        }
        .btn-secondary-saas {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-subtle);
            color: #f1f5f9;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-secondary-saas:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ── 3D Floating Mockup Window ── */
        .mockup-wrapper {
            perspective: 1200px;
        }
        .mockup-window {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 20px;
            box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.85), 0 0 40px rgba(99, 102, 241, 0.15);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .mockup-window:hover {
            transform: translateY(-3px);
            box-shadow: 0 35px 80px -15px rgba(0, 0, 0, 0.9), 0 0 50px rgba(99, 102, 241, 0.25);
        }
        .window-header {
            background: #162035;
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.85rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .window-dots {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .window-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
        }

        /* Currency Selector Tabs */
        .currency-pill-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            border-radius: 8px;
            padding: 0.3rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .currency-pill-btn.active {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.4);
        }
        .currency-pill-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        .stat-card-saas {
            background: #172238;
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 1rem 1.1rem;
            transition: all 0.2s ease;
        }
        .stat-card-saas .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
        }
        .stat-card-saas .stat-val {
            font-size: 1.55rem;
            font-weight: 800;
            color: #fff;
            margin: 0.2rem 0;
            transition: opacity 0.2s ease;
        }

        .subscription-row {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.75rem 0.95rem;
            border-radius: 12px;
            background: #151f34;
            border: 1px solid rgba(255, 255, 255, 0.06);
            margin-bottom: 0.55rem;
            transition: all 0.2s ease;
        }
        .subscription-row:hover {
            background: #1c2944;
            transform: translateX(4px);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .brand-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.12);
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }
        .brand-avatar img {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }

        /* ── Marquee Section ── */
        .marquee-section {
            padding: 36px 0;
            background: #090d16;
            border-top: 1px solid var(--border-subtle);
            border-bottom: 1px solid var(--border-subtle);
            overflow: hidden;
            position: relative;
        }
        .marquee-track {
            display: flex;
            gap: 3rem;
            width: max-content;
            animation: marqueeScroll 25s linear infinite;
        }
        .marquee-track:hover {
            animation-play-state: paused;
        }
        @keyframes marqueeScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .marquee-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #cbd5e1;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
        }
        .marquee-item img {
            width: 24px;
            height: 24px;
            border-radius: 6px;
        }

        /* ── Section Titles ── */
        .section-wrap {
            padding: 95px 0;
            position: relative;
        }
        .section-title {
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        .section-lead {
            color: var(--text-body);
            font-size: 1.12rem;
            line-height: 1.6;
        }

        /* ── Bento Grid Cards ── */
        .bento-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 2.2rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .bento-card:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-hover);
            transform: translateY(-4px);
            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.6);
        }
        .bento-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.3rem;
        }
        .bento-desc {
            color: var(--text-body);
            font-size: 1.02rem;
            line-height: 1.6;
        }

        /* ── Progressive Onboarding Step Cards ── */
        .step-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 2rem;
            height: 100%;
            transition: all 0.2s ease;
            position: relative;
        }
        .step-card:hover {
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -8px rgba(0,0,0,0.5);
        }
        .step-pill {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.45);
            color: #c7d2fe;
            font-weight: 800;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
        }

        /* ── Final Call To Action ── */
        .cta-banner {
            background: linear-gradient(135deg, #111827 0%, #1e1b4b 100%);
            border: 1px solid rgba(99, 102, 241, 0.35);
            border-radius: 24px;
            padding: 4.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.85);
        }
        .cta-banner::before {
            content: '';
            position: absolute;
            top: -40%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 300px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, transparent 70%);
            filter: blur(50px);
            pointer-events: none;
        }

        /* ── Clean Footer ── */
        .saas-footer {
            border-top: 1px solid var(--border-subtle);
            padding: 3rem 0 2rem;
            background: #080b12;
        }
    </style>
</head>
<body>

{{-- ── Navbar ──────────────────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg saas-nav fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('splash') }}">
            <img src="/favicon.svg" width="30" height="30" alt="SubShield">
            <span>SubShield</span>
        </a>
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#splashNav" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
        </button>
        <div class="collapse navbar-collapse" id="splashNav">
            <ul class="navbar-nav mx-auto gap-4">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#intelligence">AI Intelligence</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">Fast Onboarding</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('demo.show') }}">Try Demo</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('demo.show') }}" class="btn btn-outline-light btn-sm px-3 py-2 d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-play-circle text-primary"></i>Demo Sandbox
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary-saas btn-sm px-3 py-2">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary-saas btn-sm px-3 py-2">Get Started Free</a>
            </div>
        </div>
    </div>
</nav>

<main>
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
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="currency-pill-btn active" data-curr="MYR">RM MYR</button>
                            <button type="button" class="currency-pill-btn" data-curr="USD">$ USD</button>
                            <button type="button" class="currency-pill-btn" data-curr="EUR">€ EUR</button>
                            <button type="button" class="currency-pill-btn" data-curr="GBP">£ GBP</button>
                        </div>
                    </div>

                    <div class="p-3 p-sm-4">
                        {{-- Top KPI Cards with Health Score --}}
                        <div class="row g-2 g-sm-3 mb-3">
                            <div class="col-6">
                                <div class="stat-card-saas">
                                    <div class="stat-label">Monthly Commitment</div>
                                    <div class="stat-val text-success" id="stat-monthly">RM 460.54</div>
                                    <small class="text-white-50" style="font-size:0.75rem;">Cap: <span id="stat-budget">RM 520.00</span> (88.6%)</small>
                                </div>
                            </div>
                            <div class="col-6">
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
                        <div class="p-2 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="badge bg-danger text-uppercase py-1" style="font-size: 0.65rem;">Price Creep</span>
                                <span class="text-white" style="font-size: 0.78rem;">Netflix increased +10.7% (+$2.50 USD) detected via AI Scan</span>
                            </div>
                            <span class="badge bg-dark text-white-50 small border border-secondary" style="font-size: 0.65rem;">+RM 80.10/yr</span>
                        </div>

                        {{-- Subscriptions Rows --}}
                        <div class="p-3 rounded-3" style="background: #0d1322; border: 1px solid var(--border-subtle);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
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
                    <div class="d-flex align-items-center gap-2 mb-2">
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
                    <div class="d-flex align-items-center gap-3">
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
                    <div class="d-flex gap-2">
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25"><i class="bi bi-filetype-csv me-1"></i>Structured CSV</span>
                        <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25"><i class="bi bi-filetype-pdf me-1"></i>Printable A4 PDF</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Fast Onboarding Section ("Up and Running in 60 Seconds") ────────── --}}
<section class="section-wrap" id="how-it-works" style="background: #090d16; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3 fw-bold" style="border-radius: 100px; background: rgba(99, 102, 241, 0.2); color: #c7d2fe; border: 1px solid rgba(99, 102, 241, 0.4);">
                Fast Onboarding
            </span>
            <h2 class="section-title">Up and Running in 60 Seconds</h2>
            <p class="section-lead mx-auto" style="max-width: 600px;">
                Experience the fastest, most respectful setup in fintech. Zero financial permissions, zero OAuth scopes, zero bank passwords.
            </p>
        </div>

        <div class="row g-4 text-start">
            {{-- Step 1 --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">1</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Start Instantly</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        Test with 1-click in our <strong>Interactive Demo Sandbox</strong> with pre-populated multi-tier data, or register a private account with secure bcrypt hashing.
                    </p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">2</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">AI Scan or Add</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        Upload a receipt image or PDF invoice for instant Gemini vision extraction in RAM, or enter your subscriptions in their native currencies.
                    </p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-pill">3</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Automated Intelligence</h3>
                    <p class="bento-desc mb-0" style="font-size: 0.95rem;">
                        SubShield instantly normalises exchange rates, detects redundant category overlaps (e.g. 2 cloud plans), and generates your 0–100 Health Score.
                    </p>
                </div>
            </div>

            {{-- Step 4 --}}
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
</main>

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
            <div class="d-flex gap-3 align-items-center">
                <a href="{{ route('demo.show') }}" class="text-white-50 text-decoration-none">Interactive Sandbox</a>
                <span class="opacity-25">&bull;</span>
                <a href="{{ route('login') }}" class="text-white-50 text-decoration-none">Sign In</a>
                <span class="opacity-25">&bull;</span>
                <a href="{{ route('register') }}" class="text-white-50 text-decoration-none">Create Account</a>
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
            var budget = document.getElementById('stat-budget');

            monthly.style.opacity = '0.4';

            setTimeout(function() {
                if (curr === 'USD') {
                    monthly.textContent = '$ 103.49';
                    if (budget) budget.textContent = '$ 116.85';
                } else if (curr === 'EUR') {
                    monthly.textContent = '€ 95.94';
                    if (budget) budget.textContent = '€ 108.33';
                } else if (curr === 'GBP') {
                    monthly.textContent = '£ 82.23';
                    if (budget) budget.textContent = '£ 92.85';
                } else { // MYR
                    monthly.textContent = 'RM 460.54';
                    if (budget) budget.textContent = 'RM 520.00';
                }

                document.querySelectorAll('.sub-amount').forEach(function(row) {
                    var sym = curr === 'USD' ? '$' : (curr === 'EUR' ? '€' : (curr === 'GBP' ? '£' : 'RM'));
                    var val = row.getAttribute('data-' + curr.toLowerCase());
                    row.textContent = sym + ' ' + val;
                });

                monthly.style.opacity = '1';
            }, 100);
        });
    });
</script>

<x-ai-chat-bubble mode="concierge" />
</body>
</html>
