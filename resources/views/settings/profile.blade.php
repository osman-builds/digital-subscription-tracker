@extends('settings.layout')

@section('settings_content')
<div class="d-flex flex-column gap-4">

    {{-- 1. PROFILE & IDENTITY --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-2 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-person-circle fs-5"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Profile &amp; Identity</h5>
                        <small class="text-muted">Manage your personal credentials and contact email</small>
                    </div>
                </div>
                @if($user->hasVerifiedEmail())
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                        <i class="bi bi-patch-check-fill me-1"></i>Email Verified
                    </span>
                @else
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Verification Pending
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('settings.account') }}" method="POST">
                @csrf @method('PATCH')
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label for="account_name" class="form-label fw-semibold">Full Name</label>
                        <input type="text" id="account_name" name="name" class="form-control form-control-lg fs-6 @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="account_email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" id="account_email" name="email" class="form-control form-control-lg fs-6 @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Changing your email will require completing an email verification check.</div>
                    </div>
                </div>
                <div class="mt-4 pt-2">
                    <button type="submit" class="btn btn-info text-white px-4 py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i>Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. ACCOUNT MEMBERSHIP DETAILS --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-card-checklist text-primary me-2"></i>Account Overview
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-sm-4">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block">Member Since</small>
                        <span class="fw-bold fs-6">{{ $user->created_at?->format('F Y') ?? 'Recently' }}</span>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block">Active Subscriptions</small>
                        @php
                            $subCount = $user->isSandbox()
                                ? app(\App\Services\SandboxSubscriptionStore::class)->all($user->id)->count()
                                : $user->subscriptions()->count();
                            $catCount = $user->isSandbox()
                                ? app(\App\Services\SandboxCategoryStore::class)->all($user->id)->whereNotNull('user_id')->count()
                                : $user->categories()->count();
                        @endphp
                        <span class="fw-bold fs-6">{{ $subCount }} tracked</span>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="p-3 rounded-3 bg-body-tertiary">
                        <small class="text-muted d-block">Custom Categories</small>
                        <span class="fw-bold fs-6">{{ $catCount }} configured</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DANGER ZONE: DELETE ACCOUNT --}}
    <div class="card border border-danger border-opacity-25 shadow-sm">
        <div class="card-header bg-danger bg-opacity-10 border-0 pt-4 pb-2 px-4">
            <h6 class="fw-bold text-danger mb-0">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>Danger Zone
            </h6>
            <small class="text-danger-emphasis">Irreversible data purge and account termination</small>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div style="max-width: 600px;">
                    <h6 class="fw-semibold mb-1">Permanently Delete Account</h6>
                    <p class="text-muted small mb-0">
                        Once your account is deleted, all associated data — including your active subscriptions, spend history, custom categories, and personal preferences — will be permanently wiped from our database. This action cannot be reversed.
                    </p>
                </div>
                <div>
                    @if($user->isSandbox())
                        <button type="button" class="btn btn-outline-danger px-4 py-2" disabled title="The demo account cannot be deleted">
                            <i class="bi bi-lock me-1"></i>Protected Demo Account
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-danger px-4 py-2" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash3 me-1"></i>Delete Account
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

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
