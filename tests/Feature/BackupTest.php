<?php

namespace Tests\Feature;

use App\Jobs\RunEncryptedBackupJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
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

        File::delete($latest);
    }

    public function test_backup_command_can_create_encrypted_snapshot(): void
    {
        User::factory()->count(1)->create();

        $this->artisan('app:backup --encrypt')
            ->assertSuccessful()
            ->expectsOutputToContain('Snapshot created successfully');

        $backupDir = storage_path('app/backups');
        $files = File::glob("{$backupDir}/subshield-backup-*.json.enc");
        $this->assertNotEmpty($files);

        $latest = end($files);
        $raw = File::get($latest);
        $this->assertNull(json_decode($raw, true));

        $decrypted = json_decode(Crypt::decryptString($raw), true);
        $this->assertIsArray($decrypted);
        $this->assertSame('SubShield', $decrypted['meta']['app'] ?? null);
        $this->assertTrue((bool) ($decrypted['meta']['encrypted'] ?? false));

        File::delete($latest);
    }

    public function test_encrypted_backup_job_can_be_dispatched_onto_queue(): void
    {
        Queue::fake();

        RunEncryptedBackupJob::dispatch();

        Queue::assertPushed(RunEncryptedBackupJob::class);
        $this->assertInstanceOf(ShouldQueue::class, new RunEncryptedBackupJob);
    }
}
