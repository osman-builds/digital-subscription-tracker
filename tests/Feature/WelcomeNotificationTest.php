<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_notification_is_fully_dynamic_and_currency_agnostic(): void
    {
        $user = User::factory()->create();

        $notification = new WelcomeNotification;
        $mail = $notification->toMail($user);

        $this->assertSame('Welcome to SubShield — Your Digital Subscription Tracker', $mail->subject);

        $body = implode("\n", $mail->introLines);
        $this->assertStringContainsString('Cross-Currency Normalisation', $body);
        $this->assertStringContainsString('preferred tracking currency', $body);
        $this->assertStringContainsString('Customise Your Currency', $body);
    }
}
