<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SessionRevoker
{
    /**
     * Rotate the user's remember token, purge all other database sessions,
     * and regenerate the current session ID after a security-critical change.
     */
    public static function revokeOtherSessions(User $user, Request $request): void
    {
        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();

        if (config('session.driver') === 'database') {
            $table = (string) config('session.table', 'sessions');
            DB::table($table)
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $request->session()->regenerate();
    }

    /**
     * Purge all active database sessions for a user (e.g. after forgotten password reset).
     */
    public static function revokeAllSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            $table = (string) config('session.table', 'sessions');
            DB::table($table)
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
