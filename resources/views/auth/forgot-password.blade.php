@extends('layouts.auth')

@section('title', 'Forgot Password — SubShield')
@section('heading', 'Forgot Password')
@section('subheading', 'Enter your email and we will send you a password reset link.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       required autocomplete="email" placeholder="you@example.com">
            </div>
        </div>

        <button type="submit" class="btn btn-indigo w-100 fw-semibold py-2">
            <i class="bi bi-send me-1"></i>Email Password Reset Link
        </button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i>Back to Sign In
            </a>
        </div>
    </form>
@endsection
