<?php

namespace App\Models;

use App\Notifications\EmailVerificationOtpNotification;
use Database\Factories\UserFactory;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // bcrypt is used automatically via this cast
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    /** Get settings, creating defaults if missing */
    public function getSettings(): UserSetting
    {
        return UserSetting::forUser($this->id);
    }

    /**
     * Determine if the user has verified their email address.
     * The demo account does not require email OTP verification.
     */
    public function hasVerifiedEmail(): bool
    {
        if ($this->email === 'demo@subtracker.test') {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Send the email verification OTP notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        if ($this->email === 'demo@subtracker.test') {
            return;
        }

        $otp = (string) random_int(100000, 999999);

        Cache::put(
            'email_otp_'.$this->id,
            [
                'hash' => Hash::make($otp),
                'attempts' => 0,
            ],
            now()->addMinutes(10)
        );

        $this->notify(new EmailVerificationOtpNotification($otp));
    }

    /**
     * Verify the entered 6-digit email OTP.
     */
    public function verifyEmailOtp(string $enteredOtp): bool
    {
        $key = 'email_otp_'.$this->id;
        $data = Cache::get($key);

        if (! is_array($data) || ! isset($data['hash'])) {
            return false;
        }

        if (($data['attempts'] ?? 0) >= 5) {
            Cache::forget($key);

            return false;
        }

        $isValid = Hash::check($enteredOtp, $data['hash']);
        if (! $isValid) {
            $data['attempts'] = ($data['attempts'] ?? 0) + 1;
            Cache::put($key, $data, now()->addMinutes(10));
        } else {
            Cache::forget($key);
            $this->markEmailAsVerified();
            event(new Verified($this));
        }

        return $isValid;
    }
}
