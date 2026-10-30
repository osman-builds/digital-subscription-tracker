<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_email_requires_re_verification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.account'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ])
            ->assertRedirect(route('verification.notice'));

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_updating_name_only_keeps_email_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.account'), [
                'name' => 'New Name',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertNothingSent();
    }
}
