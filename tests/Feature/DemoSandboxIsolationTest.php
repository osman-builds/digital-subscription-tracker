<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DemoSandboxIsolationTest extends TestCase
{
    use RefreshDatabase;

    private const DEMO_EMAIL = 'demo@subtracker.test';

    private const SANDBOX_HOBBY = 'Sandbox Hobby';

    public function test_sandbox_launch_requires_turnstile_when_configured_and_blocks_honeypot(): void
    {
        config()->set('services.turnstile.site_key', 'sandbox-site-key');
        config()->set('services.turnstile.secret_key', 'sandbox-secret-key');
        config()->set('services.turnstile.hostnames', ['mysubshield.me']);

        $page = $this->get(route('demo.show'));
        $page->assertOk();
        $page->assertSee('cf-turnstile', false);

        $honeypotBlocked = $this->post(route('demo.start'), [
            'website_hp' => 'https://bot.invalid',
            'cf-turnstile-response' => 'token',
        ]);
        $honeypotBlocked->assertSessionHasErrors('email');
        $this->assertGuest();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::sequence()
                ->push(['success' => false], 200)
                ->push(['success' => true, 'hostname' => 'mysubshield.me'], 200),
        ]);

        $failed = $this->post(route('demo.start'), [
            'cf-turnstile-response' => 'bad-token',
        ]);
        $failed->assertSessionHasErrors('email');
        $this->assertGuest();

        $passed = $this->post(route('demo.start'), [
            'cf-turnstile-response' => 'good-token',
        ]);
        $passed->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isSandbox());
    }

    public function test_sandbox_modifications_stay_in_session_and_never_write_to_database(): void
    {
        Category::create(['name' => 'Entertainment', 'color' => '#ef4444', 'user_id' => null]);

        $this->post(route('demo.start'))->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Isolated Sandbox');

        $this->post(route('categories.store'), [
            'name' => self::SANDBOX_HOBBY,
            'color' => '#14b8a6',
        ])->assertRedirect(route('categories.index'));

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee(self::SANDBOX_HOBBY);

        $this->assertDatabaseMissing('categories', ['name' => self::SANDBOX_HOBBY]);

        $this->post(route('subscriptions.store'), [
            'name' => 'Tester Only Service',
            'amount' => '19.99',
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'start_date' => '2026-11-01',
            'next_billing_date' => '2026-12-01',
            'status' => 'active',
            'category_id' => 100001,
            'website_url' => 'https://tester.example.com',
        ])->assertRedirect(route('subscriptions.index'));

        $this->get(route('subscriptions.index'))
            ->assertOk()
            ->assertSee('Tester Only Service');

        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('user_settings', 0);
    }

    public function test_sandbox_session_expires_automatically_after_one_hour(): void
    {
        $this->post(route('demo.start'))->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->travel(61)->minutes();

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('demo.show'));
        $this->assertGuest();
    }

    public function test_direct_password_login_for_demo_account_is_disabled_by_default(): void
    {
        User::factory()->create([
            'email' => self::DEMO_EMAIL,
            'password' => bcrypt('StrongPassword99!'),
        ]);

        $response = $this->post('/login', [
            'email' => self::DEMO_EMAIL,
            'password' => 'StrongPassword99!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_external_apis_and_emails_are_blocked_for_sandbox_and_unverified_and_fake_emails(): void
    {
        Notification::fake();
        Http::fake();

        $this->post(route('demo.start'))->assertRedirect(route('dashboard'));

        $this->getJson(route('subscriptions.resolve_icon', ['domain' => 'netflix.com']))
            ->assertStatus(403);

        $this->patch(route('settings.preferences'), [
            'base_currency' => 'USD',
            'alert_7_days' => '1',
            'alert_3_days' => '1',
        ])->assertRedirect();

        $this->assertFalse(auth()->user()->getSettings()->alert_7_days);
        $this->assertFalse(auth()->user()->getSettings()->alert_3_days);
        $this->assertSame('USD', auth()->user()->getSettings()->base_currency);
        $this->assertDatabaseCount('user_settings', 0);

        $this->post(route('logout'))->assertRedirect('/');

        $unverified = User::factory()->unverified()->create(['email' => 'pending@example.com']);
        $this->actingAs($unverified)
            ->getJson(route('subscriptions.resolve_icon', ['domain' => 'netflix.com']))
            ->assertStatus(403);

        $this->post('/logout');

        $this->post('/register', [
            'name' => 'Fake Clone',
            'email' => 'clone@attacker.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'clone@attacker.test']);

        $this->post('/forgot-password', ['email' => self::DEMO_EMAIL])->assertRedirect();
        $this->post('/forgot-password', ['email' => 'clone@attacker.invalid'])->assertRedirect();

        Http::assertNothingSent();
        Notification::assertNothingSent();
    }
}
