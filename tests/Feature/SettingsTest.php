<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PASSWORD = 'SecretPassword123!';

    private const DEMO_EMAIL = 'demo@subtracker.test';

    private const NEW_NAME = 'New Name';

    public function test_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Base Tracking Currency');
        $response->assertSee('Dark mode');
    }

    public function test_changing_email_requires_re_verification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.account'), [
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
            ->patch(route('settings.account'), [
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
            ->delete(route('settings.delete_account'), [
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
            ->from(route('settings.index'))
            ->delete(route('settings.delete_account'), [
                'password' => 'WrongPassword!',
            ]);

        $response->assertRedirect(route('settings.index'))
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
            ->from(route('settings.index'))
            ->delete(route('settings.delete_account'), [
                'password' => 'password',
            ]);

        $response->assertRedirect(route('settings.index'))
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
            ->from(route('settings.index'))
            ->patch(route('settings.account'), [
                'name' => 'Attempted Change',
                'email' => 'hacked@example.com',
            ]);

        $response->assertRedirect(route('settings.index'))
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
            ->from(route('settings.index'))
            ->patch(route('settings.password'), [
                'current_password' => 'password',
                'password' => 'NewSecretPassword123!',
                'password_confirmation' => 'NewSecretPassword123!',
            ]);

        $response->assertRedirect(route('settings.index'))
            ->assertSessionHas('error');

        $demoUser->refresh();
        $this->assertTrue(Hash::check('password', $demoUser->password));
    }
}
