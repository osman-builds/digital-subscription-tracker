<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BotAndSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_DATE = '2026-11-01';

    private const NEXT_DATE = '2026-12-01';

    private const LOGIN_PATH = '/login';

    public function test_dashboard_and_expenditure_charts_hex_escape_category_names(): void
    {
        $user = User::factory()->create();
        $xssCategoryName = '</script><script>alert(1)</script>';
        $category = Category::create([
            'name' => $xssCategoryName,
            'color' => '#4f46e5',
            'user_id' => $user->id,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Test Service',
            'amount' => 25.00,
            'amount_myr' => 25.00,
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'start_date' => self::VALID_DATE,
            'next_billing_date' => self::NEXT_DATE,
            'status' => 'active',
        ]);

        $dashboard = $this->actingAs($user)->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertDontSee($xssCategoryName, false);
        $dashboard->assertSee('\u003C\/script\u003E', false);

        $expenditure = $this->actingAs($user)->get(route('expenditure.index'));
        $expenditure->assertOk();
        $expenditure->assertDontSee($xssCategoryName, false);
        $expenditure->assertSee('\u003C\/script\u003E', false);
    }

    public function test_subscription_rejects_non_http_schemes_for_website_and_logo_urls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('subscriptions.store'), [
            'name' => 'Unsafe Link',
            'amount' => '10.00',
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'start_date' => self::VALID_DATE,
            'next_billing_date' => self::NEXT_DATE,
            'status' => 'active',
            'website_url' => 'javascript:alert(1)',
            'logo_url' => 'ftp://example.com/icon.png',
        ]);

        $response->assertSessionHasErrors(['website_url', 'logo_url']);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_subscription_search_escapes_sql_like_wildcards(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'name' => '100% Fiber Plan',
            'amount' => 99.00,
            'amount_myr' => 99.00,
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'start_date' => self::VALID_DATE,
            'next_billing_date' => self::NEXT_DATE,
            'status' => 'active',
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'name' => 'Netflix',
            'amount' => 55.00,
            'amount_myr' => 55.00,
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'start_date' => self::VALID_DATE,
            'next_billing_date' => self::NEXT_DATE,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('subscriptions.index', ['search' => '%']));
        $response->assertOk();
        $response->assertSee('100% Fiber Plan');
        $response->assertDontSee('Netflix');
    }

    public function test_honeypot_blocks_automated_submissions_on_auth_forms(): void
    {
        $user = User::factory()->create(['email' => 'human@example.com']);

        $loginResponse = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
            'website_hp' => 'https://spam-bot.invalid',
        ]);
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        $registerResponse = $this->post('/register', [
            'name' => 'Bot User',
            'email' => 'bot@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'website_hp' => 'spam',
        ]);
        $registerResponse->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);

        $forgotResponse = $this->post('/forgot-password', [
            'email' => $user->email,
            'website_hp' => 'bot-filled',
        ]);
        $forgotResponse->assertSessionHasErrors('email');
    }

    public function test_turnstile_verification_enforces_only_when_keys_are_configured(): void
    {
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');

        $loginView = $this->get(self::LOGIN_PATH);
        $loginView->assertOk();
        $loginView->assertSee('cf-turnstile', false);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::sequence()
                ->push(['success' => false], 200)
                ->push(['success' => true], 200),
        ]);

        $user = User::factory()->create(['email' => 'turnstile@example.com']);

        $failed = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'invalid-token',
        ]);
        $failed->assertSessionHasErrors('email');
        $this->assertGuest();

        $passed = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'valid-token',
        ]);
        $passed->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_turnstile_enforces_allowed_hostnames_when_configured(): void
    {
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');
        config()->set('services.turnstile.hostnames', ['mysubshield.me']);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::sequence()
                ->push(['success' => true, 'hostname' => 'evil-clone.test'], 200)
                ->push(['success' => true, 'hostname' => 'mysubshield.me'], 200),
        ]);

        $user = User::factory()->create(['email' => 'hostcheck@example.com']);

        $wrongHost = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'foreign-token',
        ]);
        $wrongHost->assertSessionHasErrors('email');
        $this->assertGuest();

        $validHost = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'valid-token',
        ]);
        $validHost->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_route_enforces_volumetric_ip_rate_limit(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $response = $this->post(self::LOGIN_PATH, [
                'email' => "spray{$i}@example.com",
                'password' => 'wrong-password',
            ]);
            $this->assertNotSame(429, $response->getStatusCode());
        }

        $throttled = $this->post(self::LOGIN_PATH, [
            'email' => 'spray21@example.com',
            'password' => 'wrong-password',
        ]);
        $throttled->assertStatus(429);
    }

    public function test_robots_txt_disallows_authenticated_routes_and_allows_root(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertIsString($robots);
        $this->assertStringContainsString('Allow: /', $robots);
        $this->assertStringContainsString('Disallow: /dashboard', $robots);
        $this->assertStringContainsString('Disallow: /subscriptions', $robots);
        $this->assertStringContainsString('Disallow: /settings', $robots);
    }
}
