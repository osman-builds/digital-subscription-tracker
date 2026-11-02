@extends('layouts.app')
@section('title', 'Settings – SubShield')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Settings</h4>
        <small class="text-muted">Manage your preferences, alerts, and account</small>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ─── LEFT COLUMN ───────────────────────────────────────────── --}}
    <div class="col-12 col-lg-7 d-flex flex-column gap-4">

        {{-- 1. USER PREFERENCES ---------------------------------------- --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-sliders2 text-primary me-2"></i>User Preferences
                </h6>
                <small class="text-muted">Display currency and interface options</small>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.preferences') }}" method="POST">
                    @csrf @method('PATCH')

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label for="base_currency" class="form-label fw-semibold">Base Display Currency</label>
                            <select id="base_currency" name="base_currency" class="form-select">
                                @foreach([
                                    'MYR' => 'MYR – Malaysian Ringgit',
                                    'USD' => 'USD – US Dollar',
                                    'EUR' => 'EUR – Euro',
                                    'GBP' => 'GBP – British Pound',
                                    'SGD' => 'SGD – Singapore Dollar',
                                    'AUD' => 'AUD – Australian Dollar',
                                ] as $code => $label)
                                    <option value="{{ $code }}" {{ $settings->base_currency === $code ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">SubShield always normalises to MYR internally; this affects display labels.</div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <span class="form-label fw-semibold d-block">Dark Mode</span>
                            <div class="d-flex align-items-center gap-3 mt-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="darkModeToggle" {{ $settings->dark_mode ? 'checked' : '' }}>
                                    <label class="form-check-label" for="darkModeToggle">
                                        <span id="darkModeLabel">{{ $settings->dark_mode ? 'Dark' : 'Light' }} mode</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-text">Toggle saves instantly and persists across sessions.</div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- 2. ALERT PREFERENCES --------------------------------- --}}
                    <div>
                        <p class="fw-semibold mb-2"><i class="bi bi-bell text-warning me-2"></i>Renewal Alert Lead Times</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="alert_7_days" id="alert7" value="1"
                                       {{ $settings->alert_7_days ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert7">
                                    <span class="fw-semibold">7-day reminder</span>
                                    <span class="text-muted ms-1">— Show on dashboard 7 days before renewal</span>
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="alert_3_days" id="alert3" value="1"
                                       {{ $settings->alert_3_days ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert3">
                                    <span class="fw-semibold">3-day reminder</span>
                                    <span class="text-muted ms-1">— Highlight urgently on dashboard 3 days before renewal</span>
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

        {{-- 3. ACCOUNT DETAILS ----------------------------------------- --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-person-circle text-info me-2"></i>Account Details
                </h6>
                <small class="text-muted">Update your name and email address</small>
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
                            <i class="bi bi-pencil me-1"></i>Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 4. CHANGE PASSWORD ------------------------------------------ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-lock-fill text-danger me-2"></i>Change Password
                </h6>
                <small class="text-muted">Use a strong password with mixed case and numbers</small>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.password') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Enter current password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" id="new_password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min 8 chars, mixed case + numbers">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" placeholder="Repeat new password">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- /left column --}}

    {{-- ─── RIGHT COLUMN ──────────────────────────────────────────── --}}
    <div class="col-12 col-lg-5 d-flex flex-column gap-4">

        {{-- ABOUT SUBSHIELD -------------------------------------------- --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
                {{-- Header banner --}}
                <div class="d-flex align-items-center gap-3 p-4 about-banner">                    <img src="/favicon.svg" width="52" height="52" alt="SubShield icon">
                    <div>
                        <h5 class="mb-0 fw-bold text-white">SubShield</h5>
                        <span class="badge bg-primary">v1.0</span>
                    </div>
                </div>

                <div class="p-4">
                    <p class="text-muted mb-3">
                        A privacy-first digital subscription tracker that normalises foreign
                        currencies to MYR and alerts you before renewal deadlines — with no
                        bank logins or credential scraping required.
                    </p>

                    <hr>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-person-badge text-primary mt-1"></i>
                            <div>
                                <div class="fw-semibold">Developer</div>
                                <div class="text-muted">Osman Abdullahi Ahmed</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-mortarboard text-primary mt-1"></i>
                            <div>
                                <div class="fw-semibold">Programme</div>
                                <div class="text-muted">Bachelor of Software Engineering (Honours)</div>
                                <div class="text-muted small">Universiti Tenaga Nasional (UNITEN)</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-journal-code text-primary mt-1"></i>
                            <div>
                                <div class="fw-semibold">Project</div>
                                <div class="text-muted">Final Year Project — Digital Subscription Tracker</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i>Privacy-First
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                            <i class="bi bi-currency-exchange me-1"></i>MYR Normalisation
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                            <i class="bi bi-bell me-1"></i>Proactive Alerts
                        </span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                            <i class="bi bi-code-slash me-1"></i>Laravel 13
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SYSTEM INFO ------------------------------------------------ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-info-circle text-secondary me-2"></i>System Info</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">App Version</span>
                        <span class="fw-semibold">SubShield v1.0</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Framework</span>
                        <span class="fw-semibold">Laravel {{ app()->version() }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">PHP</span>
                        <span class="fw-semibold">{{ PHP_VERSION }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Your Account</span>
                        <span class="fw-semibold">{{ auth()->user()->email }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Member Since</span>
                        <span class="fw-semibold">{{ auth()->user()->created_at->format('M Y') }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">FX Rates</span>
                        @php $fxTs = cache('fx_rates_myr_updated_at'); @endphp
                        <span class="fw-semibold">
                            {{ $fxTs ? \Carbon\Carbon::parse($fxTs)->diffForHumans() : 'Fallback rates' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>

    </div>{{-- /right column --}}

</div>{{-- /row --}}
@endsection

@push('scripts')
{{-- Dark mode toggle is wired globally in the layout --}}
@endpush
