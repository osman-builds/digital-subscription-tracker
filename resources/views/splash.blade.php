<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubShield — Digital Subscription Tracker</title>
    <meta name="description" content="A modern, privacy-first subscription tracker that automatically normalises foreign currencies and alerts you before renewal deadlines. Zero bank credentials required.">
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
            --text-main: #ffffff;
            --text-high: #f1f5f9;
            --text-body: #cbd5e1; /* High contrast readable slate */
            --text-muted: #94a3b8;
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
            background: rgba(11, 15, 25, 0.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.9rem 0;
            z-index: 1050;
        }
        .saas-nav .navbar-brand {
            font-weight: 700;
            font-size: 1.2rem;
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
            padding-bottom: 80px;
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
            width: 800px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(16, 185, 129, 0.08) 45%, transparent 70%);
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
            padding: 0.45rem 1.1rem;
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
            animation: floatLevitate 6s ease-in-out infinite;
            transform-style: preserve-3d;
            transition: border-color 0.3s ease;
        }
        @keyframes floatLevitate {
            0%, 100% {
                transform: translateY(0px) rotateX(1.5deg) rotateY(-2deg);
            }
            50% {
                transform: translateY(-14px) rotateX(0deg) rotateY(-0.5deg);
            }
        }
        .mockup-window:hover {
            animation-play-state: paused;
            border-color: rgba(99, 102, 241, 0.6);
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

        .sub-amount {
            color: #34d399 !important;
        }

        .stat-card-saas {
            background: #172238;
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 1.1rem;
            transition: all 0.2s ease;
        }
        .stat-card-saas .stat-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
        }
        .stat-card-saas .stat-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            margin: 0.25rem 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .subscription-row {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            background: #151f34;
            border: 1px solid rgba(255, 255, 255, 0.06);
            margin-bottom: 0.6rem;
            transition: all 0.2s ease;
        }
        .subscription-row:hover {
            background: #1c2944;
            transform: translateX(4px);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .brand-avatar {
            width: 38px;
            height: 38px;
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
            width: 24px;
            height: 24px;
            object-fit: contain;
        }

        /* ── Infinite Scrolling Brand Marquee ── */
        .marquee-section {
            padding: 40px 0;
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
            width: 26px;
            height: 26px;
            border-radius: 6px;
        }

        /* ── Bento Grid Section ── */
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
            font-size: 1.15rem;
            line-height: 1.6;
        }

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
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.4rem;
        }
        .bento-desc {
            color: var(--text-body);
            font-size: 1.02rem;
            line-height: 1.6;
        }

        /* ── How It Works Steps ── */
        .step-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 2rem;
            height: 100%;
            transition: all 0.2s ease;
        }
        .step-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-3px);
        }
        .step-pill {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.4);
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
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#splashNav">
            <i class="bi bi-list fs-2"></i>
        </button>
        <div class="collapse navbar-collapse" id="splashNav">
            <ul class="navbar-nav mx-auto gap-4">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#security">Privacy</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-secondary-saas px-3 py-2">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary-saas px-3 py-2">Get Started</a>
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
                    <span>Privacy-First · Zero Bank Scraping</span>
                </div>

                <h1 class="mb-3">
                    Take Control of<br>
                    <span class="text-gradient">Recurring Subscriptions</span><br>
                    Before They Charge.
                </h1>

                <p class="lead mb-4">
                    A clean, privacy-preserving tracker that normalises foreign currency subscriptions into your home currency with live FX rates, alerting you before trial and billing deadlines.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="{{ route('register') }}" class="btn btn-primary-saas btn-lg px-4 py-3">
                        <i class="bi bi-shield-check me-2"></i>Start Free — No Card Needed
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary-saas btn-lg px-4 py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Explore Live Demo
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 text-white small" style="font-weight: 500;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <span>No bank logins required</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <span>160+ world currencies</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <span>100% private data</span>
                    </div>
                </div>
            </div>

            {{-- Right: 3D Animated Product Window with Authentic Brand Logos --}}
            <div class="col-12 col-lg-6 mockup-wrapper">
                <div class="mockup-window">
                    {{-- Chrome Bar with Live Currency Toggle --}}
                    <div class="window-header">
                        <div class="window-dots">
                            <span class="window-dot" style="background: #ff5f56;"></span>
                            <span class="window-dot" style="background: #ffbd2e;"></span>
                            <span class="window-dot" style="background: #27c93f;"></span>
                            <span class="ms-2 text-white small fw-bold">SubShield · Dashboard</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
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
                                    <small class="text-white-50" style="font-size:0.78rem;">Normalised total</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card-saas">
                                    <div class="stat-label">Yearly Run Rate</div>
                                    <div class="stat-val text-primary" id="stat-yearly">RM 10,104.00</div>
                                    <small class="text-white-50" style="font-size:0.78rem;">Projected annual</small>
                                </div>
                            </div>
                        </div>

                        {{-- Upcoming Subscriptions with Real Brand Logos --}}
                        <div class="p-3 rounded-3" style="background: #0d1322; border: 1px solid var(--border-subtle);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-white small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
                                    <i class="bi bi-alarm-fill text-warning me-1"></i>Upcoming Renewals
                                </span>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-35" style="font-size:0.75rem;">
                                    <span class="badge-dot me-1" style="display:inline-block;width:6px;height:6px;"></span>Live FX Sync Active
                                </span>
                            </div>

                            {{-- Netflix Row --}}
                            <div class="subscription-row">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://netflix.com&size=64" alt="Netflix">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">Netflix Premium</div>
                                    <small class="text-white-50" style="font-size:0.78rem;">Original: USD 15.49 · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success sub-amount" data-usd="15.49" data-myr="69.24" data-eur="14.28" data-gbp="12.24">RM 69.24</div>
                                    <span class="badge small fw-bold" style="font-size:0.7rem; background: rgba(239, 68, 68, 0.25); color: #fca5a5;">Renews Today</span>
                                </div>
                            </div>

                            {{-- Spotify Row --}}
                            <div class="subscription-row">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://spotify.com&size=64" alt="Spotify">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">Spotify Family</div>
                                    <small class="text-white-50" style="font-size:0.78rem;">Original: EUR 9.99 · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success sub-amount" data-usd="10.84" data-myr="48.45" data-eur="9.99" data-gbp="8.57">RM 48.45</div>
                                    <span class="text-white-50 small" style="font-size:0.75rem;">in 2 days</span>
                                </div>
                            </div>

                            {{-- GitHub Copilot Row --}}
                            <div class="subscription-row mb-0">
                                <div class="brand-avatar">
                                    <img src="https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://github.com&size=64" alt="GitHub">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small">GitHub Copilot Pro</div>
                                    <small class="text-white-50" style="font-size:0.78rem;">Original: USD 10.00 · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success sub-amount" data-usd="10.00" data-myr="44.70" data-eur="9.22" data-gbp="7.90">RM 44.70</div>
                                    <span class="text-white-50 small" style="font-size:0.75rem;">in 5 days</span>
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
            Seamlessly monitor services from all major global providers
        </span>
    </div>
    <div class="marquee-track">
        @php
            $marqueeBrands = [
                ['Netflix', 'netflix.com'],
                ['Spotify', 'spotify.com'],
                ['GitHub', 'github.com'],
                ['Adobe', 'adobe.com'],
                ['Apple iCloud+', 'apple.com'],
                ['YouTube Premium', 'youtube.com'],
                ['ChatGPT Plus', 'openai.com'],
                ['Figma', 'figma.com'],
                ['Notion', 'notion.so'],
                ['Canva', 'canva.com'],
                ['Disney+', 'disneyplus.com'],
                ['Microsoft 365', 'microsoft.com'],
            ];
            // Duplicate for smooth seamless loop
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

