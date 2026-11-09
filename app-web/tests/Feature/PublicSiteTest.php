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
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()->assertSee('Atención personalizada')->assertSee('12731500')
            ->assertSee('Ver horarios')->assertSee('id="hours"', false)->assertSee('class="mobile-contact"', false)
            ->assertSee('Consultar servicios por WhatsApp')->assertDontSee('data-service-query', false)
            ->assertSee('https://wa.me/528781146336', false)->assertSee('14:00 y 18:00')
            ->assertDontSee('CNN-1956')->assertDontSee('RETMEDE')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->withSession(['public_locale' => 'en'])->get('/')->assertOk()->assertSee('Personalized care')->assertSee('2:00 p.m. and 6:00 p.m.')
            ->assertSee('View office hours')->assertSee('Contact the office')
            ->assertSee('lang="en-US"', false)->assertDontSee('hreflang=', false);
        $this->get('/fr')->assertNotFound();
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/admin');
    }

    public function test_drafts_are_only_visible_to_authenticated_preview_and_publication_is_explicit(): void
    {
        $service = Service::where('source_id', 10)->firstOrFail();
        $name = $service->currentRevision->payload['es']['name'];
        $this->withSession(['public_locale' => 'es'])->get('/')->assertDontSee($name);
        $this->get('/admin/preview/es')->assertRedirect('/admin/login');
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner)->get('/admin/preview/es')->assertOk()->assertSee($name)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee($name)->assertSee('data-service-query', false)
            ->assertSee('https://wa.me/528781146336?text='.rawurlencode('Hola, quisiera información sobre el servicio: '.$name.'.'), false);
        $this->withSession(['public_locale' => 'en'])->get('/')->assertOk()->assertSee('Which service are you looking for?')
            ->assertSee('https://wa.me/528781146336?text='.rawurlencode('Hello, I would like information about this service: '.$service->currentRevision->payload['en']['name'].'.'), false);
        $payload = $service->currentRevision->payload;
        $payload['es']['name'] = 'BORRADOR NO PUBLICADO';
        $payload['visible'] = false;
        app(ServicePublisher::class)->draft($service, $payload, $service->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee($name)->assertDontSee('BORRADOR NO PUBLICADO');
        $service->refresh();
        app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertDontSee($name)->assertDontSee('BORRADOR NO PUBLICADO');
    }
}
