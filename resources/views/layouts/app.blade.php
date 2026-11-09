<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SubShield — Privacy-first digital subscription tracker with real-time multi-currency normalisation and proactive renewal alerts.">
    <title>@yield('title', 'SubShield')</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://t2.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">

    {{-- ▸ Anti-flash: apply stored theme BEFORE first paint ─────────── --}}
    <script>
        (function () {
            var stored = localStorage.getItem('subshield_dark');
            var serverPref = {{ (auth()->check() && auth()->user()->getSettings()->dark_mode) ? 'true' : 'false' }};
            // localStorage wins; fall back to server preference
            var dark = stored !== null ? stored === '1' : serverPref;
            if (dark) document.documentElement.setAttribute('data-bs-theme', 'dark');
        })();
    </script>

    <style>
        /* ──────────────────────────────────────────────────────────────
           DESIGN TOKENS
        ────────────────────────────────────────────────────────────── */
        :root {
            --ss-body-bg:        #f0f2f5;
            --ss-card-bg:        #ffffff;
            --ss-card-header-bg: #ffffff;
            --ss-card-border:    rgba(0,0,0,.075);
            --ss-card-shadow:    0 2px 8px rgba(0,0,0,.06);
            --ss-table-head-bg:  #f8f9fa;
            --ss-muted:          #6c757d;
            --ss-border:         #dee2e6;
        }

        [data-bs-theme="dark"] {
            --ss-body-bg:        #0d1117;
            --ss-card-bg:        #161b22;
            --ss-card-header-bg: #161b22;
            --ss-card-border:    rgba(255,255,255,.07);
            --ss-card-shadow:    0 2px 12px rgba(0,0,0,.4);
            --ss-table-head-bg:  #1c2128;
            --ss-muted:          #8b949e;
            --ss-border:         #30363d;
        }

        /* WCAG AA High Contrast Overrides for Dark Mode */
        [data-bs-theme="dark"] .text-success { color: #34d399 !important; }
        [data-bs-theme="dark"] .text-danger { color: #f87171 !important; }
        [data-bs-theme="dark"] .text-warning { color: #fbbf24 !important; }
        [data-bs-theme="dark"] .btn-outline-primary {
            color: #818cf8 !important;
            border-color: #818cf8 !important;
        }
        [data-bs-theme="dark"] .btn-outline-primary:hover {
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
            color: #ffffff !important;
        }

        /* ──────────────────────────────────────────────────────────────
           BASE
        ────────────────────────────────────────────────────────────── */
        body {
            background-color: var(--ss-body-bg);
            transition: background-color .25s, color .25s;
        }

        /* ──────────────────────────────────────────────────────────────
           CARDS — override Bootstrap + our hardcoded bg-white headers
        ────────────────────────────────────────────────────────────── */
        .card {
            background-color: var(--ss-card-bg) !important;
            border-color: var(--ss-card-border) !important;
        }
        /* Every card-header we wrote as "bg-white border-0" */
        .card-header,
        .card-header.bg-white,
        .card-footer,
        .card-footer.bg-white {
            background-color: var(--ss-card-header-bg) !important;
            border-color: var(--ss-card-border) !important;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--ss-card-shadow);
        }

        /* ──────────────────────────────────────────────────────────────
           TABLES
        ────────────────────────────────────────────────────────────── */
        .table th {
            font-weight: 600;
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--ss-muted);
        }
        /* table-light thead in dark mode */
        [data-bs-theme="dark"] .table-light th,
        [data-bs-theme="dark"] .table-light td,
        [data-bs-theme="dark"] thead.table-light tr {
            background-color: var(--ss-table-head-bg) !important;
            color: var(--ss-muted) !important;
            border-color: var(--ss-border) !important;
        }
        /* table-hover rows */
        [data-bs-theme="dark"] .table-hover > tbody > tr:hover > * {
            background-color: rgba(255,255,255,.03);
        }

        /* ──────────────────────────────────────────────────────────────
           FORMS — inputs look washed out in dark mode without this
        ────────────────────────────────────────────────────────────── */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #0d1117;
            border-color: var(--ss-border);
            color: #e6edf3;
        }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #161b22;
            border-color: #6366f1;
            box-shadow: 0 0 0 .2rem rgba(99,102,241,.25);
            color: #e6edf3;
        }
        [data-bs-theme="dark"] .form-control::placeholder { color: #6e7681; }
        [data-bs-theme="dark"] .input-group-text {
            background-color: #1c2128;
            border-color: var(--ss-border);
            color: #8b949e;
        }
        [data-bs-theme="dark"] .form-text { color: #8b949e; }

        /* ──────────────────────────────────────────────────────────────
           BORDERS & DIVIDERS
        ────────────────────────────────────────────────────────────── */
        [data-bs-theme="dark"] .border-bottom,
        [data-bs-theme="dark"] .border-top,
        [data-bs-theme="dark"] hr {
            border-color: var(--ss-border) !important;
        }

        /* ──────────────────────────────────────────────────────────────
           BADGES — bg-light text-dark won't show in dark mode
        ────────────────────────────────────────────────────────────── */
        [data-bs-theme="dark"] .badge.bg-light {
            background-color: #1c2128 !important;
            color: #8b949e !important;
            border-color: var(--ss-border) !important;
        }
        /* text-muted override */
        [data-bs-theme="dark"] .text-muted { color: var(--ss-muted) !important; }

        /* ──────────────────────────────────────────────────────────────
           SIDEBAR — always dark regardless of theme
        ────────────────────────────────────────────────────────────── */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        .sidebar .brand {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            padding: 1rem 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.65);
            padding: .6rem 1.5rem;
            border-radius: 0;
            transition: all .2s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i { width: 20px; }
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.1);
        }
        .dark-toggle-btn {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            color: rgba(255,255,255,.8);
            border-radius: 20px;
            padding: .3rem .9rem;
            font-size: .8rem;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .dark-toggle-btn:hover { background: rgba(255,255,255,.2); color: #fff; }

        /* ──────────────────────────────────────────────────────────────
           LAYOUT
        ────────────────────────────────────────────────────────────── */
        .main-content { margin-left: 250px; padding: 2rem; }

        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; min-height: auto; }
            .main-content { margin-left: 0; padding: 1rem; }
        }

        /* ──────────────────────────────────────────────────────────────
           SETTINGS PAGE — about card inner banner always dark
        ────────────────────────────────────────────────────────────── */
        .about-banner {
            background: linear-gradient(135deg,#1e293b 0%,#0f172a 100%) !important;
        }

        /* ──────────────────────────────────────────────────────────────
           PAGINATION
        ────────────────────────────────────────────────────────────── */
        [data-bs-theme="dark"] .page-link {
            background-color: var(--ss-card-bg);
            border-color: var(--ss-border);
            color: #8b949e;
        }
        [data-bs-theme="dark"] .page-item.active .page-link {
            background-color: #6366f1;
            border-color: #6366f1;
            color: #fff;
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="d-flex">
    {{-- ── Desktop Sidebar (always dark-themed) ─────────────────────── --}}
    <nav class="sidebar d-none d-md-flex flex-column" aria-label="Sidebar navigation">
        <div class="brand d-flex align-items-center gap-2">
            <img src="/favicon.svg" width="26" height="26" alt="SubShield">
            <span>SubShield</span>
        </div>
        <ul class="nav flex-column mt-3 flex-grow-1">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('subscriptions.index') }}"
                   class="nav-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
                    <i class="bi bi-list-ul me-2"></i>Subscriptions
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('expenditure.index') }}"
                   class="nav-link {{ request()->routeIs('expenditure.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line me-2"></i>Expenditure
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('categories.index') }}"
                   class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.index') }}"
                   class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item mt-3 px-3">
                <a href="{{ route('subscriptions.create') }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-plus-lg me-1"></i>Add Subscription
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <button id="sidebarDarkToggle" class="dark-toggle-btn w-100 justify-content-center mb-2" type="button">
                <i class="bi bi-moon-stars-fill" id="darkIcon"></i>
                <span id="darkLabel">Dark mode</span>
            </button>
            <div class="text-white-50 small mb-2">
                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- ── Mobile top bar ────────────────────────────────────────────── --}}
    <nav class="navbar navbar-dark bg-dark d-md-none w-100 position-fixed top-0" style="z-index:200;" aria-label="Mobile navigation">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold d-flex align-items-center gap-2">
                <img src="/favicon.svg" width="22" height="22" alt="SubShield">
                <span>SubShield</span>
            </span>
            <div class="d-flex gap-2 align-items-center">
                <button id="mobileDarkToggle" class="btn btn-sm btn-outline-light" type="button" title="Toggle dark/light mode">
                    <i class="bi bi-moon-stars-fill" id="mobileDarkIcon"></i>
                </button>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start bg-dark text-white" id="mobileNav">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2">
                <img src="/favicon.svg" width="22" height="22" alt="SubShield">
                <h5 class="offcanvas-title fw-bold mb-0">SubShield</h5>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <ul class="nav flex-column flex-grow-1">
                <li class="nav-item"><a href="{{ route('dashboard') }}"          class="nav-link text-white-50 px-4 py-3"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="{{ route('subscriptions.index') }}" class="nav-link text-white-50 px-4 py-3"><i class="bi bi-list-ul me-2"></i>Subscriptions</a></li>
                <li class="nav-item"><a href="{{ route('expenditure.index') }}"   class="nav-link text-white-50 px-4 py-3"><i class="bi bi-bar-chart-line me-2"></i>Expenditure</a></li>
                <li class="nav-item"><a href="{{ route('categories.index') }}"    class="nav-link text-white-50 px-4 py-3"><i class="bi bi-tags me-2"></i>Categories</a></li>
                <li class="nav-item"><a href="{{ route('settings.index') }}"      class="nav-link text-white-50 px-4 py-3"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li class="nav-item p-3">
                    <a href="{{ route('subscriptions.create') }}" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Add Subscription
                    </a>
                </li>
            </ul>
            <div class="p-3 border-top border-secondary">
                <div class="text-white-50 small mb-2">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Main content ──────────────────────────────────────────────── --}}
    <main class="main-content flex-grow-1">
        <div class="d-md-none" style="height:56px;"></div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
