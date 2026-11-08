<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_command_creates_valid_json_snapshot(): void
    {
        User::factory()->count(2)->create();

        $this->artisan('app:backup')
            ->assertSuccessful()
            ->expectsOutputToContain('Snapshot created successfully');

        $backupDir = storage_path('app/backups');
        $this->assertTrue(File::isDirectory($backupDir));

        $files = File::glob("{$backupDir}/subshield-backup-*.json");
        $this->assertNotEmpty($files);

        $latest = end($files);
        $content = json_decode(File::get($latest), true);

        $this->assertIsArray($content);
        $this->assertSame('SubShield', $content['meta']['app'] ?? null);
        $this->assertArrayHasKey('users', $content['data'] ?? []);
        $this->assertArrayHasKey('subscriptions', $content['data'] ?? []);
    }
}