{{-- ── Bento Grid Features Section ───────────────────────────────────────── --}}
<section class="section-wrap" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-20 text-indigo-light px-3 py-2 mb-3 fw-bold" style="border-radius: 100px;">
                Platform Capabilities
            </span>
            <h2 class="section-title">Everything You Need. Total Financial Privacy.</h2>
            <p class="section-lead mx-auto" style="max-width: 600px;">
                Engineered for users who subscribe to global platforms in mixed currencies and want honest visibility without handing over bank passwords.
            </p>
        </div>

        <div class="row g-4">
            {{-- Bento 1: Dynamic Multi-Currency --}}
            <div class="col-12 col-md-7">
                <div class="bento-card">
                    <div class="bento-icon bg-primary bg-opacity-15 text-primary">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Live Cross-Currency Normalisation</h3>
                    <p class="bento-desc mb-4">
                        Subscribed to Canva in SGD, Adobe in USD, and Spotify in EUR? SubShield automatically fetches live exchange rates and normalises all expenditures into your chosen home currency.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['USD ($)', 'EUR (€)', 'GBP (£)', 'MYR (RM)', 'SGD (S$)', 'AUD (A$)', 'CAD (C$)', 'JPY (¥)', 'IDR (Rp)', 'PHP (₱)', 'SAR', 'AED'] as $c)
                            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 px-3 py-2" style="font-size: 0.82rem;">
                                {{ $c }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bento 2: Zero Bank Scraping --}}
            <div class="col-12 col-md-5">
                <div class="bento-card">
                    <div class="bento-icon bg-success bg-opacity-15 text-success">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Zero Bank Scraping</h3>
                    <p class="bento-desc mb-3">
                        Unlike traditional budget apps that demand your banking credentials or card logins, SubShield never touches your financial accounts.
                    </p>
                    <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                        <div class="d-flex align-items-center gap-2 fw-bold small" style="color: #34d399;">
                            <i class="bi bi-check-circle-fill fs-5"></i>No Plaid · No OAuth · No Bank Logins
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bento 3: Renewal Countdown --}}
            <div class="col-12 col-md-5">
                <div class="bento-card">
                    <div class="bento-icon bg-warning bg-opacity-15 text-warning">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Proactive Renewal Countdown</h3>
                    <p class="bento-desc mb-0">
                        Customizable 7-day early warnings and 3-day urgent notifications surface directly on your dashboard. Cancel trials before credit cards get billed.
                    </p>
                </div>
            </div>

            {{-- Bento 4: Data Sovereignty --}}
            <div class="col-12 col-md-7">
                <div class="bento-card">
                    <div class="bento-icon bg-danger bg-opacity-15 text-danger">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Instant Data Sovereignty & Purge</h3>
                    <p class="bento-desc mb-0">
                        You have total ownership over your data. Permanently wipe your account, custom categories, and all subscription records from the database in seconds with our built-in Danger Zone.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── How It Works ────────────────────────────────────────────────────── --}}
