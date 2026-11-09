<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Entertainment',  'color' => '#ef4444'],
            ['name' => 'Productivity',   'color' => '#3b82f6'],
            ['name' => 'Music',          'color' => '#a855f7'],
            ['name' => 'Cloud Storage',  'color' => '#06b6d4'],
            ['name' => 'Gaming',         'color' => '#f97316'],
            ['name' => 'News & Reading', 'color' => '#10b981'],
            ['name' => 'Health & Fitness', 'color' => '#ec4899'],
            ['name' => 'Software & Dev', 'color' => '#6366f1'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
