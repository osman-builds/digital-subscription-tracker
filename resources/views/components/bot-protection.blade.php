@props(['action' => 'auth'])

@php
    $turnstileSiteKey = config('services.turnstile.site_key');
    $turnstileEnabled = filled($turnstileSiteKey) && filled(config('services.turnstile.secret_key'));
@endphp

<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
    <label for="website_hp">Website</label>
    <input type="text" name="website_hp" id="website_hp" value="" tabindex="-1" autocomplete="off">
</div>

<input type="hidden" name="client_device_token" id="client_device_token" value="">

@if($turnstileEnabled)
    <div class="mb-3">
        <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-action="{{ $action }}" data-theme="light"></div>
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            var token = btoa(JSON.stringify({
                sw: window.screen ? window.screen.width : 0,
                sh: window.screen ? window.screen.height : 0,
                cd: window.screen ? window.screen.colorDepth : 0,
                tp: navigator.maxTouchPoints || 0,
                hc: navigator.hardwareConcurrency || 1,
                tz: new Date().getTimezoneOffset()
            }));
            var inputs = document.querySelectorAll('input[name="client_device_token"]');
            inputs.forEach(function (input) { input.value = token; });
        } catch (e) {}
    });
</script>
@endonce