<section class="section-wrap" id="how-it-works" style="background: #090d16; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3 fw-bold" style="border-radius: 100px; background: rgba(255, 255, 255, 0.12); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.2);">
                Fast Onboarding
            </span>
            <h2 class="section-title">Up and Running in 60 Seconds</h2>
            <p class="section-lead">No financial permissions or bank linking required.</p>
        </div>

        <div class="row g-4 text-start">
            <div class="col-12 col-md-4">
                <div class="step-card">
                    <div class="step-pill">1</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Create Account</h3>
                    <p class="bento-desc mb-0">
                        Register with your email and password. Your personal data is stored securely with bcrypt hashing.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="step-card">
                    <div class="step-pill">2</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Add Subscriptions</h3>
                    <p class="bento-desc mb-0">
                        Enter service name, original billing currency, and renewal cycle. SubShield normalises amounts instantly.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="step-card">
                    <div class="step-pill">3</div>
                    <h3 class="fw-bold text-white mb-2 fs-5">Receive Proactive Alerts</h3>
                    <p class="bento-desc mb-0">
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
            <img src="/favicon.svg" width="60" height="60" class="mb-3" alt="SubShield">
            <h2 class="fw-bold text-white fs-1 mb-3">
                Ready to take control of your recurring spend?
            </h2>
            <p class="section-lead mb-4 mx-auto" style="max-width: 540px;">
                Join SubShield today and enjoy total financial visibility across all your international services.
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('register') }}" class="btn btn-primary-saas btn-lg px-5 py-3">
                    <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary-saas btn-lg px-5 py-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Explore Live Demo
                </a>
            </div>
        </div>
    </div>
</section>
</main>

{{-- ── Clean Footer (No PII / No Framework Fingerprinting) ──────────────── --}}
<footer class="saas-footer">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="/favicon.svg" width="24" height="24" alt="SubShield">
                    <span class="text-white fw-bold fs-5">SubShield</span>
                </div>
                <p class="text-white-50 small mb-0">
                    A privacy-first digital subscription tracker built for individuals with global recurring services.
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
            var yearly = document.getElementById('stat-yearly');

            monthly.style.opacity = '0.4';
            yearly.style.opacity = '0.4';

            setTimeout(function() {
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

                monthly.style.opacity = '1';
                yearly.style.opacity = '1';
            }, 100);
        });
    });
</script>
</body>
</html>
