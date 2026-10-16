<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SubShield')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            padding-top: 1rem;
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
            color: rgba(255,255,255,.7);
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
        .main-content { margin-left: 250px; padding: 2rem; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .table th { font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; }
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; min-height: auto; }
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar d-none d-md-flex flex-column">
        <div class="brand">
            <i class="bi bi-shield-shaded me-2 text-primary"></i>SubShield
        </div>
        <ul class="nav flex-column mt-3 flex-grow-1">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('subscriptions.index') }}" class="nav-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
                    <i class="bi bi-list-ul me-2"></i>Subscriptions
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('expenditure.index') }}" class="nav-link {{ request()->routeIs('expenditure.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line me-2"></i>Expenditure
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item mt-3 px-3">
                <a href="{{ route('subscriptions.create') }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-plus-lg me-1"></i>Add Subscription
                </a>
            </li>
        </ul>

        <!-- User info + logout -->
        <div class="sidebar-footer">
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

    <!-- Mobile top nav -->
    <nav class="navbar navbar-dark bg-dark d-md-none w-100 position-fixed top-0" style="z-index:200;">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="bi bi-shield-shaded me-1 text-primary"></i>SubShield</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <div class="offcanvas offcanvas-start bg-dark text-white" id="mobileNav">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">SubShield</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <ul class="nav flex-column flex-grow-1">
                <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link text-white-50 px-4 py-3"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="{{ route('subscriptions.index') }}" class="nav-link text-white-50 px-4 py-3"><i class="bi bi-list-ul me-2"></i>Subscriptions</a></li>
                <li class="nav-item"><a href="{{ route('expenditure.index') }}" class="nav-link text-white-50 px-4 py-3"><i class="bi bi-bar-chart-line me-2"></i>Expenditure</a></li>
                <li class="nav-item"><a href="{{ route('categories.index') }}" class="nav-link text-white-50 px-4 py-3"><i class="bi bi-tags me-2"></i>Categories</a></li>
                <li class="nav-item p-3"><a href="{{ route('subscriptions.create') }}" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add Subscription</a></li>
            </ul>
            <div class="p-3 border-top border-secondary">
                <div class="text-white-50 small mb-2">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Page content -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
