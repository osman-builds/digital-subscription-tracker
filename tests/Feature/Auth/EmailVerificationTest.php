<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee('Verify Your Email');
        $response->assertSee('Enter 6-Digit Code');
    }

    public function test_email_can_be_verified_with_valid_otp_and_receives_welcome_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        // Seed OTP into cache
        Cache::put("email_otp_{$user->id}", [
            'hash' => Hash::make('654321'),
            'attempts' => 0,
        ], now()->addMinutes(10));

        $response = $this->actingAs($user)->post(route('verification.otp'), [
            'otp' => '654321',
        ]);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        // Verify WelcomeNotification was dispatched
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_email_is_not_verified_with_invalid_otp(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        Cache::put("email_otp_{$user->id}", [
            'hash' => Hash::make('123456'),
            'attempts' => 0,
        ], now()->addMinutes(10));

        $response = $this->actingAs($user)->from(route('verification.notice'))->post(route('verification.otp'), [
            'otp' => '999999',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHasErrors('otp');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertNotSentTo($user, WelcomeNotification::class);
    }

    public function test_email_is_not_verified_when_otp_expired_or_missing(): void
    {
        $user = User::factory()->unverified()->create();

        // No cache entry exists (expired)
        $response = $this->actingAs($user)->from(route('verification.notice'))->post(route('verification.otp'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHasErrors('otp');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_resending_otp_dispatches_new_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post(route('verification.send'));

        $response->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentToTimes($user, EmailVerificationOtpNotification::class, 1);
    }

    public function test_email_can_still_be_verified_via_signed_url(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
        Notification::assertSentTo($user, WelcomeNotification::class);
    }
}
