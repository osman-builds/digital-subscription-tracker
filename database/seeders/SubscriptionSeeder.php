<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $entertainment = Category::where('name', 'Entertainment')->first();
        $productivity  = Category::where('name', 'Productivity')->first();
        $music         = Category::where('name', 'Music')->first();
        $cloud         = Category::where('name', 'Cloud Storage')->first();
        $gaming        = Category::where('name', 'Gaming')->first();
        $news          = Category::where('name', 'News & Reading')->first();
        $health        = Category::where('name', 'Health & Fitness')->first();
        $dev           = Category::where('name', 'Software & Dev')->first();

        $subscriptions = [

            // ── Existing 12 – now with explicit currency ─────────────────────

            [
                'name'              => 'Netflix',
                'description'       => 'Streaming movies and TV shows',
                'amount'            => 15.49,
                'currency'          => 'USD',
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-01-15',
                'next_billing_date' => now()->day(15)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://netflix.com',
            ],
            [
                'name'              => 'Spotify',
                'description'       => 'Music streaming — EU plan billed in EUR',
                'amount'            => 9.99,
                'currency'          => 'EUR',
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
                'currency'          => 'USD',
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
                'currency'          => 'USD',
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
                'currency'          => 'USD',
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
                'currency'          => 'USD',
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
                'currency'          => 'USD',
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-04-01',
                'next_billing_date' => now()->addDays(20)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://youtube.com/premium',
            ],
            [
                'name'              => 'Notion',
                'description'       => 'All-in-one workspace — USD plan',
                'amount'            => 16.00,
                'currency'          => 'USD',
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
                'currency'          => 'USD',
                'billing_cycle'     => 'monthly',
                'start_date'        => '2023-08-01',
                'next_billing_date' => now()->toDateString(),
                'status'            => 'active',
                'category_id'       => $news?->id,
                'website_url'       => 'https://nytimes.com',
                'notes'             => 'Includes games & cooking access',
            ],
            [
                'name'              => 'Calm',
                'description'       => 'Meditation and sleep app',
                'amount'            => 69.99,
                'currency'          => 'USD',
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
                'currency'          => 'USD',
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
                'currency'          => 'USD',
                'billing_cycle'     => 'monthly',
                'start_date'        => '2022-12-01',
                'next_billing_date' => now()->addDays(25)->format('Y-m-d'),
                'status'            => 'cancelled',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://disneyplus.com',
                'notes'             => 'Cancelled — rarely used',
            ],

            // ── New entries: varied currencies & billing cycles ───────────────

            [
                'name'              => 'iCloud+',
                'description'       => '50 GB iCloud storage — local MYR plan',
                'amount'            => 4.90,
                'currency'          => 'MYR',
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-06-01',
                'next_billing_date' => now()->addDays(5)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $cloud?->id,
                'website_url'       => 'https://icloud.com',
                'notes'             => 'Apple iCloud 50 GB tier — MYR billing',
            ],
            [
                'name'              => 'Canva Pro',
                'description'       => 'Premium design tools and assets — SGD annual plan',
                'amount'            => 169.90,
                'currency'          => 'SGD',
                'billing_cycle'     => 'annual',
                'start_date'        => '2024-02-10',
                'next_billing_date' => now()->addMonths(4)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $dev?->id,
                'website_url'       => 'https://canva.com',
                'notes'             => 'Singapore dollar annual billing',
            ],
            [
                'name'              => 'Apple TV+',
                'description'       => 'Apple originals streaming service — GBP plan',
                'amount'            => 8.99,
                'currency'          => 'GBP',
                'billing_cycle'     => 'monthly',
                'start_date'        => '2024-07-01',
                'next_billing_date' => now()->addDays(11)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $entertainment?->id,
                'website_url'       => 'https://tv.apple.com',
                'notes'             => 'UK billing in GBP',
            ],
            [
                'name'              => 'Duolingo Super',
                'description'       => 'Ad-free language learning — AUD quarterly',
                'amount'            => 29.99,
                'currency'          => 'AUD',
                'billing_cycle'     => 'quarterly',
                'start_date'        => '2024-04-01',
                'next_billing_date' => now()->addDays(18)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $health?->id,
                'website_url'       => 'https://duolingo.com',
                'notes'             => 'AUD quarterly billing',
            ],
            [
                'name'              => 'Figma Professional',
                'description'       => 'UI/UX design tool — EUR semi-annual',
                'amount'            => 90.00,
                'currency'          => 'EUR',
                'billing_cycle'     => 'semi_annual',
                'start_date'        => '2024-01-01',
                'next_billing_date' => now()->addMonths(3)->format('Y-m-d'),
                'status'            => 'active',
                'category_id'       => $dev?->id,
                'website_url'       => 'https://figma.com',
                'notes'             => 'EUR semi-annual plan for design team',
            ],
        ];

        foreach ($subscriptions as $sub) {
            // Update existing rows to also set currency if missing
            $existing = Subscription::where('name', $sub['name'])->first();
            if ($existing) {
                $existing->update([
                    'currency' => $sub['currency'],
                    'amount'   => $sub['amount'],
                ]);
            } else {
                Subscription::create($sub);
            }
        }
    }
}
