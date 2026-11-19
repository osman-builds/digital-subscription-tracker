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

    private const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private const TURNSTILE_FIELD = 'cf-turnstile-response';

    private const VALID_TEST_PASSWORD = 'password';

    private const BOT_TEST_EMAIL = 'bot@example.com';

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
            'email' => self::BOT_TEST_EMAIL,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'website_hp' => 'spam',
        ]);
        $registerResponse->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => self::BOT_TEST_EMAIL]);

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
            self::TURNSTILE_VERIFY_URL => Http::sequence()
                ->push(['success' => false, 'error-codes' => ['timeout-or-duplicate']], 200)
                ->push(['success' => true, 'action' => 'sandbox'], 200)
                ->push(['success' => true, 'action' => 'login'], 200),
        ]);

        $user = User::factory()->create(['email' => 'turnstile@example.com']);

        $failed = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => self::VALID_TEST_PASSWORD,
            self::TURNSTILE_FIELD => 'invalid-or-replayed-token',
        ]);
        $failed->assertSessionHasErrors('email');
        $this->assertGuest();

        $mismatchedAction = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => self::VALID_TEST_PASSWORD,
            self::TURNSTILE_FIELD => 'sandbox-action-token',
        ]);
        $mismatchedAction->assertSessionHasErrors('email');
        $this->assertGuest();

        $passed = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => self::VALID_TEST_PASSWORD,
            self::TURNSTILE_FIELD => 'valid-token',
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
            self::TURNSTILE_VERIFY_URL => Http::sequence()
                ->push(['success' => true, 'hostname' => 'evil-clone.test', 'action' => 'login'], 200)
                ->push(['success' => true, 'hostname' => 'mysubshield.me', 'action' => 'login'], 200),
        ]);

        $user = User::factory()->create(['email' => 'hostcheck@example.com']);

        $wrongHost = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => self::VALID_TEST_PASSWORD,
            self::TURNSTILE_FIELD => 'foreign-token',
        ]);
        $wrongHost->assertSessionHasErrors('email');
        $this->assertGuest();

        $validHost = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => self::VALID_TEST_PASSWORD,
            self::TURNSTILE_FIELD => 'valid-token',
        ]);
        $validHost->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_password_renders_and_enforces_turnstile_when_configured(): void
    {
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');

        $resetView = $this->get('/reset-password/sample-token');
        $resetView->assertOk();
        $resetView->assertSee('data-action="reset-password"', false);

        Http::fake([
            self::TURNSTILE_VERIFY_URL => Http::response(['success' => false], 200),
        ]);

        $blocked = $this->post('/reset-password', [
            'token' => 'sample-token',
            'email' => 'user@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
            self::TURNSTILE_FIELD => 'bad-token',
        ]);
        $blocked->assertSessionHasErrors('email');
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

    public function test_automated_script_user_agents_are_blocked_immediately(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'python-requests/2.31.0',
        ])->post(self::LOGIN_PATH, [
            'email' => self::BOT_TEST_EMAIL,
            'password' => self::VALID_TEST_PASSWORD,
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('Automated script activity detected', (string) $response->getContent());
        $this->assertGuest();
    }

    public function test_tampered_client_device_token_is_rejected(): void
    {
        $failed = $this->post(self::LOGIN_PATH, [
            'email' => 'user@example.com',
            'password' => self::VALID_TEST_PASSWORD,
            'client_device_token' => 'invalid-non-base64-payload!!',
        ]);
        $failed->assertSessionHasErrors('email');
        $this->assertGuest();

        $validToken = base64_encode(json_encode([
            'sw' => 1920,
            'sh' => 1080,
            'cd' => 24,
            'tp' => 0,
            'hc' => 8,
            'tz' => -480,
        ]));

        $user = User::factory()->create();
        $passed = $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => self::VALID_TEST_PASSWORD,
            'client_device_token' => $validToken,
        ]);
        $passed->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_repeated_bot_violations_trigger_temporary_ip_jail(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post(self::LOGIN_PATH, [
                'email' => 'attacker@example.com',
                'password' => self::VALID_TEST_PASSWORD,
                'website_hp' => 'bot-honeypot-trap',
            ]);
        }

        $jailed = $this->post(self::LOGIN_PATH, [
            'email' => 'attacker@example.com',
            'password' => self::VALID_TEST_PASSWORD,
        ]);

        $jailed->assertStatus(429);
        $this->assertStringContainsString('Access temporarily restricted', (string) $jailed->getContent());
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
