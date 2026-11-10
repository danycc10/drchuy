<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{BackupRun, ContentBlock, MediaAsset, User};
use App\Services\SiteBackup;
use Database\Seeders\{ContentSeeder, ServiceCatalogSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan, DB, File, Storage};
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class SiteBackupTest extends TestCase
{
    use RefreshDatabase;

    private string $workspace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->workspace = storage_path('framework/testing/backup-'.Str::uuid());
        File::ensureDirectoryExists($this->workspace.'/restored');
        config(['backup.directory' => $this->workspace.'/archives', 'database.connections.restore_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
        Artisan::call('migrate', ['--database' => 'restore_test', '--force' => true]);
        $this->seed([ContentSeeder::class, ServiceCatalogSeeder::class]);
    }

    protected function tearDown(): void
    {
        DB::purge('restore_test');
        if (isset($this->workspace) && str_starts_with($this->workspace, storage_path('framework/testing/backup-'))) { File::deleteDirectory($this->workspace); }
        parent::tearDown();
    }

    private function media(): MediaAsset
    {
        $directory = 'media/'.Str::uuid();
        foreach (['original.jpg', 'display.webp', 'thumb.webp'] as $name) { Storage::disk('local')->put($directory.'/'.$name, 'TEST IMAGE '.$name); }

        return MediaAsset::create(['title' => 'Fotografía de prueba', 'original_name' => 'prueba.jpg', 'original_mime' => 'image/jpeg', 'directory' => $directory, 'extension' => 'jpg', 'width' => 10, 'height' => 10, 'display_width' => 10, 'display_height' => 10, 'original_bytes' => 20, 'display_bytes' => 20, 'thumb_bytes' => 20]);
    }

    public function test_encrypted_backup_restores_content_users_and_media_only_to_a_separate_empty_database(): void
    {
        $user = User::factory()->create(['role' => Role::Owner]);
        $asset = $this->media();
        $block = ContentBlock::where('key', 'home')->firstOrFail();
        $block->published_revision_id = $block->current_revision_id;
        $block->save();
        $backup = app(SiteBackup::class);
        $run = $backup->create();
        $path = config('backup.directory').'/'.$run->filename;
        $manifest = $backup->inspect($path);
        $this->assertSame(1, $manifest['tables']['users']);
        $zip = new ZipArchive();
        $zip->open($path);
        $this->assertFalse(@$zip->getFromName('database/users.json'));
        $this->assertFalse($zip->locateName('.env'));
        $this->assertFalse($zip->locateName('database/sessions.json'));
        $zip->close();
        $backup->restore($path, 'restore_test', $this->workspace.'/restored');
        $target = DB::connection('restore_test');
        $this->assertSame($block->published_revision_id, $target->table('content_blocks')->where('id', $block->id)->value('published_revision_id'));
        $this->assertSame($user->password, $target->table('users')->where('id', $user->id)->value('password'));
        $this->assertSame(1, $target->table('users')->where('id', $user->id)->value('auth_version'));
        $this->assertSame('TEST IMAGE original.jpg', File::get($this->workspace.'/restored/'.substr($asset->directory, 6).'/original.jpg'));
        $this->assertNotNull($run->fresh()->restore_checked_at);
        $this->assertSame(0, $user->fresh()->auth_version);
        $this->assertSame([], $target->select('PRAGMA foreign_key_check'));
        $this->expectException(RuntimeException::class);
        $backup->restore($path, 'restore_test', $this->workspace.'/restored');
    }

    public function test_active_database_and_tampered_archives_are_rejected_before_any_restore(): void
    {
        $backup = app(SiteBackup::class);
        $run = $backup->create();
        $path = config('backup.directory').'/'.$run->filename;
        try { $backup->restore($path, config('database.default'), $this->workspace.'/restored'); $this->fail('Active database accepted'); } catch (RuntimeException $error) { $this->assertStringContainsString('conexión distinta', $error->getMessage()); }
        $zip = new ZipArchive();
        $zip->open($path);
        $zip->addFromString('../unexpected.txt', 'UNSAFE');
        $zip->close();
        try { $backup->restore($path, 'restore_test', $this->workspace.'/restored'); $this->fail('Tampered archive accepted'); } catch (RuntimeException $error) { $this->assertStringContainsString('inesperadas', $error->getMessage()); }
        $this->assertFalse(DB::connection('restore_test')->table('content_blocks')->exists());
        $this->assertSame([], File::allFiles($this->workspace.'/restored'));
    }

    public function test_missing_media_marks_failure_and_never_leaves_a_completed_archive(): void
    {
        $media = $this->media();
        Storage::disk('local')->delete($media->directory.'/original.jpg');
        $this->artisan('app:backup')->assertFailed();
        $this->assertSame('failed', BackupRun::latest('id')->first()->status);
        $this->assertSame([], File::files(config('backup.directory')));
    }

    public function test_retention_preserves_the_two_newest_complete_backups(): void
    {
        $backup = app(SiteBackup::class);
        $first = $backup->create();
        $first->forceFill(['created_at' => now()->subDays(30)])->save();
        $second = $backup->create();
        $second->forceFill(['created_at' => now()->subDays(29)])->save();
        $third = $backup->create();
        $backup->prune();
        $this->assertSame('pruned', $first->fresh()->status);
        $this->assertFileDoesNotExist(config('backup.directory').'/'.$first->filename);
        $this->assertFileExists(config('backup.directory').'/'.$second->filename);
        $this->assertFileExists(config('backup.directory').'/'.$third->filename);
    }

    public function test_maintenance_and_content_export_are_owner_only_and_export_excludes_authentication_data(): void
    {
        foreach ([Role::Editor, Role::Administrator] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get('/admin/maintenance')->assertForbidden();
            $this->get('/admin/maintenance/export')->assertForbidden();
        }
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner)->get('/admin/maintenance')->assertOk()->assertSee('Exportar contenido');
        $response = $this->get('/admin/maintenance/export')->assertOk();
        $data = json_decode($response->streamedContent(), true);
        $this->assertCount(9, $data['content']);
        $this->assertArrayNotHasKey('users', $data);
        $this->assertStringNotContainsString($owner->password, $response->streamedContent());
    }
}
