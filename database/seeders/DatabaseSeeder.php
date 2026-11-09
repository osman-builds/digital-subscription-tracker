<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo user (bcrypt hash applied via 'hashed' cast on User model)
        $demo = User::firstOrCreate(
            ['email' => 'demo@subtracker.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
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
