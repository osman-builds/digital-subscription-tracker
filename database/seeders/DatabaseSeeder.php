<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed read-only system categories; demo sandbox sessions run ephemerally without DB accounts
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
