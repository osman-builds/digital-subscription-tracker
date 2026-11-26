@extends('settings.layout')

@section('settings_content')
<div class="d-flex flex-column gap-4">

    {{-- 1. CHANGE PASSWORD --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0">Change Password</h5>
                    <small class="text-muted">Updating your password automatically signs out other active devices.</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            @if($user->isSandbox())
                <div class="alert alert-info py-3 mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>Password changes are disabled in demo mode. Create a free account to manage your own credentials.
                </div>
            @endif

            <form action="{{ route('settings.password') }}" method="POST">
                @csrf @method('PATCH')
                <div class="row g-4">
                    <div class="col-12">
                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control form-control-lg fs-6 @error('current_password') is-invalid @enderror"
                               placeholder="Enter your current password"
                               {{ $user->isSandbox() ? 'disabled' : '' }}>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" id="new_password" name="password"
                               class="form-control form-control-lg fs-6 @error('password') is-invalid @enderror"
                               placeholder="Min 8 characters (mixed case + number)"
                               {{ $user->isSandbox() ? 'disabled' : '' }}>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control form-control-lg fs-6" placeholder="Repeat new password"
                               {{ $user->isSandbox() ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold"
                            {{ $user->isSandbox() ? 'disabled' : '' }}>
                        <i class="bi bi-key me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. TWO-FACTOR AUTHENTICATION --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <span class="p-2 rounded-2 bg-secondary bg-opacity-10 text-secondary flex-shrink-0">
                    <i class="bi bi-phone fs-5"></i>
                </span>
                <div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                        <h6 class="fw-bold mb-0">Two-Factor Authentication (2FA)</h6>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 small">Coming Soon</span>
                    </div>
                    <p class="text-muted small mb-0">
                        Authenticator app verification (Google Authenticator, Microsoft Authenticator, 1Password) is planned for an upcoming release.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
