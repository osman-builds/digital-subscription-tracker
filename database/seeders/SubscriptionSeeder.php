<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $entertainment  = Category::where('name', 'Entertainment')->first();
        $productivity   = Category::where('name', 'Productivity')->first();
        $music          = Category::where('name', 'Music')->first();
        $cloud          = Category::where('name', 'Cloud Storage')->first();
        $gaming         = Category::where('name', 'Gaming')->first();
        $news           = Category::where('name', 'News & Reading')->first();
        $health         = Category::where('name', 'Health & Fitness')->first();
        $dev            = Category::where('name', 'Software & Dev')->first();

        $subscriptions = [
            [
                'name'              => 'Netflix',
                'description'       => 'Streaming movies and TV shows',
                'amount'            => 15.49,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-01-15',
                'next_billing_date' => now()->day(15)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://netflix.com',
                'logo_url'          => 'https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg',
            ],
            [
                'name'              => 'Spotify',
                'description'       => 'Music streaming service',
                'amount'            => 9.99,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2023-06-01',
                'next_billing_date' => now()->addDays(3)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $music?->id,
                'website_url'       => 'https://spotify.com',
            ],
            [
                'name'              => 'Adobe Creative Cloud',
                'description'       => 'Design and creative apps suite',
                'amount'            => 54.99,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2023-09-01',
                'next_billing_date' => now()->addDays(12)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $dev?->id,
                'website_url'       => 'https://adobe.com',
            ],
            [
                'name'              => 'Microsoft 365',
                'description'       => 'Office apps + 1 TB OneDrive',
                'amount'            => 99.99,
                'billing_cycle'     => 'annual',
                'start_date'        => '2024-03-01',
                'next_billing_date' => now()->addMonths(5)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $productivity?->id,
                'website_url'       => 'https://microsoft.com/microsoft-365',
            ],
            [
                'name'              => 'Google One',
                'description'       => '2 TB Google storage plan',
                'amount'            => 9.99,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2023-11-01',
                'next_billing_date' => now()->addDays(6)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $cloud?->id,
                'website_url'       => 'https://one.google.com',
            ],
            [
                'name'              => 'Xbox Game Pass Ultimate',
                'description'       => 'Games library + Xbox Live Gold',
                'amount'            => 14.99,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-02-01',
                'next_billing_date' => now()->addDays(1)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $gaming?->id,
                'website_url'       => 'https://xbox.com/play',
            ],
            [
                'name'              => 'YouTube Premium',
                'description'       => 'Ad-free YouTube + YouTube Music',
                'amount'            => 13.99,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-04-01',
                'next_billing_date' => now()->addDays(20)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://youtube.com/premium',
            ],
            [
                'name'              => 'Notion',
                'description'       => 'All-in-one workspace for notes and projects',
                'amount'            => 16.00,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2023-12-01',
                'next_billing_date' => now()->addDays(14)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $productivity?->id,
                'website_url'       => 'https://notion.so',
            ],
            [
                'name'              => 'The New York Times',
                'description'       => 'Digital news subscription',
                'amount'            => 17.00,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2023-08-01',
                'next_billing_date' => now()->addDays(0)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $news?->id,
                'website_url'       => 'https://nytimes.com',
                'notes'             => 'Includes games & cooking access',
            ],
            [
                'name'              => 'Calm',
                'description'       => 'Meditation and sleep app',
                'amount'            => 69.99,
                'billing_cycle'     => 'annual',
                'start_date'        => '2024-01-01',
                'next_billing_date' => now()->addMonths(2)->format('Y-m-d'),
                'status'            => 'inactive',
                'category_id'       => $health?->id,
                'website_url'       => 'https://calm.com',
            ],
            [
                'name'              => 'GitHub Copilot',
                'description'       => 'AI code completion tool',
                'amount'            => 10.00,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-05-01',
                'next_billing_date' => now()->addDays(9)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $dev?->id,
                'website_url'       => 'https://github.com/features/copilot',
            ],
            [
                'name'              => 'Disney+',
                'description'       => 'Disney, Marvel, Star Wars streaming',
                'amount'            => 13.99,
                'billing_cycle'     => 'monthly',
                'start_date'        => '2022-12-01',
                'next_billing_date' => now()->addDays(25)->format('Y-m-d'),
                'status'            => 'cancelled',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://disneyplus.com',
                'notes'             => 'Cancelled — rarely used',
            ],
        ];

        foreach ($subscriptions as $sub) {
            Subscription::firstOrCreate(['name' => $sub['name']], $sub);
        }
    }
}
