<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PASSWORD = 'SecretPassword123!';

    private const DEMO_EMAIL = 'demo@subtracker.test';

    private const NEW_NAME = 'New Name';

    private const ROUTE_SETTINGS_INDEX = 'settings.index';

    private const ROUTE_SETTINGS_ACCOUNT = 'settings.account';

    private const ROUTE_SETTINGS_DELETE = 'settings.delete_account';

    private const ROUTE_SETTINGS_PROFILE = 'settings.profile';

    private const ROUTE_SETTINGS_SECURITY = 'settings.security';

    public function test_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route(self::ROUTE_SETTINGS_INDEX));

        $response->assertOk();
        $response->assertSee('Base Tracking Currency');
        $response->assertSee('Dark mode');
    }

    public function test_changing_email_requires_re_verification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route(self::ROUTE_SETTINGS_ACCOUNT), [
                'name' => self::NEW_NAME,
                'email' => 'new@example.com',
            ])
            ->assertRedirect(route('verification.notice'));

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentToTimes($user, EmailVerificationOtpNotification::class, 1);
    }

    public function test_updating_name_only_keeps_email_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route(self::ROUTE_SETTINGS_ACCOUNT), [
                'name' => self::NEW_NAME,
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();

        $this->assertSame(self::NEW_NAME, $user->name);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_preferences_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.preferences'), [
                'base_currency' => 'usd',
                'alert_7_days' => '1',
                'alert_3_days' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $settings = $user->getSettings();
        $this->assertSame('USD', $settings->base_currency);
        $this->assertTrue($settings->alert_7_days);
        $this->assertFalse($settings->alert_3_days);
    }

    public function test_password_can_be_updated_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $this->actingAs($user)
            ->patch(route('settings.password'), [
                'current_password' => 'OldPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    public function test_dark_mode_can_be_toggled(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('settings.dark_mode'), [
                'dark_mode' => true,
            ]);

        $response->assertOk()
            ->assertJson(['dark_mode' => true]);

        $this->assertTrue($user->getSettings()->dark_mode);
    }

    public function test_user_can_update_base_currency_preference_and_it_reflects_on_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.preferences'), [
                'base_currency' => 'USD',
                'alert_7_days' => 1,
                'alert_3_days' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('USD', $user->getSettings()->base_currency);

        Subscription::create([
            'user_id' => $user->id,
            'name' => 'Netflix US',
            'amount' => 15.00,
            'currency' => 'USD',
            'amount_myr' => 67.05,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('$ 15.00');
        $dashboardResponse->assertSee('USD');

        $expenditureResponse = $this->actingAs($user)->get(route('expenditure.index'));
        $expenditureResponse->assertOk();
        $expenditureResponse->assertSee('$ 180.00');
    }

    public function test_user_can_delete_account_with_correct_password_and_erases_all_data(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::TEST_PASSWORD),
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Custom Personal Tag',
            'color' => '#6366f1',
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Test Subscription',
            'amount' => 25.00,
            'currency' => 'USD',
            'amount_myr' => 110.00,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $user->getSettings()->update(['base_currency' => 'EUR']);

        $response = $this->actingAs($user)
            ->delete(route(self::ROUTE_SETTINGS_DELETE), [
                'password' => self::TEST_PASSWORD,
            ]);

        $response->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('user_settings', ['user_id' => $user->id]);
    }

    public function test_user_cannot_delete_account_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::TEST_PASSWORD),
        ]);

        $response = $this->actingAs($user)
            ->from(route(self::ROUTE_SETTINGS_INDEX))
            ->delete(route(self::ROUTE_SETTINGS_DELETE), [
                'password' => 'WrongPassword!',
            ]);

        $response->assertRedirect(route(self::ROUTE_SETTINGS_INDEX))
            ->assertSessionHasErrorsIn('userDeletion', ['password']);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_demo_evaluation_account_cannot_be_deleted(): void
    {
        $demoUser = User::factory()->create([
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($demoUser)
            ->from(route(self::ROUTE_SETTINGS_INDEX))
            ->delete(route(self::ROUTE_SETTINGS_DELETE), [
                'password' => 'password',
            ]);

        $response->assertRedirect(route(self::ROUTE_SETTINGS_INDEX))
            ->assertSessionHas('error');

        $this->assertAuthenticatedAs($demoUser);
        $this->assertDatabaseHas('users', ['id' => $demoUser->id]);
    }

    public function test_demo_account_email_cannot_be_modified(): void
    {
        $demoUser = User::factory()->create([
            'email' => self::DEMO_EMAIL,
        ]);

        $response = $this->actingAs($demoUser)
            ->from(route(self::ROUTE_SETTINGS_INDEX))
            ->patch(route(self::ROUTE_SETTINGS_ACCOUNT), [
                'name' => 'Attempted Change',
                'email' => 'hacked@example.com',
            ]);

        $response->assertRedirect(route(self::ROUTE_SETTINGS_INDEX))
            ->assertSessionHas('error');

        $demoUser->refresh();
        $this->assertSame(self::DEMO_EMAIL, $demoUser->email);
    }

    public function test_demo_account_password_cannot_be_modified(): void
    {
        $demoUser = User::factory()->create([
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($demoUser)
            ->from(route(self::ROUTE_SETTINGS_INDEX))
            ->patch(route('settings.password'), [
                'current_password' => 'password',
                'password' => 'NewSecretPassword123!',
                'password_confirmation' => 'NewSecretPassword123!',
            ]);

        $response->assertRedirect(route(self::ROUTE_SETTINGS_INDEX))
            ->assertSessionHas('error');

        $demoUser->refresh();
        $this->assertTrue(Hash::check('password', $demoUser->password));
    }

    public function test_settings_profile_page_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'name' => 'Alice Test',
            'email' => 'alice@example.com',
        ]);

        $response = $this->actingAs($user)->get(route(self::ROUTE_SETTINGS_PROFILE));

        $response->assertOk();
        $response->assertSee('Profile & Identity');
        $response->assertSee('Alice Test');
        $response->assertSee('alice@example.com');
        $response->assertSee('Danger Zone');
    }

    public function test_settings_security_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route(self::ROUTE_SETTINGS_SECURITY));

        $response->assertOk();
        $response->assertSee('Change Password');
        $response->assertSee('Two-Factor Authentication');
    }

    public function test_settings_about_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.about'));

        $response->assertOk();
        $response->assertSee('SubShield');
        $response->assertSee('No Bank Logins Required');
        $response->assertSee('Automatic Multi-Currency Conversion');
        $response->assertSee('support@mysubshield.me');
    }

    public function test_guests_cannot_access_settings_subpages(): void
    {
        $this->get(route(self::ROUTE_SETTINGS_INDEX))->assertRedirect(route('login'));
        $this->get(route(self::ROUTE_SETTINGS_PROFILE))->assertRedirect(route('login'));
        $this->get(route(self::ROUTE_SETTINGS_SECURITY))->assertRedirect(route('login'));
        $this->get(route('settings.about'))->assertRedirect(route('login'));
    }

    public function test_user_cannot_change_account_email_to_reserved_demo_or_non_routable_address(): void
    {
        $user = User::factory()->create([
            'email' => 'realowner@gmail.com',
        ]);

        $this->actingAs($user)
            ->from(route(self::ROUTE_SETTINGS_PROFILE))
            ->patch(route(self::ROUTE_SETTINGS_ACCOUNT), [
                'name' => $user->name,
                'email' => self::DEMO_EMAIL,
            ])
            ->assertRedirect(route(self::ROUTE_SETTINGS_PROFILE))
            ->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->from(route(self::ROUTE_SETTINGS_PROFILE))
            ->patch(route(self::ROUTE_SETTINGS_ACCOUNT), [
                'name' => $user->name,
                'email' => 'attacker@internal.local',
            ])
            ->assertRedirect(route(self::ROUTE_SETTINGS_PROFILE))
            ->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertSame('realowner@gmail.com', $user->email);
        $this->assertFalse($user->isSandbox());
    }

    public function test_demo_account_cannot_update_password_via_auth_password_endpoint(): void
    {
        $demoUser = User::factory()->create([
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make(self::TEST_PASSWORD),
        ]);

        $response = $this->actingAs($demoUser)
            ->from(route(self::ROUTE_SETTINGS_SECURITY))
            ->put(route('password.update'), [
                'current_password' => self::TEST_PASSWORD,
                'password' => 'ChangedDemoPass9!',
                'password_confirmation' => 'ChangedDemoPass9!',
            ]);

        $response->assertRedirect(route(self::ROUTE_SETTINGS_SECURITY))
            ->assertSessionHas('error');

        $demoUser->refresh();
        $this->assertTrue(Hash::check(self::TEST_PASSWORD, $demoUser->password));
    }

    public function test_dashboard_renders_onboarding_card_for_verified_user_with_zero_subscriptions(): void
    {
        $user = User::factory()->create([
            'email' => 'newmember@gmail.com',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Quick-Start Onboarding Guide');
        $response->assertSee(route(self::ROUTE_SETTINGS_INDEX), false);
    }

    public function test_changing_password_rotates_remember_token_and_revokes_other_database_sessions(): void
    {
        config()->set('session.driver', 'database');

        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
            'remember_token' => 'initial-token-value',
        ]);

        DB::table('sessions')->insert([
            'id' => 'hijacked-device-session-id',
            'user_id' => $user->id,
            'ip_address' => '203.0.113.55',
            'user_agent' => 'AttackerBrowser/1.0',
            'payload' => base64_encode('payload'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($user)
            ->patch(route('settings.password'), [
                'current_password' => 'OldPassword123',
                'password' => 'NewStrongPass9',
                'password_confirmation' => 'NewStrongPass9',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();
        $this->assertNotSame('initial-token-value', $user->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'hijacked-device-session-id']);
    }

    public function test_settings_password_endpoint_is_rate_limited_against_brute_force(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPass123'),
        ]);

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)
                ->patch(route('settings.password'), [
                    'current_password' => 'WrongPass123',
                    'password' => 'NewStrongPass9',
                    'password_confirmation' => 'NewStrongPass9',
                ])
                ->assertRedirect();
        }

        $this->actingAs($user)
            ->patch(route('settings.password'), [
                'current_password' => 'WrongPass123',
                'password' => 'NewStrongPass9',
                'password_confirmation' => 'NewStrongPass9',
            ])
            ->assertStatus(429);
    }
}
