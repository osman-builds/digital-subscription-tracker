<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FxRateService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FxRateConversionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cache.default', 'array');
        Cache::flush();
        Cache::put('fx_rates_myr', [
            'MYR' => 1.0,
            'USD' => 4.5,
            'EUR' => 5.0,
            'SGD' => 3.0,
        ], 3600);
    }

    public function test_it_converts_between_currencies_using_myr_pivot(): void
    {
        $fx = new FxRateService;

        $this->assertSame(45.0, $fx->toMyr(10.0, 'usd'));
        $this->assertSame(10.0, $fx->toMyr(10.0, 'MYR'));
        $this->assertSame(9.0, $fx->convert(10.0, 'USD', 'EUR'));
        $this->assertSame(15.0, $fx->convert(10.0, 'USD', 'SGD'));
        $this->assertSame(10.0, $fx->convert(10.0, 'EUR', 'EUR'));
    }

    public function test_it_computes_exact_cross_currency_rates(): void
    {
        $fx = new FxRateService;

        $this->assertSame(1.0, $fx->rate('USD', 'USD'));
        $this->assertSame(4.5, $fx->rate('USD', 'MYR'));
        $this->assertSame(0.9, $fx->rate('USD', 'EUR'));
        $this->assertSame(1.5, $fx->rate('USD', 'SGD'));
    }

    public function test_sandbox_conversion_falls_back_gracefully_when_cache_is_empty(): void
    {
        Cache::flush();
        $fx = new FxRateService;

        $this->assertSame(44.7, $fx->convertForSandbox(10.0, 'USD', 'MYR'));
        $this->assertSame(10.0, $fx->convertForSandbox(10.0, 'USD', 'USD'));
        $this->assertSame(round(44.7 / 4.85, 2), $fx->convertForSandbox(10.0, 'USD', 'EUR'));
    }

    public function test_it_returns_expected_currency_symbols(): void
    {
        $fx = new FxRateService;

        $this->assertSame('RM', $fx->symbol('myr'));
        $this->assertSame('$', $fx->symbol('USD'));
        $this->assertSame('€', $fx->symbol('EUR'));
        $this->assertSame('£', $fx->symbol('GBP'));
        $this->assertSame('S$', $fx->symbol('SGD'));
        $this->assertSame('KSh', $fx->symbol('KES'));
        $this->assertSame('THB', $fx->symbol('thb'));
    }
}
