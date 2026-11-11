<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{ContentBlock, Service, User};
use App\Services\{EditorialReview, MediaLibrary, ServicePublisher};
use Database\Seeders\{ContentSeeder, ServiceCatalogSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevisionComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_shows_saved_versions_and_private_images_and_cannot_publish_a_newer_revision(): void
    {
        $this->withoutVite(); Storage::fake('local');
        $this->seed(ServiceCatalogSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $service = Service::firstOrFail();
        $this->get(route('admin.services.compare', $service))->assertRedirect(route('login'));
        $publisher = app(ServicePublisher::class);
        $publisher->publish($service, $service->current_revision_id, $owner);
        $image = app(MediaLibrary::class)->upload(UploadedFile::fake()->image('new.png', 120, 80), 'Imagen propuesta', $owner);
        $payload = $service->currentRevision->payload;
        $payload['es']['description'] = '<script>Texto propuesto</script>';
        $payload['image_id'] = $image->id;
        $publisher->draft($service, $payload, $service->current_revision_id, $owner);
        $service->refresh(); $revision = $service->current_revision_id;
        $this->actingAs($owner)->get(route('admin.services.compare', $service))->assertOk()->assertSee($image->url(true))->assertSee('Versión pública actual')->assertSee('Modificado')->assertSee($payload['es']['description'])->assertDontSee($payload['es']['description'], false)->assertSee('Publicar esta versión');
        $review = app(EditorialReview::class)->submit($service, $revision, null, $owner);
        $this->get(route('admin.reviews.show', $review))->assertOk()->assertSee('Publicada al solicitar revisión')->assertSee($image->url(true));
        $publisher->draft($service, $payload, $revision, $owner);
        $this->post(route('admin.services.publish', $service), ['revision_id' => $revision])->assertSessionHasErrors('revision_id');
        $this->actingAs(User::factory()->create(['role' => Role::Editor]))->get(route('admin.services.compare', $service))->assertOk()->assertDontSee('Publicar esta versión');
        $this->get($image->url())->assertNotFound();
    }

    public function test_content_comparison_handles_the_initial_fallback_and_identical_published_revision(): void
    {
        $this->withoutVite(); $this->seed(ContentSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]); $this->actingAs($owner);
        $block = ContentBlock::where('key', 'home')->firstOrFail();
        $this->get(route('admin.content.compare', $block))->assertOk()->assertSee('contenido original');
        app(\App\Services\ContentPublisher::class)->publish($block, $block->current_revision_id, $owner);
        $this->get(route('admin.content.compare', $block))->assertOk()->assertSee('No hay diferencias');
        Storage::fake('local');
        $media = ContentBlock::where('key', 'media')->firstOrFail();
        $image = app(MediaLibrary::class)->upload(UploadedFile::fake()->image('portrait.png', 20, 40), 'Retrato propuesto', $owner);
        $payload = $media->currentRevision->payload;
        $payload['shared']['doctor_image_id'] = $image->id;
        app(\App\Services\ContentPublisher::class)->draft($media, $payload, $media->current_revision_id, $owner);
        $this->get(route('admin.content.compare', $media))->assertOk()->assertSee($image->url(true))->assertSee(asset('images/doctor.webp'));
    }
}
