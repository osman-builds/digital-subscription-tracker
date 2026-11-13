<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrandIconTest extends TestCase
{
    use RefreshDatabase;

    private const RESOLVE_ICON_ROUTE = 'subscriptions.resolve_icon';

    public function test_health_and_fitness_brand_is_inferred_without_false_positive_short_key_matches(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Strava Summit',
            'amount' => '11.99',
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect(route('subscriptions.index'));

        $strava = Subscription::where('name', 'Strava Summit')->first();
        $this->assertNotNull($strava);
        $this->assertSame('https://strava.com', $strava->website_url);
        $this->assertStringContainsString('strava.com', (string) $strava->logo_url);

        // "Apex Clinic" contains "x", which must not trigger "x.com"
        $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Apex Clinic',
            'amount' => '45.00',
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect(route('subscriptions.index'));

        $apex = Subscription::where('name', 'Apex Clinic')->first();
        $this->assertNotNull($apex);
        $this->assertNull($apex->website_url);
        $this->assertNull($apex->logo_url);
    }

    public function test_resolve_icon_requires_authentication(): void
    {
        $this->get(route(self::RESOLVE_ICON_ROUTE, ['domain' => 'calm.com']))
            ->assertRedirect(route('login'));
    }

    public function test_resolve_icon_falls_back_to_duckduckgo_and_root_domain_when_google_returns_404(): void
    {
        $user = User::factory()->create();

        Http::fake([
            't2.gstatic.com/*' => Http::response('', 404),
            'icons.duckduckgo.com/ip3/app.calm.com.ico' => Http::response('', 404),
            'icons.duckduckgo.com/ip3/calm.com.ico' => Http::response(str_repeat('A', 120), 200, ['Content-Type' => 'image/png']),
            '*' => Http::response('', 404),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route(self::RESOLVE_ICON_ROUTE, ['domain' => 'https://app.calm.com/subscribe']));

        $response->assertOk();
        $response->assertJsonPath('domain', 'app.calm.com');
        $response->assertJsonPath('logo_url', 'https://icons.duckduckgo.com/ip3/calm.com.ico');
        $response->assertJsonPath('matched_brand.name', 'Calm');
        $response->assertJsonPath('matched_brand.category', 'Health & Fitness');
    }

    public function test_resolve_icon_handles_multi_part_country_domains_like_com_my(): void
    {
        $user = User::factory()->create();

        Http::fake([
            't2.gstatic.com/*' => Http::response(str_repeat('B', 120), 200, ['Content-Type' => 'image/png']),
            '*' => Http::response('', 404),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route(self::RESOLVE_ICON_ROUTE, ['domain' => 'portal.aia.com.my']));

        $response->assertOk();
        $response->assertJsonPath('domain', 'portal.aia.com.my');
        $response->assertJsonPath('matched_brand.domain', 'aia.com.my');
        $response->assertJsonPath('matched_brand.name', 'AIA Health');
    }

    public function test_resolve_icon_rejects_invalid_domains(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route(self::RESOLVE_ICON_ROUTE, ['domain' => 'not a valid domain']))
            ->assertStatus(422);
    }
}
