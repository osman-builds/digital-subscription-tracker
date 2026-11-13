@extends('layouts.auth')

@section('title', 'Reset Password — SubShield')
@section('heading', 'Reset Password')
@section('subheading', 'Choose a strong new password for your SubShield account.')

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                       class="form-control @error('email') is-invalid @enderror"
                       required autocomplete="username">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required autocomplete="new-password">
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       required autocomplete="new-password">
            </div>
        </div>

        <button type="submit" class="btn btn-indigo w-100 fw-semibold py-2">
            <i class="bi bi-check-circle me-1"></i>Reset Password
        </button>
    </form>
@endsection
