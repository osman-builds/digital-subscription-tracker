<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTE_REGISTER = '/register';

    private const TEST_EMAIL = 'test@example.com';

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(self::ROUTE_REGISTER);

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(self::ROUTE_REGISTER, [
            'name' => 'Test User',
            'email' => self::TEST_EMAIL,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_sends_exactly_one_verification_email(): void
    {
        Notification::fake();

        $this->post(self::ROUTE_REGISTER, [
            'name' => 'Test User',
            'email' => self::TEST_EMAIL,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', self::TEST_EMAIL)->firstOrFail();

        Notification::assertSentToTimes($user, EmailVerificationOtpNotification::class, 1);
    }

    public function test_unverified_users_cannot_access_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }
}
