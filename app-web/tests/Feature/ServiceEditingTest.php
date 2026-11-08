<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Service;
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

    public function test_editor_can_save_but_cannot_publish_even_by_direct_request(): void
    {
        $this->actingAs($this->actor(Role::Editor));
        $service = Service::firstOrFail();
        $payload = $service->currentRevision->payload;
        $payload['es']['description'] = 'Texto actualizado para revisión.';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors()->assertRedirect();
        $service->refresh();
        $this->assertNull($service->published_revision_id);
        $this->assertSame($payload['es']['description'], $service->currentRevision->payload['es']['description']);
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertForbidden();
    }

    public function test_publication_remains_stable_while_drafts_and_restores_are_created(): void
    {
        $this->actingAs($this->actor());
        $service = Service::firstOrFail();
        $initial = $service->current_revision_id;
        $this->post(route('admin.services.publish', $service), ['revision_id' => $initial])->assertSessionHasNoErrors();
        $payload = $service->currentRevision->payload;
        $payload['es']['name'] = 'Nombre nuevo';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $initial])->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertSame($initial, $service->published_revision_id);
        $this->assertNotSame($initial, $service->current_revision_id);
        $this->post(route('admin.services.restore', [$service, $initial]), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertSame($initial, $service->published_revision_id);
        $this->assertNotSame($initial, $service->current_revision_id);
        $this->assertSame($service->publishedRevision->payload, $service->currentRevision->payload);
        $this->assertDatabaseHas('audit_events', ['action' => 'service.restored', 'service_id' => $service->id]);
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

    public function test_only_owner_can_publish_procedures_and_revisions_are_scoped(): void
    {
        $this->actingAs($this->actor(Role::Administrator));
        $service = Service::where('source_id', 10)->firstOrFail();
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertForbidden();
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
        $data['en']['description'] = '<script>alert(1)</script>';
        $this->put(route('admin.services.update', $service), $data)->assertSessionHasNoErrors();
        $this->get(route('admin.services.edit', $service))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $this->get(route('admin.services.index', ['q' => 'asma']))->assertOk()->assertSee('Atención del asma');
        $this->get(route('admin.services.index', ['status' => 'published']))->assertOk()->assertSee('No encontramos');
        $this->get(route('admin.activity'))->assertOk();
    }
}
