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
                    <small class="text-muted">Ensure your account is protected with a strong, distinct password</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            @if($user->email === 'demo@subtracker.test')
                <div class="alert alert-info py-3 mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i><strong>Evaluation Account:</strong> The demo account password is fixed (<code>password</code>) for continuous evaluator access.
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
                               {{ $user->email === 'demo@subtracker.test' ? 'disabled' : '' }}>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" id="new_password" name="password"
                               class="form-control form-control-lg fs-6 @error('password') is-invalid @enderror"
                               placeholder="Min 8 characters (mixed case + number)"
                               {{ $user->email === 'demo@subtracker.test' ? 'disabled' : '' }}>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control form-control-lg fs-6" placeholder="Repeat new password"
                               {{ $user->email === 'demo@subtracker.test' ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold"
                            {{ $user->email === 'demo@subtracker.test' ? 'disabled' : '' }}>
                        <i class="bi bi-key me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. ACTIVE SESSION & ENVIRONMENT PROTECTIONS --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 rounded-2 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shield-check fs-5"></i>
                </span>
                <div>
                    <h6 class="fw-bold mb-0">Active Security Controls</h6>
                    <small class="text-muted">Built-in protections defending your session and data in real time</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small"><i class="bi bi-cookie text-primary me-2"></i>HTTP-Only Cookies</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">Active</span>
                        </div>
                        <p class="text-muted small mb-0">Authentication cookies are strictly restricted to HTTP transmission, preventing browser scripts from reading your session token.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small"><i class="bi bi-fingerprint text-info me-2"></i>CSRF Protection</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">Active</span>
                        </div>
                        <p class="text-muted small mb-0">Every state-altering request is signed with a cryptographic token verified by the backend kernel.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small"><i class="bi bi-shield-shaded text-warning me-2"></i>Strict SameSite Policy</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">Active</span>
                        </div>
                        <p class="text-muted small mb-0">Cookies are isolated to first-party requests to block third-party cross-site request forgery attacks.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small"><i class="bi bi-cloud-check text-primary me-2"></i>Cloudflare Anycast WAF</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">Active</span>
                        </div>
                        <p class="text-muted small mb-0">Traffic is filtered at the global edge with Bot Fight Mode and automated scanner blocking before reaching origin.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TWO-FACTOR AUTHENTICATION (TOTP) ROADMAP --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="p-2 rounded-2 bg-secondary bg-opacity-10 text-secondary flex-shrink-0">
                        <i class="bi bi-phone fs-5"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0">Two-Factor Authentication (2FA)</h6>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 small">Roadmap</span>
                        </div>
                        <p class="text-muted small mb-0">
                            Support for authenticator apps (Google Authenticator, Microsoft Authenticator, 1Password) using time-based one-time passwords (TOTP) will be introduced in the next security milestone.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
