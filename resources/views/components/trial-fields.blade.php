@props(['subscription' => null])

@php
    $isTrial = (bool) old('is_trial', $subscription->is_trial ?? false);
    $trialEndsAt = old('trial_ends_at', isset($subscription) && $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') : '');
    $cancellationUrl = old('cancellation_url', $subscription->cancellation_url ?? '');
@endphp

<div class="card bg-light border-0 p-3 rounded-3">
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="sub_is_trial" name="is_trial" value="1"
               {{ $isTrial ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="sub_is_trial">
            <i class="bi bi-gift text-warning me-1"></i>Currently on a Free Trial?
        </label>
    </div>

    <div id="trialEndDateWrapper" class="{{ $isTrial ? '' : 'd-none' }} mb-2">
        <label for="sub_trial_ends_at" class="form-label small fw-semibold">Trial Expiration Date</label>
        <input type="date" id="sub_trial_ends_at" name="trial_ends_at"
               class="form-control form-control-sm @error('trial_ends_at') is-invalid @enderror"
               value="{{ $trialEndsAt }}">
        <div class="form-text small">We will highlight this service so you can cancel before the trial converts to a paid subscription.</div>
        @error('trial_ends_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="mt-2">
        <label for="sub_cancellation_url" class="form-label small fw-semibold">
            <i class="bi bi-box-arrow-up-right me-1 text-muted"></i>Direct Cancellation Page URL <span class="text-muted fw-normal">(Optional)</span>
        </label>
        <input type="url" id="sub_cancellation_url" name="cancellation_url"
               class="form-control form-control-sm @error('cancellation_url') is-invalid @enderror"
               value="{{ $cancellationUrl }}"
               placeholder="e.g. https://www.netflix.com/cancelplan">
        <div class="form-text small">Save direct link to the service's cancellation page for 1-click access when you decide to stop.</div>
        @error('cancellation_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>
