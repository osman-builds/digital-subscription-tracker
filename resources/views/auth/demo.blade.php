@extends('layouts.auth')

@section('title', 'Interactive Sandbox — SubShield')
@section('heading', '1-Hour Interactive Sandbox')
@section('subheading', 'Explore SubShield safely in a private session with sample subscriptions.')

@section('content')
    <div class="alert alert-light border small text-start mb-3">
        <div class="fw-semibold text-dark mb-1">
            <i class="bi bi-shield-lock text-primary me-1"></i>How the Sandbox Works
        </div>
        <ul class="mb-0 ps-3 text-muted">
            <li>Runs strictly in your temporary browser session for <strong>1 hour</strong>.</li>
            <li>Any subscriptions, categories, or edits you test are <strong>never saved to the database</strong> and clear automatically on expiry or logout.</li>
            <li>Live domain icon API lookups and email alerts are disabled in sandbox mode and reserved for verified accounts.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('demo.start') }}">
        @csrf

        <x-bot-protection />

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
