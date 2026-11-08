<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\ServicePublisher;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(ServiceCatalogSeeder::class);
    }

    public function test_both_languages_and_contact_details_are_present_without_private_claims(): void
    {
        $this->get('/es')->assertOk()->assertSee('Atención personalizada')->assertSee('12731500')
            ->assertSee('https://wa.me/528781146336', false)->assertSee('14:00 y 18:00')
            ->assertDontSee('CNN-1956')->assertDontSee('RETMEDE')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/en')->assertOk()->assertSee('Personalized care')->assertSee('2:00 p.m. and 6:00 p.m.')
            ->assertSee('hreflang="es-MX"', false);
        $this->get('/fr')->assertNotFound();
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/admin');
    }

    public function test_drafts_are_only_visible_to_authenticated_preview_and_publication_is_explicit(): void
    {
        $service = Service::where('source_id', 10)->firstOrFail();
        $name = $service->currentRevision->payload['es']['name'];
        $this->get('/es')->assertDontSee($name);
        $this->get('/admin/preview/es')->assertRedirect('/admin/login');
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner)->get('/admin/preview/es')->assertOk()->assertSee($name)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner);
        $this->get('/es')->assertSee($name);
        $payload = $service->currentRevision->payload;
        $payload['es']['name'] = 'BORRADOR NO PUBLICADO';
        $payload['visible'] = false;
        app(ServicePublisher::class)->draft($service, $payload, $service->current_revision_id, $owner);
        $this->get('/es')->assertSee($name)->assertDontSee('BORRADOR NO PUBLICADO');
        $service->refresh();
        app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner);
        $this->get('/es')->assertDontSee($name)->assertDontSee('BORRADOR NO PUBLICADO');
    }
}
