<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\RenewalReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RenewalReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_notification_dynamically_formats_in_user_preferred_base_currency(): void
    {
        $user = User::factory()->create([
            'email' => 'realuser@gmail.com',
            'name' => 'Osman',
        ]);

        $user->settings()->create([
            'base_currency' => 'KES',
            'alert_3_days' => true,
            'alert_7_days' => true,
        ]);

        $category = Category::create([
            'name' => 'Streaming',
            'color' => '#10b981',
            'user_id' => $user->id,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Netflix Premium',
            'amount' => 15.99,
            'currency' => 'USD',
            'amount_myr' => 71.48,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDay()->toDateString(),
        ]);

        $notification = new RenewalReminderNotification(collect([$sub]));
        $mail = $notification->toMail($user);

        $rendered = implode("\n", $mail->introLines);

        $this->assertStringContainsString('KSh', $rendered);
        $this->assertStringContainsString('Netflix Premium', $rendered);
        $this->assertStringContainsString('USD', $rendered);
    }

    public function test_remind_command_skips_demo_and_test_and_unverified_users(): void
    {
        Notification::fake();

        $demoUser = User::factory()->create([
            'email' => 'demo@subtracker.test',
            'email_verified_at' => now(),
        ]);

        $demoUser->settings()->create([
            'base_currency' => 'MYR',
            'alert_3_days' => true,
            'alert_7_days' => true,
        ]);

        $unverifiedUser = User::factory()->create([
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
        ]);

        $unverifiedUser->settings()->create([
            'base_currency' => 'USD',
            'alert_3_days' => true,
            'alert_7_days' => true,
        ]);

        $category = Category::create([
            'name' => 'General',
            'color' => '#3b82f6',
            'user_id' => null,
        ]);

        Subscription::create([
            'user_id' => $demoUser->id,
            'category_id' => $category->id,
            'name' => 'Demo Subscription',
            'amount' => 20.00,
            'currency' => 'MYR',
            'amount_myr' => 20.00,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDays(2)->toDateString(),
        ]);

        Subscription::create([
            'user_id' => $unverifiedUser->id,
            'category_id' => $category->id,
            'name' => 'Unverified Subscription',
            'amount' => 50.00,
            'currency' => 'USD',
            'amount_myr' => 220.00,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->artisan('subscriptions:remind --days=3')
            ->expectsOutputToContain('Skipping unverified/demo user demo@subtracker.test')
            ->expectsOutputToContain('Skipping unverified/demo user unverified@example.com')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_remind_command_sends_notification_to_verified_real_users(): void
    {
        Notification::fake();

        $realUser = User::factory()->create([
            'email' => 'abdullahiosman6543@gmail.com',
            'email_verified_at' => now(),
        ]);

        $realUser->settings()->create([
            'base_currency' => 'KES',
            'alert_3_days' => true,
            'alert_7_days' => true,
        ]);

        $category = Category::create([
            'name' => 'Productivity',
            'color' => '#8b5cf6',
            'user_id' => $realUser->id,
        ]);

        Subscription::create([
            'user_id' => $realUser->id,
            'category_id' => $category->id,
            'name' => 'GitHub Copilot',
            'amount' => 10.00,
            'currency' => 'USD',
            'amount_myr' => 44.70,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'next_billing_date' => now()->addDay()->toDateString(),
        ]);

        $this->artisan('subscriptions:remind --days=3')
            ->expectsOutputToContain('Sent reminder email to: abdullahiosman6543@gmail.com')
            ->assertSuccessful();

        Notification::assertSentTo($realUser, RenewalReminderNotification::class);
    }
}
