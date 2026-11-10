@extends('layouts.app')
@section('title', 'Settings – SubShield')

@push('styles')
<style>
    .settings-nav .nav-link {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        color: var(--bs-body-color);
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.15s ease-in-out;
        text-decoration: none;
        margin-bottom: 0.35rem;
        border: 1px solid transparent;
    }
    .settings-nav .nav-link:hover {
        background-color: rgba(99, 102, 241, 0.08);
        color: #6366f1;
        border-color: rgba(99, 102, 241, 0.15);
    }
    .settings-nav .nav-link.active {
        background-color: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
        font-weight: 600;
        border-color: rgba(99, 102, 241, 0.25);
    }
    [data-bs-theme="dark"] .settings-nav .nav-link:hover {
        background-color: rgba(129, 140, 248, 0.12);
        color: #a5b4fc;
        border-color: rgba(129, 140, 248, 0.2);
    }
    [data-bs-theme="dark"] .settings-nav .nav-link.active {
        background-color: rgba(129, 140, 248, 0.18);
        color: #c7d2fe;
        border-color: rgba(129, 140, 248, 0.35);
    }
    .settings-nav .nav-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        flex-shrink: 0;
    }
    .settings-nav-profile-card {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(14, 165, 233, 0.05) 100%);
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Settings</h4>
        <small class="text-muted">Manage your preferences, security credentials, and account data</small>
    </div>
    <div>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
            <i class="bi bi-shield-check me-1"></i>Protected by Cloudflare Edge
        </span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('status'))
    <div class="alert alert-info alert-dismissible fade show shadow-sm mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- ─── LEFT SUB-NAVIGATION COLUMN ───────────────────────────── --}}
    <div class="col-12 col-lg-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-2 settings-nav">
                <a href="{{ route('settings.index') }}"
                   class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                    <span class="nav-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-sliders2 fs-5"></i>
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate">Preferences</div>
                        <small class="text-muted d-block text-truncate">Currency, theme, alerts</small>
                    </div>
                </a>

                <a href="{{ route('settings.profile') }}"
                   class="nav-link {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
                    <span class="nav-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-person-circle fs-5"></i>
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate">Profile &amp; Account</div>
                        <small class="text-muted d-block text-truncate">Identity &amp; danger zone</small>
                    </div>
                </a>

                <a href="{{ route('settings.security') }}"
                   class="nav-link {{ request()->routeIs('settings.security') ? 'active' : '' }}">
                    <span class="nav-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate">Security</div>
                        <small class="text-muted d-block text-truncate">Password &amp; sessions</small>
                    </div>
                </a>

                <a href="{{ route('settings.about') }}"
                   class="nav-link {{ request()->routeIs('settings.about') ? 'active' : '' }}">
                    <span class="nav-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate">About &amp; Privacy</div>
                        <small class="text-muted d-block text-truncate">Guarantees &amp; telemetry</small>
                    </div>
                </a>
            </div>
        </div>

        {{-- USER IDENTITY SUMMARY BADGE --}}
        <div class="card border-0 shadow-sm settings-nav-profile-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm"
                         style="width: 44px; height: 44px; flex-shrink: 0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
                        <div class="text-muted small text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Account Status</small>
                    @if(auth()->user()->hasVerifiedEmail())
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">
                            <i class="bi bi-patch-check-fill me-1"></i>Verified
                        </span>
                    @else
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 small">
                            <i class="bi bi-clock-history me-1"></i>Pending
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ─── RIGHT CONTENT CANVAS COLUMN ──────────────────────────── --}}
    <div class="col-12 col-lg-9">
        @yield('settings_content')
    </div>
</div>
@endsection
