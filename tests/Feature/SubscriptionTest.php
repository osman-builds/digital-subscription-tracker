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

    private const NETFLIX_PREMIUM = 'Netflix Premium';

    private const NETFLIX_URL = 'https://netflix.com';

    private const NETFLIX_AMOUNT = '15.99';

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
            'name' => self::NETFLIX_PREMIUM,
            'website_url' => 'netflix.com',
            'amount' => self::NETFLIX_AMOUNT,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'category_id' => $category->id,
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertRedirect(route('subscriptions.index'));

        $sub = Subscription::where('name', self::NETFLIX_PREMIUM)->first();
        $this->assertNotNull($sub);
        $this->assertEquals(self::NETFLIX_URL, $sub->website_url);
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

    public function test_user_can_export_subscriptions_to_csv(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::create(['name' => 'Entertainment', 'color' => '#ef4444', 'user_id' => null]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => self::NETFLIX_PREMIUM,
            'website_url' => self::NETFLIX_URL,
            'amount' => self::NETFLIX_AMOUNT,
            'currency' => 'USD',
            'amount_myr' => '71.95',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        Subscription::create([
            'user_id' => $otherUser->id,
            'name' => 'Secret Other User Service',
            'amount' => '99.99',
            'currency' => 'USD',
            'amount_myr' => '450.00',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('subscriptions.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $content = $response->streamedContent();
        $this->assertStringContainsString(self::NETFLIX_PREMIUM, $content);
        $this->assertStringContainsString(self::NETFLIX_URL, $content);
        $this->assertStringContainsString('USD', $content);
        $this->assertStringContainsString('Entertainment', $content);
        $this->assertStringNotContainsString('Secret Other User Service', $content);
    }

    public function test_csv_export_neutralizes_formula_injection_attempts(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'name' => '=cmd|calc!A0',
            'amount' => '10.00',
            'currency' => 'USD',
            'amount_myr' => '45.00',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
            'notes' => '+formula_test',
        ]);

        $response = $this->actingAs($user)->get(route('subscriptions.export'));
        $content = $response->streamedContent();

        // Should be sanitized with leading single quote to neutralize formula execution in Excel
        $this->assertStringContainsString("'=cmd|calc!A0", $content);
        $this->assertStringContainsString("'+formula_test", $content);
    }

    public function test_user_can_create_trial_subscription_with_cancellation_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Figma Pro Trial',
            'amount' => '12.00',
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addDays(14)->toDateString(),
            'is_trial' => '1',
            'trial_ends_at' => now()->addDays(14)->toDateString(),
            'cancellation_url' => 'https://figma.com/settings/billing',
        ]);

        $response->assertRedirect(route('subscriptions.index'));

        $sub = Subscription::where('name', 'Figma Pro Trial')->first();
        $this->assertNotNull($sub);
        $this->assertTrue($sub->is_trial);
        $this->assertEquals(now()->addDays(14)->toDateString(), $sub->trial_ends_at->toDateString());
        $this->assertEquals('https://figma.com/settings/billing', $sub->cancellation_url);
    }

    public function test_user_can_renew_and_advance_billing_cycle(): void
    {
        $user = User::factory()->create();
        $initialBillingDate = now()->addDays(2)->startOfDay();

        $sub = Subscription::create([
            'user_id' => $user->id,
            'name' => 'Spotify Family',
            'amount' => '16.99',
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => $initialBillingDate->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('subscriptions.renew', $sub));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sub->refresh();
        $this->assertEquals($initialBillingDate->copy()->addMonth()->toDateString(), $sub->next_billing_date->toDateString());
    }

    public function test_cannot_renew_other_users_subscription(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $sub = Subscription::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Netflix',
            'amount' => self::NETFLIX_AMOUNT,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('subscriptions.renew', $sub));
        $response->assertForbidden();
    }
}
