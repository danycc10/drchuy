<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{MediaAsset, ReviewRequest, Service, User};
use App\Services\{MediaLibrary, ServicePublisher};
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceImagesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->seed(ServiceCatalogSeeder::class);
        $this->owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($this->owner);
    }

    private function image(string $title): MediaAsset
    {
        return app(MediaLibrary::class)->upload(UploadedFile::fake()->image('service.png', 120, 80), $title, $this->owner);
    }

    private function save(Service $service, ?int $imageId): void
    {
        $service->refresh();
        $this->put(route('admin.services.update', $service), [...$service->currentRevision->payload, 'revision_id' => $service->current_revision_id, 'image_id' => $imageId])->assertSessionHasNoErrors();
        $service->refresh();
    }

    private function publish(Service $service): void
    {
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
    }

    public function test_editor_selects_private_image_and_only_authorized_publication_exposes_it(): void
    {
        $image = $this->image('Estudio respiratorio');
        $service = Service::where('source_id', 10)->firstOrFail();
        $this->publish($service);
        $this->actingAs(User::factory()->create(['role' => Role::Editor]));
        $this->save($service, $image->id);
        $this->assertSame($image->id, $service->currentRevision->image_id);
        $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('Imagen del servicio')->assertSee($image->url(true, 'thumb'));
        $this->get('/')->assertDontSee($image->url());
        $this->get($image->url())->assertNotFound();
        $this->get('/admin/preview/es')->assertOk()->assertSee($image->url(true));
        $this->get('/admin/preview/en')->assertOk()->assertSee($image->url(true));
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => Role::Administrator]));
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertForbidden();
        $this->actingAs($this->owner);
        $this->publish($service);
        auth()->logout();
        $this->get($image->url())->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->get('/')->assertSee($image->url());
        $this->get(route('admin.media.original', $image))->assertRedirect(route('login'));
    }

    public function test_replacement_default_and_restore_preserve_history_and_library_usage(): void
    {
        $first = $this->image('Primera imagen de servicio');
        $second = $this->image('Segunda imagen de servicio');
        $service = Service::firstOrFail();
        $initial = $service->current_revision_id;
        $this->save($service, $first->id);
        $withFirst = $service->current_revision_id;
        $this->publish($service);
        $this->save($service, $second->id);
        $this->get('/')->assertSee($first->url())->assertDontSee($second->url());
        $this->get($second->url())->assertNotFound();
        $this->publish($service);
        $this->get($first->url())->assertNotFound();
        $this->save($service, null);
        $this->publish($service);
        $this->get('/')->assertDontSee($second->url())->assertSee('images/services/consultation-640.webp');
        $this->post(route('admin.services.restore', [$service, $withFirst]), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertSame($first->id, $service->currentRevision->image_id);
        $this->get(route('admin.services.revision', [$service, $withFirst]))->assertOk()->assertSee($first->url(true, 'thumb'))->assertSee($first->title);
        $this->publish($service);
        $this->get('/')->assertSee($first->url());
        $this->get('/admin/media?usage=used')->assertSee($first->title)->assertSee($second->title);
        $this->get('/admin/media?usage=unused')->assertDontSee($first->title)->assertDontSee($second->title);
        $this->delete(route('admin.media.destroy', $first))->assertSessionHasErrors('media');
        $this->delete(route('admin.media.destroy', $second))->assertSessionHasErrors('media');
        $this->post(route('admin.services.restore', [$service, $initial]), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $this->assertNull($service->fresh()->currentRevision->image_id);
        Storage::disk('local')->assertExists([$first->path(), $second->path()]);
    }

    public function test_image_only_change_is_reviewable_and_omitted_field_preserves_current_selection(): void
    {
        $image = $this->image('Imagen propuesta');
        $service = Service::firstOrFail();
        $this->publish($service);
        $this->save($service, $image->id);
        $this->post('/admin/reviews', ['type' => 'service', 'target_id' => $service->id, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $review = ReviewRequest::firstOrFail();
        $this->get(route('admin.reviews.show', $review))->assertOk()->assertSee('Imagen del servicio')->assertSee($image->title)->assertSee('Ilustración original de la categoría');
        $this->post(route('admin.reviews.decide', $review), ['decision' => 'publish'])->assertSessionHasNoErrors();
        $service->refresh();
        $this->put(route('admin.services.update', $service), [...$service->currentRevision->payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertSame($image->id, $service->currentRevision->image_id);
        $response = $this->get('/admin/maintenance/export')->assertOk();
        $export = collect(json_decode($response->streamedContent(), true)['services'])->firstWhere('source_id', $service->source_id);
        $this->assertSame($image->id, collect($export['revisions'])->firstWhere('id', $service->current_revision_id)['image_id']);
    }

    public function test_invalid_image_does_not_create_a_revision_and_hidden_service_does_not_expose_media(): void
    {
        $image = $this->image('Imagen solo para servicio');
        $service = Service::firstOrFail();
        $revision = $service->current_revision_id;
        $this->put(route('admin.services.update', $service), [...$service->currentRevision->payload, 'revision_id' => $revision, 'image_id' => 99999])->assertSessionHasErrors('image_id');
        $this->assertSame($revision, $service->fresh()->current_revision_id);
        $this->save($service, $image->id);
        $this->publish($service);
        $payload = $service->currentRevision->payload;
        $payload['visible'] = false;
        app(ServicePublisher::class)->draft($service, $payload, $service->current_revision_id, $this->owner);
        $service->refresh();
        $this->publish($service);
        $this->get($image->url())->assertNotFound();
        $this->get('/')->assertDontSee($image->url());
    }
}
