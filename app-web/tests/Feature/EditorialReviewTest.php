<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{ContentBlock, ReviewRequest, Service, User};
use App\Services\{ContentPublisher, ServicePublisher};
use Database\Seeders\{ContentSeeder, ServiceCatalogSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([ContentSeeder::class, ServiceCatalogSeeder::class]);
    }

    public function test_editor_submits_exact_draft_and_authorized_reviewer_requests_changes_then_publishes(): void
    {
        $editor = User::factory()->create();
        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = ContentBlock::where('key', 'home')->firstOrFail();
        $this->actingAs($editor)->post('/admin/reviews', ['type' => 'content', 'target_id' => $block->id, 'revision_id' => $block->current_revision_id, 'note' => 'Revisar texto <script>unsafe</script>'])->assertSessionHasNoErrors();
        $review = ReviewRequest::firstOrFail();
        $this->get(route('admin.content.edit', $block))->assertOk()
            ->assertSee('Abrir solicitud y comentarios')->assertDontSee('Enviar a revisión');
        $this->get(route('admin.reviews.show', $review))->assertOk()->assertSee('Español · Título principal')->assertDontSee('<script>unsafe</script>', false);
        $this->post(route('admin.reviews.decide', $review), ['decision' => 'publish'])->assertForbidden();
        $this->assertNull($block->fresh()->published_revision_id);
        $this->actingAs($owner)->post(route('admin.reviews.decide', $review), ['decision' => 'changes'])->assertSessionHasErrors('response');
        $this->post(route('admin.reviews.decide', $review), ['decision' => 'changes', 'response' => 'Ajusta la introducción.'])->assertSessionHasNoErrors();
        $this->assertSame('changes_requested', $review->fresh()->status);
        $this->get('/admin/reviews?status=changes_requested')->assertSee('Inicio y presentación');
        $payload = $block->currentRevision->payload;
        $payload['es']['hero'] = 'Texto corregido';
        app(ContentPublisher::class)->draft($block, $payload, $block->current_revision_id, $editor);
        $block->refresh();
        $this->actingAs($editor)->post('/admin/reviews', ['type' => 'content', 'target_id' => $block->id, 'revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        $new = ReviewRequest::latest('id')->first();
        $this->actingAs($owner)->post(route('admin.reviews.decide', $new), ['decision' => 'publish', 'response' => 'Revisado.'])->assertSessionHasNoErrors();
        $this->assertSame('published', $new->fresh()->status);
        $this->get(route('admin.content.edit', $block))->assertOk()
            ->assertSee('Esta versión ya está publicada.')->assertDontSee('Enviar a revisión');
        $this->get('/')->assertSee('Texto corregido');
        $this->assertDatabaseHas('audit_events', ['action' => 'review.changes_requested']);
    }

    public function test_new_drafts_supersede_requests_and_direct_publication_updates_the_inbox(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $service = Service::firstOrFail();
        $this->actingAs($owner);
        $data = ['type' => 'service', 'target_id' => $service->id, 'revision_id' => $service->current_revision_id];
        $this->post('/admin/reviews', $data)->assertSessionHasNoErrors();
        $review = ReviewRequest::firstOrFail();
        $this->post('/admin/reviews', $data)->assertSessionHasErrors('revision_id');
        app(ServicePublisher::class)->draft($service, $service->currentRevision->payload, $service->current_revision_id, $owner);
        $service->refresh();
        $this->assertSame('superseded', $review->fresh()->status);
        $this->post(route('admin.reviews.decide', $review), ['decision' => 'publish'])->assertSessionHasErrors('review');
        $this->assertNull($service->fresh()->published_revision_id);
        $this->get(route('admin.reviews.show', $review))->assertSee('Existe un borrador más reciente');
        $this->post('/admin/reviews', [...$data, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner);
        $this->assertSame('published', ReviewRequest::latest('id')->first()->status);
        $this->assertSame(0, ReviewRequest::where('status', 'pending')->count());
    }

    public function test_sensitive_content_still_requires_owner_and_comparison_keeps_its_original_base(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $admin = User::factory()->create(['role' => Role::Administrator]);
        $block = ContentBlock::where('key', 'physician')->firstOrFail();
        app(ContentPublisher::class)->publish($block, $block->current_revision_id, $owner);
        $base = $block->current_revision_id;
        $payload = $block->currentRevision->payload;
        $payload['es']['bio'] = 'BIOGRAFÍA PROPUESTA';
        app(ContentPublisher::class)->draft($block, $payload, $base, $admin);
        $block->refresh();
        $this->actingAs($admin)->post('/admin/reviews', ['type' => 'content', 'target_id' => $block->id, 'revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        $review = ReviewRequest::firstOrFail();
        $this->assertSame($base, $review->base_revision_id);
        $this->post(route('admin.reviews.decide', $review), ['decision' => 'publish'])->assertForbidden();
        $this->get(route('admin.reviews.show', $review))->assertSee('BIOGRAFÍA PROPUESTA');
        $this->actingAs($owner)->post(route('admin.reviews.decide', $review), ['decision' => 'publish'])->assertSessionHasNoErrors();
        $this->get(route('admin.reviews.show', $review))->assertSee('BIOGRAFÍA PROPUESTA')->assertSee('Comparación con la versión publicada #'.$base);
    }
}
