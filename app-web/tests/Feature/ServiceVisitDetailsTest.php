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

    public function test_visit_details_are_optional_translated_published_on_save_and_escaped(): void
    {
        $this->withoutVite();
        $this->seed(ServiceCatalogSeeder::class);
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $service = Service::firstOrFail();
        $this->get('/admin/preview/es')->assertDontSee('service-visit-details', false);
        $payload = $service->currentRevision->payload;
        $payload['es']['preparation'] = 'Indicaciones <script>alert(1)</script>';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasErrors('en.preparation');
        $this->assertNull($service->fresh()->published_revision_id);
        $payload['en']['preparation'] = 'Instructions confirmed by the physician';
        $payload['es']['bring'] = 'Documento confirmado por el médico';
        $payload['en']['bring'] = 'Document confirmed by the physician';
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $service->refresh();
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('Indicaciones')->assertSee($payload['es']['bring'])->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<dt>Duración aproximada</dt>', false);
        $this->withSession(['public_locale' => 'en'])->get('/')->assertSee($payload['en']['preparation']);
        $payload['es']['preparation'] = null;
        $payload['en']['preparation'] = null;
        $this->put(route('admin.services.update', $service), [...$payload, 'revision_id' => $service->current_revision_id])->assertSessionHasNoErrors();
        $this->withSession(['public_locale' => 'es'])->get('/')->assertDontSee('<dt>Preparación previa</dt>', false)->assertSee($payload['es']['bring']);
    }
}
