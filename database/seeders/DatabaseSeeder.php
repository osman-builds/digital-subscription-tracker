<?php

namespace Database\Seeders;

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
                'name'     => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        $this->call([
            CategorySeeder::class,
            SubscriptionSeeder::class,
        ]);

        // Assign all seeded subscriptions to the demo user
        \App\Models\Subscription::whereNull('user_id')->update(['user_id' => $demo->id]);
    }
}
