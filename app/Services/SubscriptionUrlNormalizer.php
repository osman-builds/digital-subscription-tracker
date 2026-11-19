<?php

namespace App\Services;

use Illuminate\Http\Request;

class SubscriptionUrlNormalizer
{
    private const HTTPS_SCHEME = 'https://';

    public function __construct(
        private readonly BrandIconService $brandIcons
    ) {}

    /**
     * Normalize website and cancellation URLs and auto-fill logo URL if needed.
     */
    public function normalize(Request $request): void
    {
        $this->normalizeWebsiteUrl($request);
        $this->normalizeCancellationUrl($request);
        $this->inferBrandDetailsFromName($request);
        $this->inferNameFromWebsite($request);
    }

    private function normalizeWebsiteUrl(Request $request): void
    {
        if (! $request->filled('website_url')) {
            return;
        }

        $url = trim((string) $request->input('website_url'));
        if (str_starts_with(strtolower($url), 'http://')) {
            $url = self::HTTPS_SCHEME.substr($url, 7);
        } elseif (! preg_match('~^(?:[a-z][a-z0-9+.-]*://|javascript:|data:|vbscript:)~i', $url)) {
            $url = self::HTTPS_SCHEME.$url;
        }
        $request->merge(['website_url' => $url]);

        if (! $request->filled('logo_url')) {
            $cleanHost = $this->brandIcons->cleanHost($url);
            if ($cleanHost !== '') {
                $request->merge([
                    'logo_url' => $this->brandIcons->googleFaviconUrl($cleanHost),
                ]);
            }
        }
    }

    private function normalizeCancellationUrl(Request $request): void
    {
        if (! $request->filled('cancellation_url')) {
            return;
        }

        $url = trim((string) $request->input('cancellation_url'));
        if (str_starts_with(strtolower($url), 'http://')) {
            $url = self::HTTPS_SCHEME.substr($url, 7);
        } elseif (! preg_match('~^(?:[a-z][a-z0-9+.-]*://|javascript:|data:|vbscript:)~i', $url)) {
            $url = self::HTTPS_SCHEME.$url;
        }
        $request->merge(['cancellation_url' => $url]);
    }

    private function inferBrandDetailsFromName(Request $request): void
    {
        if (! $request->filled('name') || $request->filled('website_url')) {
            return;
        }

        $matched = $this->brandIcons->matchByName((string) $request->input('name'));

        if ($matched !== null) {
            $domain = $matched['domain'];
            $request->merge([
                'website_url' => self::HTTPS_SCHEME.$domain,
                'logo_url' => $this->brandIcons->googleFaviconUrl($domain),
            ]);
        }
    }

    private function inferNameFromWebsite(Request $request): void
    {
        if ($request->filled('name') || ! $request->filled('website_url')) {
            return;
        }

        $host = parse_url((string) $request->input('website_url'), PHP_URL_HOST) ?? '';
        $cleanHost = preg_replace('/^www\./', '', (string) $host);
        $parts = explode('.', $cleanHost);
        $derivedName = ucfirst($parts[0] ?? 'Subscription');
        $request->merge(['name' => $derivedName]);
    }
}
