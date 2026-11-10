<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FxRateService
{
    /**
     * Cache duration: 6 hours (free tier has 1500 req/month ≈ 50/day, so 4/day is safe)
     */
    private const CACHE_TTL_SECONDS = 21600;

    private const CACHE_KEY = 'fx_rates_myr';

    private const TARGET = 'MYR';

    /**
     * Get exchange rates with MYR as the base (how many MYR per 1 foreign unit).
     * Falls back to hardcoded approximates if API is unavailable.
     *
     * @return array<string, float> e.g. ['USD' => 4.47, 'EUR' => 4.85, ...]
     */
    public function getRates(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchFromApi();
        });
    }

    /**
     * Convert an amount in a given currency to MYR.
     */
    public function toMyr(float $amount, string $currency): float
    {
        $currency = strtoupper(trim($currency));

        if ($currency === self::TARGET) {
            return $amount;
        }

        $rates = $this->getRates();

        if (! isset($rates[$currency])) {
            Log::warning("FxRateService: Unknown currency [{$currency}], returning original amount");

            return $amount;
        }

        return round($amount * $rates[$currency], 2);
    }

    /**
     * Convert an amount between any two supported currencies.
     */
    public function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return round($amount, 2);
        }

        $amountInMyr = $this->toMyr($amount, $from);
        $rateTo = $this->getRates()[$to] ?? null;

        if ($to !== self::TARGET && $rateTo > 0) {
            return round($amountInMyr / $rateTo, 2);
        }

        if ($to !== self::TARGET) {
            Log::warning("FxRateService: Unknown target currency [{$to}], returning MYR equivalent");
        }

        return round($amountInMyr, 2);
    }

    /**
     * Get a human-readable currency symbol or code.
     */
    public function symbol(string $currency): string
    {
        return match (strtoupper(trim($currency))) {
            'MYR' => 'RM',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'SGD' => 'S$',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'JPY', 'CNY' => '¥',
            'INR' => '₹',
            'KES' => 'KSh',
            'CHF' => 'CHF',
            'NZD' => 'NZ$',
            default => strtoupper(trim($currency)),
        };
    }

    /**
     * Force refresh the cached rates.
     */
    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);

        return $this->getRates();
    }

    /**
     * Return when the cache was last updated.
     */
    public function lastUpdated(): ?string
    {
        $timestamp = Cache::get(self::CACHE_KEY.'_updated_at');

        if ($timestamp === null) {
            $this->getRates();
            $timestamp = Cache::get(self::CACHE_KEY.'_updated_at');
        }

        return $timestamp;
    }

    /**
     * Determine whether the service is currently operating on fallback rates.
     */
    public function isFallback(): bool
    {
        if (! Cache::has(self::CACHE_KEY)) {
            $this->getRates();
        }

        return (bool) Cache::get(self::CACHE_KEY.'_is_fallback', false);
    }

    // -------------------------------------------------------------------------

    private function fetchFromApi(): array
    {
        $apiKey = config('services.exchangerate.key');
        $baseUrl = config('services.exchangerate.base_url');

        // 1. Try keyed ExchangeRate-API endpoint if an API key is configured
        if (! empty($apiKey)) {
            try {
                $response = Http::timeout(10)->get("{$baseUrl}/{$apiKey}/latest/USD");

                if ($response->successful()) {
                    $data = $response->json();

                    if (($data['result'] ?? '') === 'success') {
                        return $this->processApiRates($data['conversion_rates'] ?? $data['rates'] ?? []);
                    }
                }

                Log::warning('FxRateService: Keyed API returned unexpected response, attempting open fallback endpoint.', [
                    'status' => $response->status(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('FxRateService: Keyed HTTP request failed, trying open public endpoint.', ['error' => $e->getMessage()]);
            }
        }

        // 2. Fallback to ExchangeRate-API open public endpoint (requires zero API keys)
        try {
            $openResponse = Http::timeout(10)->get('https://open.er-api.com/v6/latest/USD');

            if ($openResponse->successful()) {
                $data = $openResponse->json();

                if (($data['result'] ?? '') === 'success') {
                    return $this->processApiRates($data['rates'] ?? $data['conversion_rates'] ?? []);
                }
            }

            Log::warning('FxRateService: Open public API returned unexpected response.', [
                'status' => $openResponse->status(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('FxRateService: Open public FX API call failed, dropping to offline fallbacks.', ['error' => $e->getMessage()]);
        }

        // 3. If completely offline/unreachable, drop to hardcoded baseline approximations
        Cache::put(self::CACHE_KEY.'_is_fallback', true, self::CACHE_TTL_SECONDS);
        Cache::put(self::CACHE_KEY.'_updated_at', now()->toDateTimeString(), self::CACHE_TTL_SECONDS);

        return $this->fallbackRates();
    }

    /**
     * Parse raw USD-based conversion rates, invert to MYR base, and store cache timestamps.
     *
     * @param  array<string, float|int>  $usdRates
     * @return array<string, float>
     */
    private function processApiRates(array $usdRates): array
    {
        if (empty($usdRates)) {
            Cache::put(self::CACHE_KEY.'_is_fallback', true, self::CACHE_TTL_SECONDS);
            Cache::put(self::CACHE_KEY.'_updated_at', now()->toDateTimeString(), self::CACHE_TTL_SECONDS);

            return $this->fallbackRates();
        }

        $usdToMyr = (float) ($usdRates[self::TARGET] ?? $this->fallbackRates()['USD']);
        $myrRates = [];

        foreach ($usdRates as $code => $rateFromUsd) {
            $rateFloat = (float) $rateFromUsd;
            if ($rateFloat > 0) {
                $myrRates[(string) $code] = round($usdToMyr / $rateFloat, 6);
            }
        }

        Cache::put(self::CACHE_KEY.'_is_fallback', false, self::CACHE_TTL_SECONDS);
        Cache::put(self::CACHE_KEY.'_updated_at', now()->toDateTimeString(), self::CACHE_TTL_SECONDS);

        return $myrRates;
    }

    /**
     * Hardcoded approximate MYR rates (Oct 2026 estimates) used when API is unavailable.
     */
    private function fallbackRates(): array
    {
        return [
            'USD' => 4.47,
            'EUR' => 4.85,
            'GBP' => 5.70,
            'SGD' => 3.32,
            'AUD' => 2.90,
            'JPY' => 0.030,
            'CNY' => 0.62,
            'HKD' => 0.57,
            'INR' => 0.054,
            'CAD' => 3.25,
            'CHF' => 5.05,
            'KRW' => 0.0033,
            'TWD' => 0.14,
            'THB' => 0.13,
            'IDR' => 0.00028,
            'PHP' => 0.077,
            'KES' => 0.035,
            'MYR' => 1.00,
        ];
    }
}