/* ── Dark-mode controller ────────────────────────────────────────────────── */
(function () {
    var CSRF = '{{ csrf_token() }}';
    var ENDPOINT = '{{ route("settings.dark_mode") }}';

    function isDark() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    function applyTheme(dark) {
        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        localStorage.setItem('subshield_dark', dark ? '1' : '0');

        // Update all toggle UI elements
        ['darkIcon', 'mobileDarkIcon'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.className = dark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        });
        var lbl = document.getElementById('darkLabel');
        if (lbl) lbl.textContent = dark ? 'Light mode' : 'Dark mode';

        // Also sync the Settings page toggle if it exists on same page
        var settingsToggle = document.getElementById('darkModeToggle');
        if (settingsToggle) settingsToggle.checked = dark;
        var settingsLabel = document.getElementById('darkModeLabel');
        if (settingsLabel) settingsLabel.textContent = (dark ? 'Dark' : 'Light') + ' mode';
    }

    function persistToServer(dark) {
        fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ dark_mode: dark })
        }).catch(function () {}); // silent fail — localStorage already updated
    }

    // Initialise UI to match current theme on load
    applyTheme(isDark());

    // Wire up toggles
    ['sidebarDarkToggle', 'mobileDarkToggle'].forEach(function (id) {
        var btn = document.getElementById(id);
        if (btn) {
            btn.addEventListener('click', function () {
                var next = !isDark();
                applyTheme(next);
                persistToServer(next);
            });
        }
    });

    // Settings page inline toggle (also calls applyTheme so sidebar updates)
    var settingsToggle = document.getElementById('darkModeToggle');
    if (settingsToggle) {
        settingsToggle.addEventListener('change', function () {
            var next = this.checked;
            applyTheme(next);
            persistToServer(next);
        });
    }

    // Fallback handler for brand logo images that fail to load
    document.addEventListener('error', function (event) {
        if (event.target && event.target.classList && event.target.classList.contains('brand-fallback-img')) {
            event.target.style.display = 'none';
            if (event.target.nextElementSibling) {
                event.target.nextElementSibling.classList.remove('d-none');
                event.target.nextElementSibling.classList.add('d-flex');
            }
        }
    }, true);
})();
</script>
@stack('scripts')
</body>
</html>
