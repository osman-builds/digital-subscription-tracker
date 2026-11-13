@php
    $turnstileSiteKey = config('services.turnstile.site_key');
    $turnstileEnabled = filled($turnstileSiteKey) && filled(config('services.turnstile.secret_key'));
@endphp

<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
    <label for="website_hp">Website</label>
    <input type="text" name="website_hp" id="website_hp" value="" tabindex="-1" autocomplete="off">
</div>

@if($turnstileEnabled)
    <div class="mb-3">
        <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-theme="light"></div>
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
