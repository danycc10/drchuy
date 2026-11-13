<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{ContentBlock, Service, User};
use App\Services\MediaLibrary;
use Database\Seeders\{ContentSeeder, ServiceCatalogSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevokedPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_in_flight_editorial_writes_recheck_a_deactivated_account_before_committing(): void
    {
        $this->withoutVite();
        $this->seed([ServiceCatalogSeeder::class, ContentSeeder::class]);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $service = Service::firstOrFail();
        $block = ContentBlock::where('key', 'home')->firstOrFail();
        $this->actingAs($owner);
        User::whereKey($owner->id)->update(['is_active' => false, 'auth_version' => 1]);
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertForbidden();
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertForbidden();
        $this->put(route('admin.services.update', $service), [...$service->currentRevision->payload, 'revision_id' => $service->current_revision_id])->assertForbidden();
        $this->put(route('admin.content.update', $block), ['payload' => $block->currentRevision->payload, 'revision_id' => $block->current_revision_id])->assertForbidden();
        $this->assertNull($service->fresh()->published_revision_id);
        $this->assertNull($block->fresh()->published_revision_id);
        $this->assertDatabaseCount('service_revisions', 14);
        $this->assertDatabaseCount('content_revisions', 9);
    }

    public function test_in_flight_media_mutations_do_not_survive_account_revocation(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => Role::Owner]);
        $media = app(MediaLibrary::class)->upload(UploadedFile::fake()->image('existing.png', 20, 20), 'Existing', $owner);
        $files = Storage::disk('local')->allFiles();
        $this->actingAs($owner);
        User::whereKey($owner->id)->update(['is_active' => false, 'auth_version' => 1]);
        $this->delete(route('admin.media.destroy', $media))->assertForbidden();
        $this->postJson(route('admin.media.store'), ['title' => 'Blocked', 'image' => UploadedFile::fake()->image('new.png', 20, 20)])->assertForbidden();
        $this->assertDatabaseCount('media_assets', 1);
        $this->assertSame($files, Storage::disk('local')->allFiles());
    }
}
