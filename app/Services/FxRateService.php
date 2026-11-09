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

        if ($to === self::TARGET) {
            return round($amountInMyr, 2);
        }

        $rates = $this->getRates();
        $rateTo = $rates[$to] ?? null;

        if (! $rateTo || $rateTo <= 0) {
            Log::warning("FxRateService: Unknown target currency [{$to}], returning MYR equivalent");

            return round($amountInMyr, 2);
        }

        return round($amountInMyr / $rateTo, 2);
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

    // -------------------------------------------------------------------------

    private function fetchFromApi(): array
    {
        $apiKey = config('services.exchangerate.key');

        if (empty($apiKey)) {
            Log::info('FxRateService: No API key set, using fallback rates.');

            return $this->fallbackRates();
        }

        try {
            $baseUrl = config('services.exchangerate.base_url');
            // We fetch USD base, then invert to get "how many MYR per 1 foreign unit"
            $response = Http::timeout(10)->get("{$baseUrl}/{$apiKey}/latest/USD");

            if ($response->successful()) {
                $data = $response->json();

                if (($data['result'] ?? '') === 'success') {
                    $usdRates = $data['conversion_rates']; // rates from 1 USD

                    // MYR per 1 USD
                    $usdToMyr = $usdRates[self::TARGET] ?? $this->fallbackRates()['USD'];

                    // Build "MYR per 1 foreign currency" map
                    $myrRates = [];
                    foreach ($usdRates as $code => $rateFromUsd) {
                        if ($rateFromUsd > 0) {
                            // 1 foreign = (1/rateFromUsd) USD * usdToMyr MYR
                            $myrRates[$code] = round($usdToMyr / $rateFromUsd, 6);
                        }
                    }

                    Cache::put(self::CACHE_KEY.'_updated_at', now()->toDateTimeString(), self::CACHE_TTL_SECONDS);

                    return $myrRates;
                }
            }

            Log::warning('FxRateService: API returned unexpected response, using fallback.', [
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('FxRateService: HTTP request failed.', ['error' => $e->getMessage()]);
        }

        return $this->fallbackRates();
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
