<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{Service, User};
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceVisitDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_details_are_optional_translated_private_until_published_and_escaped(): void
    {
        $this->withoutVite();
        $this->seed(ServiceCatalogSeeder::class);
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $service = Service::firstOrFail();
        $this->get('/admin/preview/es')->assertDontSee('service-visit-details', false);
        $payload = $service->currentRevision->payload;
        $payload['es']['preparation'] = 'Indicaciones <script>alert(1)</script>';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasErrors('en.preparation');
        $payload['en']['preparation'] = 'Instructions confirmed by the physician';
        $payload['es']['bring'] = 'Documento confirmado por el médico';
        $payload['en']['bring'] = 'Document confirmed by the physician';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
        $this->get('/')->assertDontSee($payload['es']['preparation']);
        $this->get('/admin/preview/es')->assertSee($payload['es']['preparation'])->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<dt>Duración aproximada</dt>', false);
        $this->get('/admin/preview/en')->assertSee($payload['en']['preparation']);
        $this->post(route('admin.services.publish', $service), ['revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $this->get('/')->assertSee($payload['es']['preparation']);
        $payload['es']['preparation'] = null;
        $payload['en']['preparation'] = null;
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $this->get('/admin/preview/es')->assertDontSee('<dt>Preparación previa</dt>', false);
    }
}
