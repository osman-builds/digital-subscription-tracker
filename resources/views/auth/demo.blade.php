@extends('layouts.auth')

@section('title', 'Try Demo — SubShield')
@section('heading', 'Try SubShield Demo')
@section('subheading', 'Explore the full dashboard with sample subscriptions — no account needed.')

@section('content')
<p class="text-muted small text-center mb-4">
    Add, edit, or scan receipts freely to see how SubShield tracks renewals, spending trends, and currency conversion.
</p>

<form method="POST" action="{{ route('demo.start') }}">
    @csrf

    <x-bot-protection action="sandbox" />

    <button type="submit" class="btn btn-indigo w-100 fw-semibold py-2">
        <i class="bi bi-play-circle me-1"></i>Launch Demo
    </button>
</form>

<hr class="my-3">
<div class="d-flex justify-content-between small">
    <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Create Free Account</a>
    <a href="{{ route('login') }}" class="text-muted text-decoration-none">Sign In</a>
</div>

<x-ai-chat-bubble mode="concierge" />
@endsection
