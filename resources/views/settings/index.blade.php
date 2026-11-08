@extends('layouts.app')
@section('title', 'Settings – SubShield')

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
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ─── LEFT COLUMN: ACTIONABLE SETTINGS ───────────────────────── --}}
    <div class="col-12 col-lg-7 d-flex flex-column gap-4">

        {{-- 1. USER PREFERENCES ---------------------------------------- --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-sliders2 fs-5"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0">Preferences & Display</h6>
                        <small class="text-muted">Tracking currency, appearance, and renewal notifications</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.preferences') }}" method="POST">
                    @csrf @method('PATCH')

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label for="base_currency" class="form-label fw-semibold">Base Tracking Currency</label>
                            <select id="base_currency" name="base_currency" class="form-select">
                                <optgroup label="Popular Global Currencies">
                                    <option value="USD" {{ $settings->base_currency === 'USD' ? 'selected' : '' }}>USD – US Dollar ($)</option>
                                    <option value="EUR" {{ $settings->base_currency === 'EUR' ? 'selected' : '' }}>EUR – Euro (€)</option>
                                    <option value="GBP" {{ $settings->base_currency === 'GBP' ? 'selected' : '' }}>GBP – British Pound (£)</option>
                                    <option value="MYR" {{ $settings->base_currency === 'MYR' ? 'selected' : '' }}>MYR – Malaysian Ringgit (RM)</option>
                                    <option value="SGD" {{ $settings->base_currency === 'SGD' ? 'selected' : '' }}>SGD – Singapore Dollar (S$)</option>
                                    <option value="AUD" {{ $settings->base_currency === 'AUD' ? 'selected' : '' }}>AUD – Australian Dollar (A$)</option>
                                    <option value="CAD" {{ $settings->base_currency === 'CAD' ? 'selected' : '' }}>CAD – Canadian Dollar (C$)</option>
                                    <option value="JPY" {{ $settings->base_currency === 'JPY' ? 'selected' : '' }}>JPY – Japanese Yen (¥)</option>
                                    <option value="CNY" {{ $settings->base_currency === 'CNY' ? 'selected' : '' }}>CNY – Chinese Yuan (¥)</option>
                                    <option value="INR" {{ $settings->base_currency === 'INR' ? 'selected' : '' }}>INR – Indian Rupee (₹)</option>
                                </optgroup>
                                <optgroup label="Asia & Pacific">
                                    <option value="IDR" {{ $settings->base_currency === 'IDR' ? 'selected' : '' }}>IDR – Indonesian Rupiah (Rp)</option>
                                    <option value="PHP" {{ $settings->base_currency === 'PHP' ? 'selected' : '' }}>PHP – Philippine Peso (₱)</option>
                                    <option value="THB" {{ $settings->base_currency === 'THB' ? 'selected' : '' }}>THB – Thai Baht (฿)</option>
                                    <option value="VND" {{ $settings->base_currency === 'VND' ? 'selected' : '' }}>VND – Vietnamese Dong (₫)</option>
                                    <option value="KRW" {{ $settings->base_currency === 'KRW' ? 'selected' : '' }}>KRW – South Korean Won (₩)</option>
                                    <option value="HKD" {{ $settings->base_currency === 'HKD' ? 'selected' : '' }}>HKD – Hong Kong Dollar (HK$)</option>
                                    <option value="TWD" {{ $settings->base_currency === 'TWD' ? 'selected' : '' }}>TWD – New Taiwan Dollar (NT$)</option>
                                    <option value="NZD" {{ $settings->base_currency === 'NZD' ? 'selected' : '' }}>NZD – New Zealand Dollar (NZ$)</option>
                                    <option value="PKR" {{ $settings->base_currency === 'PKR' ? 'selected' : '' }}>PKR – Pakistani Rupee (₨)</option>
                                    <option value="BDT" {{ $settings->base_currency === 'BDT' ? 'selected' : '' }}>BDT – Bangladeshi Taka (৳)</option>
                                </optgroup>
                                <optgroup label="Middle East & Africa">
                                    <option value="SAR" {{ $settings->base_currency === 'SAR' ? 'selected' : '' }}>SAR – Saudi Riyal (SAR)</option>
                                    <option value="AED" {{ $settings->base_currency === 'AED' ? 'selected' : '' }}>AED – UAE Dirham (AED)</option>
                                    <option value="QAR" {{ $settings->base_currency === 'QAR' ? 'selected' : '' }}>QAR – Qatari Riyal (QAR)</option>
                                    <option value="KWD" {{ $settings->base_currency === 'KWD' ? 'selected' : '' }}>KWD – Kuwaiti Dinar (KWD)</option>
                                    <option value="EGP" {{ $settings->base_currency === 'EGP' ? 'selected' : '' }}>EGP – Egyptian Pound (EGP)</option>
                                    <option value="ZAR" {{ $settings->base_currency === 'ZAR' ? 'selected' : '' }}>ZAR – South African Rand (R)</option>
                                    <option value="NGN" {{ $settings->base_currency === 'NGN' ? 'selected' : '' }}>NGN – Nigerian Naira (₦)</option>
                                    <option value="KES" {{ $settings->base_currency === 'KES' ? 'selected' : '' }}>KES – Kenyan Shilling (KES)</option>
                                </optgroup>
                                <optgroup label="Europe & Americas">
                                    <option value="CHF" {{ $settings->base_currency === 'CHF' ? 'selected' : '' }}>CHF – Swiss Franc (CHF)</option>
                                    <option value="SEK" {{ $settings->base_currency === 'SEK' ? 'selected' : '' }}>SEK – Swedish Krona (SEK)</option>
                                    <option value="NOK" {{ $settings->base_currency === 'NOK' ? 'selected' : '' }}>NOK – Norwegian Krone (NOK)</option>
                                    <option value="DKK" {{ $settings->base_currency === 'DKK' ? 'selected' : '' }}>DKK – Danish Krone (DKK)</option>
                                    <option value="PLN" {{ $settings->base_currency === 'PLN' ? 'selected' : '' }}>PLN – Polish Zloty (zł)</option>
                                    <option value="TRY" {{ $settings->base_currency === 'TRY' ? 'selected' : '' }}>TRY – Turkish Lira (₺)</option>
                                    <option value="BRL" {{ $settings->base_currency === 'BRL' ? 'selected' : '' }}>BRL – Brazilian Real (R$)</option>
                                    <option value="MXN" {{ $settings->base_currency === 'MXN' ? 'selected' : '' }}>MXN – Mexican Peso ($)</option>
                                    <option value="CLP" {{ $settings->base_currency === 'CLP' ? 'selected' : '' }}>CLP – Chilean Peso ($)</option>
                                    <option value="COP" {{ $settings->base_currency === 'COP' ? 'selected' : '' }}>COP – Colombian Peso ($)</option>
                                    <option value="ARS" {{ $settings->base_currency === 'ARS' ? 'selected' : '' }}>ARS – Argentine Peso ($)</option>
                                </optgroup>
                            </select>
                            <div class="form-text">All dashboard spend charts and expenditure reports will automatically convert and display in this currency.</div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <span class="form-label fw-semibold d-block">Interface Theme</span>
                            <div class="d-flex align-items-center gap-3 mt-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           aria-checked="{{ $settings->dark_mode ? 'true' : 'false' }}"
                                           id="darkModeToggle" {{ $settings->dark_mode ? 'checked' : '' }}>
                                    <label class="form-check-label" for="darkModeToggle">
                                        <span id="darkModeLabel">{{ $settings->dark_mode ? 'Dark' : 'Light' }} mode</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-text">Theme preference saves automatically and syncs across devices.</div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ALERT PREFERENCES --------------------------------- --}}
                    <div>
                        <p class="fw-semibold mb-2"><i class="bi bi-bell text-warning me-2"></i>Renewal Alerts & Lead Times</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       aria-checked="{{ $settings->alert_7_days ? 'true' : 'false' }}"
                                       name="alert_7_days" id="alert7" value="1"
                                       {{ $settings->alert_7_days ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert7">
                                    <span class="fw-semibold">7-day reminder</span>
                                    <span class="text-muted ms-1">— Display on upcoming dashboard renewals 7 days prior</span>
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       aria-checked="{{ $settings->alert_3_days ? 'true' : 'false' }}"
                                       name="alert_3_days" id="alert3" value="1"
                                       {{ $settings->alert_3_days ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert3">
                                    <span class="fw-semibold">3-day urgent reminder</span>
                                    <span class="text-muted ms-1">— Highlight urgently on dashboard 3 days prior</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. ACCOUNT DETAILS ----------------------------------------- --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-2 bg-info bg-opacity-10 text-info">
                            <i class="bi bi-person-circle fs-5"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0">Profile & Identity</h6>
                            <small class="text-muted">Manage your personal credentials and contact address</small>
                        </div>
                    </div>
                    @if(auth()->user()->hasVerifiedEmail())
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                            <i class="bi bi-patch-check-fill me-1"></i>Verified
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.account') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label for="account_name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" id="account_name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="account_email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" id="account_email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-info text-white px-4">
                            <i class="bi bi-save me-1"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 3. CHANGE PASSWORD ------------------------------------------ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0">Security & Authentication</h6>
                        <small class="text-muted">Update password to ensure your account remains protected</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.password') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Enter your current password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" id="new_password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min 8 characters">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" placeholder="Repeat new password">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline-dark px-4">
                            <i class="bi bi-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 4. DANGER ZONE: DELETE ACCOUNT ---------------------------- --}}
        <div class="card border border-danger border-opacity-25 shadow-sm">
            <div class="card-header bg-danger bg-opacity-10 border-0 pt-3 pb-2">
                <h6 class="fw-bold text-danger mb-0">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>Danger Zone
                </h6>
                <small class="text-danger-emphasis">Irreversible data purge and account termination</small>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h6 class="fw-semibold mb-1">Delete Account</h6>
                        <p class="text-muted small mb-0">
                            Permanently delete your account, active subscriptions, custom categories, and all associated personal data from the database.
                        </p>
                    </div>
                    @if(auth()->user()->email === 'demo@subtracker.test')
                        <button type="button" class="btn btn-outline-danger" disabled title="The demo account cannot be deleted">
                            <i class="bi bi-lock me-1"></i>Protected Demo Account
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash3 me-1"></i>Delete Account
                        </button>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /left column --}}

    {{-- ─── RIGHT COLUMN: CONTEXTUAL PRIVACY & SECURITY PANEL ────────── --}}
    <div class="col-12 col-lg-5 d-flex flex-column gap-4">

        {{-- PRIVACY ARCHITECTURE CARD (SANITIZED - NO PII / NO FRAMEWORK FINGERPRINTING) --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
                <div class="d-flex align-items-center justify-content-between p-4 about-banner">
                    <div class="d-flex align-items-center gap-3">
                        <img src="/favicon.svg" width="46" height="46" alt="SubShield icon">
                        <div>
                            <h5 class="mb-0 fw-bold text-white">SubShield</h5>
                            <span class="badge bg-primary bg-opacity-75">v1.0 Production</span>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-25 text-white border border-white border-opacity-25 px-2 py-1">
                        <i class="bi bi-shield-fill-check me-1"></i>Active
                    </span>
                </div>

                <div class="p-4">
                    <p class="text-muted mb-3">
                        A privacy-preserving digital subscription manager designed to track recurring services, normalize foreign currencies in real time, and alert you before renewal deadlines.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-lock-fill text-primary mt-1 fs-5"></i>
                            <div>
                                <div class="fw-semibold small">Zero Credential Scraping</div>
                                <div class="text-muted small">No banking passwords, card numbers, or financial institution logins required.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-lock-fill text-success mt-1 fs-5"></i>
                            <div>
                                <div class="fw-semibold small">End-to-End Edge Encryption</div>
                                <div class="text-muted small">All traffic secured through Cloudflare Anycast CDN with enforced TLS 1.3.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-currency-exchange text-warning mt-1 fs-5"></i>
                            <div>
                                <div class="fw-semibold small">Real-Time FX Normalisation</div>
                                <div class="text-muted small">Automatic multi-currency conversion to your preferred local tracking currency.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-trash3-fill text-danger mt-1 fs-5"></i>
                            <div>
                                <div class="fw-semibold small">Data Sovereignty Guarantee</div>
                                <div class="text-muted small">Instant one-click full database purge whenever you choose to close your account.</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Security & Inquiries</span>
                        <span class="text-primary small fw-semibold">
                            <i class="bi bi-envelope me-1"></i>support@mysubshield.me
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACCOUNT & SYSTEM HEALTH (SANITIZED - NO PHP/LARAVEL VERSION EXPOSURE) --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-activity text-secondary me-2"></i>Account & System Health
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Account Status</span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                            <i class="bi bi-check-circle me-1"></i>Active Member
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Current Account</span>
                        <span class="fw-semibold small">{{ auth()->user()->email }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Member Since</span>
                        <span class="fw-semibold small">{{ auth()->user()->created_at?->format('M Y') ?? 'Recently' }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Base Currency</span>
                        <span class="fw-semibold small">
                            {{ $settings->base_currency }} ({{ app(\App\Services\FxRateService::class)->symbol($settings->base_currency) }})
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">FX Rates Engine</span>
                        @php $fxTs = cache('fx_rates_myr_updated_at'); @endphp
                        <span class="fw-semibold small text-success">
                            <i class="bi bi-check2-circle me-1"></i>{{ $fxTs ? \Carbon\Carbon::parse($fxTs)->diffForHumans() : 'Live Sync' }}
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Session Protection</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            <i class="bi bi-shield-lock me-1"></i>HTTP-Only & CSRF
                        </span>
                    </li>
                </ul>
            </div>
        </div>

    </div>{{-- /right column --}}

</div>{{-- /row --}}

{{-- Delete Account Confirmation Modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </span>
                    <h5 class="modal-title fw-bold text-danger" id="deleteAccountModalLabel">Delete Account</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settings.delete_account') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body py-3">
                    <p class="text-muted mb-3">
                        Are you sure you want to delete your account? This will <strong>immediately and permanently erase all your data</strong>, including your subscriptions, expenditure history, custom categories, and settings from our database.
                    </p>
                    <div class="alert alert-warning py-2 mb-3 small">
                        <i class="bi bi-info-circle me-1"></i><strong>This action cannot be undone.</strong> Please enter your password to confirm.
                    </div>
                    <div class="mb-2">
                        <label for="delete_account_password" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password"
                               id="delete_account_password"
                               name="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Enter your current password"
                               required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i>Permanently Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($errors->userDeletion->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
        modal.show();
    });
</script>
@endif
@endpush
