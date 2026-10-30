<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_rate_limited_per_ip(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/register', [
                'name' => 'Bot',
                'email' => "bot{$attempt}@example.com",
                'password' => 'password',
                'password_confirmation' => 'password',
            ])->assertStatus(302);

            auth()->logout();
        }

        $this->post('/register', [
            'name' => 'Bot',
            'email' => 'bot6@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertTooManyRequests();
    }

    public function test_forgot_password_is_rate_limited_per_ip(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/forgot-password', ['email' => "user{$attempt}@example.com"])
                ->assertStatus(302);
        }

        $this->post('/forgot-password', ['email' => 'user6@example.com'])
            ->assertTooManyRequests();
    }

    public function test_resending_verification_email_is_limited_to_three_per_hour(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->actingAs($user)
                ->post(route('verification.send'))
                ->assertStatus(302);
        }

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertTooManyRequests();
    }
}
