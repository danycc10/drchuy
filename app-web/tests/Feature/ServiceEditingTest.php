<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Service;
use App\Models\ServiceRevision;
use App\Models\User;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceEditingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(ServiceCatalogSeeder::class);
    }

    private function actor(Role $role = Role::Owner): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_saving_publishes_immediately_for_any_active_account(): void
    {
        $this->actingAs($this->actor(Role::Editor));
        $service = Service::firstOrFail();
        $payload = $service->currentRevision->payload;
        $payload['es']['description'] = 'Texto actualizado al instante.';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors()->assertRedirect();
        $service->refresh();
        $this->assertSame($service->current_revision_id, $service->published_revision_id);
        $this->assertSame($payload['es']['description'], $service->publishedRevision->payload['es']['description']);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()->assertSee('Texto actualizado al instante.');
        $this->assertDatabaseHas('audit_events', ['action' => 'service.published', 'service_id' => $service->id]);
        $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('Guardar y publicar')->assertSee('En el sitio')
            ->assertSee('data-pending-publish="false"', false)->assertDontSee('Publicar ahora')->assertDontSee('Solicitar revisión')->assertDontSee('Revisiones');
    }

    public function test_pending_initial_versions_are_listed_and_can_be_published_as_they_are(): void
    {
        $this->actingAs($this->actor());
        $service = Service::firstOrFail();
        $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('data-pending-publish="true"', false)->assertSee('Publicar ahora')->assertSee('Sin publicar');
        $this->get('/admin')->assertOk()->assertSee('Pendiente de publicar')->assertSee('Abrir y publicar')->assertSee('Sin publicar');
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertSame($service->current_revision_id, $service->published_revision_id);
        $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('data-pending-publish="false"', false)->assertDontSee('Publicar ahora');
    }

    public function test_restore_publishes_the_restored_version_and_keeps_history(): void
    {
        $this->actingAs($this->actor());
        $service = Service::firstOrFail();
        $initial = $service->current_revision_id;
        $payload = $service->currentRevision->payload;
        $payload['es']['name'] = 'Nombre nuevo';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $initial])->assertSessionHasNoErrors();
        $service->refresh();
        $renamed = $service->current_revision_id;
        $this->assertSame($renamed, $service->published_revision_id);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('Nombre nuevo');
        $this->post(route('admin.services.restore', [$service, $initial]), ['revision_id' => $renamed])->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertNotContains($service->current_revision_id, [$initial, $renamed]);
        $this->assertSame($service->current_revision_id, $service->published_revision_id);
        $this->assertSame(ServiceRevision::findOrFail($initial)->payload, $service->publishedRevision->payload);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertDontSee('Nombre nuevo');
        $this->assertDatabaseHas('audit_events', ['action' => 'service.restored', 'service_id' => $service->id]);
        $this->assertDatabaseCount('service_revisions', 16);
    }

    public function test_stale_edits_and_stale_publication_are_rejected(): void
    {
        $this->actingAs($this->actor());
        $service = Service::firstOrFail();
        $data = [...$service->currentRevision->payload, 'revision_id' => $service->current_revision_id];
        $this->put(route('admin.services.update', $service), $data)->assertSessionHasNoErrors();
        $this->put(route('admin.services.update', $service), $data)->assertSessionHasErrors('revision_id');
        $this->post(route('admin.services.publish', $service), ['revision_id' => $data['revision_id']])->assertSessionHasErrors('revision_id');
        $this->assertDatabaseCount('service_revisions', 15);
    }

    public function test_administrators_publish_procedures_and_revisions_are_scoped(): void
    {
        $this->actingAs($this->actor(Role::Administrator));
        $service = Service::where('source_id', 10)->firstOrFail();
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $this->assertSame($service->current_revision_id, $service->fresh()->published_revision_id);
        $other = Service::where('source_id', 1)->firstOrFail();
        $this->get(route('admin.services.revision', [$service, $other->current_revision_id]))->assertNotFound();
        $this->post(route('admin.services.restore', [$service, $other->current_revision_id]), ['revision_id' => $service->current_revision_id])->assertNotFound();
        $this->post(route('admin.services.publish', $other), ['revision_id' => $other->current_revision_id])->assertSessionHasNoErrors();
    }

    public function test_bilingual_validation_and_output_escaping(): void
    {
        $this->actingAs($this->actor());
        $service = Service::firstOrFail();
        $data = [...$service->currentRevision->payload, 'revision_id' => $service->current_revision_id];
        $data['en']['description'] = '';
        $this->put(route('admin.services.update', $service), $data)->assertSessionHasErrors('en.description');
        $this->assertNull($service->fresh()->published_revision_id);
        $data['en']['description'] = '<script>alert(1)</script>';
        $this->put(route('admin.services.update', $service), $data)->assertSessionHasNoErrors();
        $this->get(route('admin.services.edit', $service))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $this->withSession(['public_locale' => 'en'])->get('/')->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('admin.services.index', ['q' => 'asma']))->assertOk()->assertSee('Atención del asma');
        $this->get(route('admin.services.index', ['status' => 'published']))->assertOk()->assertSee($data['es']['name']);
        $this->get(route('admin.services.index', ['status' => 'draft']))->assertOk()->assertDontSee($data['es']['name']);
        $this->get(route('admin.activity'))->assertOk();
    }
}
