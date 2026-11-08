<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('app:backup')]
#[Description('Create an offline timestamped JSON snapshot of all application data')]
class BackupData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SubShield data snapshot...');

        $users = User::all();
        $subscriptions = Subscription::all();
        $categories = Category::all();
        $settings = UserSetting::all();

        $backupDir = storage_path('app/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "subshield-backup-{$timestamp}.json";
        $filePath = "{$backupDir}/{$filename}";

        $payload = [
            'meta' => [
                'app' => 'SubShield',
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0',
                'environment' => config('app.env'),
                'counts' => [
                    'users' => $users->count(),
                    'subscriptions' => $subscriptions->count(),
                    'categories' => $categories->count(),
                    'settings' => $settings->count(),
                ],
            ],
            'data' => [
                'users' => $users,
                'subscriptions' => $subscriptions,
                'categories' => $categories,
                'settings' => $settings,
            ],
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put($filePath, (string) $json);

        $this->table(
            ['Entity', 'Records Dumped'],
            [
                ['Users', $users->count()],
                ['Subscriptions', $subscriptions->count()],
                ['Categories', $categories->count()],
                ['User Settings', $settings->count()],
            ]
        );

        $sizeKb = number_format(filesize($filePath) / 1024, 2);
        $this->info("Snapshot created successfully ({$sizeKb} KB):");
        $this->line("  {$filePath}");

        return self::SUCCESS;
    }
}
