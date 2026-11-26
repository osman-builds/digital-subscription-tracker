<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnforceSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_session_expires_after_absolute_lifetime_even_when_left_open_all_day(): void
    {
        config()->set('session.absolute_lifetime_hours', 12);
        Carbon::setTestNow(Carbon::parse('2026-11-26 08:00:00'));

        $user = User::factory()->create([
            'password' => Hash::make('ValidPass123'),
        ]);

        $this->withHeader('User-Agent', 'Mozilla/5.0 SubShieldBrowser')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'ValidPass123',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        // Still valid after 6 hours
        Carbon::setTestNow(Carbon::parse('2026-11-26 14:00:00'));
        $this->withHeader('User-Agent', 'Mozilla/5.0 SubShieldBrowser')
            ->get(route('dashboard'))
            ->assertOk();

        // Expired after 13 hours (past the 12-hour absolute cap)
        Carbon::setTestNow(Carbon::parse('2026-11-26 21:00:00'));
        $this->withHeader('User-Agent', 'Mozilla/5.0 SubShieldBrowser')
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_stolen_session_replayed_with_different_user_agent_is_terminated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ValidPass123'),
        ]);

        $this->withHeader('User-Agent', 'Mozilla/5.0 LegitimateBrowser/1.0')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'ValidPass123',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        // Attacker replays the same session from a different browser/tool
        $this->withHeader('User-Agent', 'Mozilla/5.0 AttackerReplay/9.9')
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_tampered_session_owner_binding_is_rejected_and_logged_out(): void
    {
        $victim = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($victim)
            ->withHeader('User-Agent', 'Mozilla/5.0 TestAgent')
            ->withSession([
                EnforceSessionSecurity::KEY_STARTED_AT => now()->timestamp,
                EnforceSessionSecurity::KEY_UA_HASH => hash_hmac('sha256', 'ua:mozilla/5.0 testagent', (string) config('app.key')),
                EnforceSessionSecurity::KEY_OWNER_MAC => hash_hmac('sha256', 'uid:'.$otherUser->id, (string) config('app.key')),
            ])
            ->getJson(route('dashboard'))
            ->assertStatus(401);

        $this->assertGuest();
    }

    public function test_password_reset_revokes_all_existing_database_sessions(): void
    {
        config()->set('session.driver', 'database');

        $user = User::factory()->create([
            'email' => 'recoverme@gmail.com',
            'password' => Hash::make('OldPassword123'),
        ]);

        DB::table('sessions')->insert([
            'id' => 'attacker-active-session-id',
            'user_id' => $user->id,
            'ip_address' => '198.51.100.44',
            'user_agent' => 'AttackerBrowser/2.0',
            'payload' => base64_encode('payload'),
            'last_activity' => now()->timestamp,
        ]);

        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'BrandNewPass9!',
            'password_confirmation' => 'BrandNewPass9!',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('sessions', ['id' => 'attacker-active-session-id']);
    }

    public function test_expenditure_clamps_extreme_year_query_parameter(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('expenditure.index', ['year' => 99999]));

        $response->assertOk();
        $response->assertViewHas('year', now()->year + 5);
    }
}
