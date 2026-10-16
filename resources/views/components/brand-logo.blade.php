{{--
    Brand Logo Component
    Usage: <x-brand-logo :subscription="$sub" size="38" />

    Resolution order:
      1. subscription->logo_url  (manually set)
      2. Google Favicon API      (s2.googleapis.com/faviconV2 — no API key required, free)
      3. Coloured initial fallback

    Google Favicon API params:
      domain  = the service domain (e.g. netflix.com)
      sz      = pixel size (16/32/64/128/256)
      type    = FAVICON (gets best available icon)
--}}
@props([
    'subscription',
    'size'   => 40,
    'rounded' => 'rounded-2',
])

@php
    $name    = $subscription->name ?? '?';
    $logoUrl = $subscription->logo_url ?? null;
    $domain  = null;

    // Derive domain from website_url if no explicit logo
    if (!$logoUrl && !empty($subscription->website_url)) {
        $host   = parse_url($subscription->website_url, PHP_URL_HOST) ?? '';
        $domain = preg_replace('/^www\./', '', $host);
    }

    // Google Favicon API URL (no key required, publicly accessible)
    $faviconUrl = $domain
        ? "https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://{$domain}&size=64"
        : null;

    // Colour from category, or default indigo
    $color = $subscription->category->color ?? '#6366f1';

    // First letter for fallback
    $initial = strtoupper(mb_substr($name, 0, 1));
@endphp

<div class="{{ $rounded }} d-inline-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden"
     style="width:{{ $size }}px;height:{{ $size }}px;background:{{ $color }}22;">

    @if($logoUrl)
        {{-- Manually set logo URL --}}
        <img src="{{ $logoUrl }}"
             alt="{{ $name }} logo"
             width="{{ $size }}"
             height="{{ $size }}"
             class="{{ $rounded }}"
             style="object-fit:contain;width:{{ $size }}px;height:{{ $size }}px;"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <span class="d-none align-items-center justify-content-center w-100 h-100 fw-bold"
              style="color:{{ $color }};font-size:{{ round($size * 0.45) }}px;">{{ $initial }}</span>

    @elseif($faviconUrl)
        {{-- Google Favicon resolution --}}
        <img src="{{ $faviconUrl }}"
             alt="{{ $name }} logo"
             width="{{ round($size * 0.7) }}"
             height="{{ round($size * 0.7) }}"
             loading="lazy"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <span class="d-none align-items-center justify-content-center w-100 h-100 fw-bold"
              style="color:{{ $color }};font-size:{{ round($size * 0.45) }}px;">{{ $initial }}</span>

    @else
        {{-- Pure initial fallback --}}
        <span class="fw-bold" style="color:{{ $color }};font-size:{{ round($size * 0.45) }}px;">{{ $initial }}</span>
    @endif

</div>
