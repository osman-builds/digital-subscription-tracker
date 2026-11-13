@extends('layouts.auth')

@section('title', 'Confirm Password — SubShield')
@section('heading', 'Confirm Password')
@section('subheading', 'This is a secure area of SubShield. Please confirm your password before continuing.')

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required autofocus autocomplete="current-password">
            </div>
        </div>

        <button type="submit" class="btn btn-indigo w-100 fw-semibold py-2">
            <i class="bi bi-check2-circle me-1"></i>Confirm
        </button>
    </form>
@endsection
