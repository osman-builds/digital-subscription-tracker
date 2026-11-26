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
            overflow-x: clip;
        }

        .section-wrap {
            scroll-margin-top: 85px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-high);
            overflow-x: clip;
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
            flex-wrap: wrap;
            gap: 0.65rem;
        }
        @media (max-width: 767.98px) {
            .hero {
                padding-top: 96px;
                padding-bottom: 56px;
            }
            .hero-glow,
            .cta-glow {
                max-width: 100vw;
            }
            .stat-card-saas .stat-val {
                font-size: 1.3rem;
            }
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
            <ul class="navbar-nav mx-auto gap-2 gap-lg-4 my-2 my-lg-0">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#intelligence">AI Intelligence</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">Fast Onboarding</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('demo.show') }}">Try Demo</a></li>
            </ul>
            <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-lg-0">
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
    <x-landing.hero />
    <x-landing.intelligence-showcase />
    <x-landing.features />
</main>

<x-landing.footer />

<script nonce="{{ Vite::cspNonce() }}" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script nonce="{{ Vite::cspNonce() }}">
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
