<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->seed(ContentSeeder::class);
    }

    private function upload(string $title): MediaAsset
    {
        $this->post(route('admin.media.store'), ['title' => $title, 'image' => UploadedFile::fake()->image('photo.png', 50, 80)])->assertSessionHasNoErrors();
        return MediaAsset::latest('id')->firstOrFail();
    }

    public function test_only_published_images_and_bilingual_alts_reach_the_public_site(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner);
        $image = $this->upload('Retrato aprobado');
        $block = ContentBlock::where('key', 'media')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['shared']['doctor_image_id'] = $image->id;
        $payload['shared']['fachada_image_id'] = $image->id;
        $payload['es']['doctor_alt'] = 'Retrato revisado del médico';
        $payload['en']['doctor_alt'] = 'Reviewed physician portrait';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasNoErrors();
        $block->refresh();
        $this->assertSame(1, $block->currentRevision->mediaAssets()->count());
        $this->get('/')->assertOk()->assertDontSee('Retrato revisado del médico')->assertDontSee($image->url());
        $this->get($image->url())->assertNotFound();
        $this->get('/admin/preview/es')->assertOk()->assertSee($image->url(true))->assertSee('Retrato revisado del médico');
        $this->get('/admin/preview/en')->assertOk()->assertSee('Reviewed physician portrait');
        $this->get(route('admin.content.edit', $block))->assertOk()->assertSee($image->url(true, 'thumb'));
        $this->get(route('admin.content.revision', [$block, $block->current_revision_id]))->assertOk()->assertSee('Retrato aprobado')->assertSee($image->url(true, 'thumb'));
        $this->actingAs(User::factory()->create(['role' => Role::Administrator]));
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertForbidden();
        $this->actingAs($owner)->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        auth()->logout();
        $this->get($image->url())->assertOk()->assertHeader('Content-Type', 'image/webp')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/')->assertOk()->assertSee('Retrato revisado del médico')->assertSee($image->url());
        $this->withSession(['public_locale' => 'en'])->get('/')->assertSee('Reviewed physician portrait');
        $this->get('/media/'.$image->id.'/original')->assertNotFound();
        $this->get(route('admin.media.original', $image))->assertRedirect(route('login'));
    }

    public function test_replacement_and_restore_preserve_published_photos_and_protect_history(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $first = $this->upload('Primera foto');
        $second = $this->upload('Segunda foto');
        $block = ContentBlock::where('key', 'media')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['shared']['doctor_image_id'] = $first->id;
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasNoErrors();
        $block->refresh();
        $firstRevision = $block->current_revision_id;
        $this->post(route('admin.content.publish', $block), ['revision_id' => $firstRevision])->assertSessionHasNoErrors();
        $payload['shared']['doctor_image_id'] = $second->id;
        $this->put(route('admin.content.update', $block), ['revision_id' => $firstRevision, 'payload' => $payload])->assertSessionHasNoErrors();
        $this->get('/')->assertSee($first->url())->assertDontSee($second->url());
        $this->get($second->url())->assertNotFound();
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->fresh()->current_revision_id])->assertSessionHasNoErrors();
        $this->get($first->url())->assertNotFound();
        $this->post(route('admin.content.restore', [$block, $firstRevision]), ['revision_id' => $block->fresh()->current_revision_id])->assertSessionHasNoErrors();
        $this->get('/')->assertSee($second->url());
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->fresh()->current_revision_id])->assertSessionHasNoErrors();
        $this->get('/')->assertSee($first->url());
        $this->delete(route('admin.media.destroy', $first))->assertSessionHasErrors('media');
        $this->delete(route('admin.media.destroy', $second))->assertSessionHasErrors('media');
        Storage::disk('local')->assertExists([$first->path(), $second->path()]);
    }

    public function test_missing_assets_and_empty_alternative_text_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Editor]));
        $block = ContentBlock::where('key', 'media')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['shared']['doctor_image_id'] = 9999;
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasErrors('shared.doctor_image_id');
        $payload['shared']['doctor_image_id'] = null;
        $payload['en']['doctor_alt'] = '';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasErrors('en.doctor_alt');
        $this->assertSame($block->current_revision_id, $block->fresh()->current_revision_id);
    }
}
