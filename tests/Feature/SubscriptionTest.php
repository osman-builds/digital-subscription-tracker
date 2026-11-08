<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_subscription_screen_renders_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscriptions.create'));

        $response->assertOk();
        $response->assertSee('Quick Brand Auto-Fill');
        $response->assertSee('popularServiceList');
        $response->assertSee('smartTypoAlert');
        $response->assertSee('sub_website_url');
    }

    public function test_subscription_auto_normalizes_website_url_and_logo(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Entertainment', 'color' => '#ef4444', 'user_id' => null]);

        $response = $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Netflix Premium',
            'website_url' => 'netflix.com',
            'amount' => '15.99',
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'category_id' => $category->id,
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertRedirect(route('subscriptions.index'));

        $sub = Subscription::where('name', 'Netflix Premium')->first();
        $this->assertNotNull($sub);
        $this->assertEquals('https://netflix.com', $sub->website_url);
        $this->assertStringContainsString('faviconV2', (string) $sub->logo_url);
        $this->assertStringContainsString('netflix.com', (string) $sub->logo_url);
    }

    public function test_subscription_auto_populates_website_and_logo_from_brand_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Spotify',
            'amount' => '10.99',
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertRedirect(route('subscriptions.index'));

        $sub = Subscription::where('name', 'Spotify')->first();
        $this->assertNotNull($sub);
        $this->assertEquals('https://spotify.com', $sub->website_url);
        $this->assertStringContainsString('spotify.com', (string) $sub->logo_url);
    }
}
