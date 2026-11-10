<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Services\FxRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FxRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_fx_service_uses_keyed_api_when_available(): void
    {
        config([
            'services.exchangerate.key' => 'test-api-key',
            'services.exchangerate.base_url' => 'https://v6.exchangerate-api.com/v6',
        ]);

        Http::fake([
            'https://v6.exchangerate-api.com/v6/test-api-key/latest/USD' => Http::response([
                'result' => 'success',
                'conversion_rates' => [
                    'USD' => 1,
                    'MYR' => 4.20,
                    'EUR' => 0.84,
                ],
            ], 200),
        ]);

        Cache::forget('fx_rates_myr');

        $service = app(FxRateService::class);
        $rates = $service->getRates();

        $this->assertEquals(4.20, $rates['USD']);
        $this->assertEquals(5.00, $rates['EUR']); // 4.20 / 0.84 = 5.00
        $this->assertFalse($service->isFallback());
        $this->assertNotNull($service->lastUpdated());
    }

    public function test_fx_service_falls_back_to_open_public_endpoint_when_no_key_is_set(): void
    {
        config(['services.exchangerate.key' => '']);

        Http::fake([
            'https://open.er-api.com/v6/latest/USD' => Http::response([
                'result' => 'success',
                'rates' => [
                    'USD' => 1,
                    'MYR' => 4.00,
                    'GBP' => 0.80,
                ],
            ], 200),
        ]);

        Cache::forget('fx_rates_myr');

        $service = app(FxRateService::class);
        $rates = $service->getRates();

        $this->assertEquals(4.00, $rates['USD']);
        $this->assertEquals(5.00, $rates['GBP']); // 4.00 / 0.80 = 5.00
        $this->assertFalse($service->isFallback());
    }

    public function test_fx_service_drops_to_hardcoded_rates_when_all_network_requests_fail(): void
    {
        config(['services.exchangerate.key' => '']);

        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        Cache::forget('fx_rates_myr');

        $service = app(FxRateService::class);
        $rates = $service->getRates();

        $this->assertEquals(4.47, $rates['USD']);
        $this->assertTrue($service->isFallback());
    }

    public function test_artisan_fx_refresh_command_updates_subscription_amounts(): void
    {
        $user = User::factory()->create();

        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => 'Figma Pro',
            'amount' => 10.00,
            'currency' => 'USD',
            'amount_myr' => 10.00,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        Http::fake([
            '*' => Http::response([
                'result' => 'success',
                'rates' => [
                    'USD' => 1,
                    'MYR' => 4.50,
                ],
            ], 200),
        ]);

        $this->artisan('fx:refresh --force')
            ->assertSuccessful();

        $sub->refresh();
        $this->assertEquals(45.00, (float) $sub->amount_myr);
    }
}
