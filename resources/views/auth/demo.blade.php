@extends('layouts.auth')

@section('title', 'Interactive Sandbox — SubShield')
@section('heading', '1-Hour Interactive Sandbox')
@section('subheading', 'Explore SubShield safely in a private session with realistic subscription data.')

@section('content')
<style>
    .auth-card {
        max-width: 520px !important;
    }
    .sandbox-feature-item {
        background: rgba(99, 102, 241, 0.05);
        border: 1px solid rgba(99, 102, 241, 0.12);
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        font-size: 0.85rem;
    }
</style>

<div class="mb-3">
    <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded-3 mb-3" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25);">
        <span class="text-success small fw-semibold">
            <i class="bi bi-shield-check me-1"></i> Ephemeral In-Memory Session
        </span>
        <span class="badge bg-success bg-opacity-75 text-white" style="font-size: 0.72rem;">1 Hour</span>
    </div>

    <div class="small text-muted mb-2 fw-semibold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.75rem;">
        Pre-Loaded Intelligence Showcase
    </div>

    <div class="row g-2 mb-3">
        <div class="col-12 col-sm-6">
            <div class="sandbox-feature-item">
                <i class="bi bi-collection text-primary me-1"></i>
                <strong class="text-dark">10 Subscriptions</strong>
                <div class="text-muted small">ChatGPT, Netflix, Figma, etc.</div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="sandbox-feature-item">
                <i class="bi bi-graph-up-arrow text-danger me-1"></i>
                <strong class="text-dark">Price Creep Alert</strong>
                <div class="text-muted small">Netflix +15% stealth inflation</div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="sandbox-feature-item">
                <i class="bi bi-alarm text-warning me-1"></i>
                <strong class="text-dark">Trial-Shock Guard</strong>
                <div class="text-muted small">Figma Pro expiring in 3 days</div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="sandbox-feature-item">
                <i class="bi bi-file-earmark-pdf text-info me-1"></i>
                <strong class="text-dark">CSV &amp; PDF Exports</strong>
                <div class="text-muted small">Printable executive audit reports</div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border small text-start mb-3 py-2 px-3">
        <div class="fw-semibold text-dark mb-1">
            <i class="bi bi-shield-lock text-primary me-1"></i>Session Isolation Guarantee
        </div>
        <ul class="mb-0 ps-3 text-muted" style="font-size: 0.82rem;">
            <li>All additions and edits run in your private session and are <strong>never saved to the database</strong>.</li>
            <li>Session automatically self-destructs after 60 minutes or upon logout.</li>
            <li>Outbound webhooks and live SMTP email alerts are disabled in sandbox mode.</li>
        </ul>
    </div>
</div>

<form method="POST" action="{{ route('demo.start') }}">
    @csrf

    <x-bot-protection action="sandbox" />

    <button type="submit" class="btn btn-indigo w-100 fw-semibold py-2">
        <i class="bi bi-play-circle me-1"></i>Launch 1-Hour Sandbox
    </button>
</form>

<hr class="my-3">
<div class="d-flex justify-content-between small">
    <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Create Free Account</a>
    <a href="{{ route('login') }}" class="text-muted text-decoration-none">Sign In</a>
</div>
@endsection
