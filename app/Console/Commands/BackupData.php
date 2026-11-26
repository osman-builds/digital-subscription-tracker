<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

#[Signature('app:backup {--encrypt : Encrypt the JSON snapshot payload at rest using APP_KEY}')]
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
            File::makeDirectory($backupDir, 0700, true);
        }
        @chmod($backupDir, 0700);

        $encrypt = (bool) $this->option('encrypt');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = $encrypt
            ? "subshield-backup-{$timestamp}.json.enc"
            : "subshield-backup-{$timestamp}.json";
        $filePath = "{$backupDir}/{$filename}";

        $payload = [
            'meta' => [
                'app' => 'SubShield',
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0',
                'environment' => config('app.env'),
                'encrypted' => $encrypt,
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

        $json = (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $contents = $encrypt ? Crypt::encryptString($json) : $json;
        File::put($filePath, $contents);
        @chmod($filePath, 0600);

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
