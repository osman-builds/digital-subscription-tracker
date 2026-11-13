<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demoEmail = (string) config('demo.email', 'demo@subtracker.test');
        $configuredPassword = (string) config('demo.password', '');
        $strongPassword = $configuredPassword !== '' ? $configuredPassword : Str::random(40);

        // Rotate demo credentials on every seed so weak defaults never persist
        $demo = User::updateOrCreate(
            ['email' => $demoEmail],
            [
                'name' => 'Sandbox Demo',
                'password' => Hash::make($strongPassword),
                'email_verified_at' => now(),
            ]
        );
        $demo->updateQuietly(['email_verified_at' => now()]);

        $this->call([
            CategorySeeder::class,
            SubscriptionSeeder::class,
        ]);

        // Assign all seeded subscriptions to the demo user
        Subscription::whereNull('user_id')->update(['user_id' => $demo->id]);

        // Disable automated email alerts for the demo account to protect sender deliverability
        $demo->settings()->updateOrCreate(
            ['user_id' => $demo->id],
            [
                'alert_3_days' => false,
                'alert_7_days' => false,
            ]
        );
    }
}
