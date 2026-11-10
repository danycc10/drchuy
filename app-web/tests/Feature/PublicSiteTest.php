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
            ->assertSee('data-service-filter="procedures" aria-pressed="true"', false)
            ->assertSee('https://wa.me/528781146336?text='.rawurlencode('Hola, quisiera solicitar el siguiente servicio: '.$name.'. ¿Me pueden confirmar la disponibilidad y cómo agendar?'), false);
        $this->withSession(['public_locale' => 'en'])->get('/')->assertOk()->assertSee('Which service are you looking for?')
            ->assertSee('https://wa.me/528781146336?text='.rawurlencode('Hello, I would like to request the following service: '.$service->currentRevision->payload['en']['name'].'. Could you confirm availability and how to book an appointment?'), false);
        $payload = $service->currentRevision->payload;
        $payload['es']['name'] = 'BORRADOR NO PUBLICADO';
        $payload['visible'] = false;
        app(ServicePublisher::class)->draft($service, $payload, $service->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee($name)->assertDontSee('BORRADOR NO PUBLICADO');
        $service->refresh();
        app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertDontSee($name)->assertDontSee('BORRADOR NO PUBLICADO');
    }

    public function test_appointment_actions_share_an_editable_message_in_the_selected_language(): void
    {
        foreach (['es' => 'Hola, quisiera solicitar una cita', 'en' => 'Hello, I would like to request an appointment'] as $locale => $message) {
            $html = $this->withSession(['public_locale' => $locale])->get('/')->assertOk()->getContent();
            preg_match_all('/href="([^"]+)" data-appointment-link/', $html, $matches);
            $this->assertCount(5, $matches[1]);
            $this->assertCount(1, array_unique($matches[1]));
            $this->assertStringContainsString(rawurlencode($message), $matches[1][0]);
            $this->assertStringStartsWith(config('clinic.whatsapp', 'https://wa.me/'.ltrim(config('clinic.phone'), '+')), $matches[1][0]);
            $this->assertStringContainsString($locale === 'es' ? 'Espera la confirmación' : 'Wait for confirmation', $html);
        }
    }

    public function test_visit_card_uses_published_contact_details_and_keeps_draft_preview_distinct(): void
    {
        $this->seed(\Database\Seeders\ContentSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = \App\Models\ContentBlock::where('key', 'contact')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['shared']['address'] = 'Calle <Principal> 100';
        $payload['shared']['phone'] = '+528781234567';
        $publisher = app(\App\Services\ContentPublisher::class);
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);
        $block->refresh();
        $publisher->publish($block, $block->current_revision_id, $owner);
        $block->refresh();
        $payload['shared']['address'] = 'DIRECCION BORRADOR';
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);

        foreach (['es' => 'Copiar dirección', 'en' => 'Copy address'] as $locale => $label) {
            $html = $this->withSession(['public_locale' => $locale])->get('/')->assertOk()->getContent();
            preg_match('~<article class="visit-card".*?</article>~s', $html, $matches);
            $this->assertNotEmpty($matches);
            $this->assertStringContainsString('Calle &lt;Principal&gt; 100', $matches[0]);
            $this->assertStringNotContainsString('DIRECCION BORRADOR', $matches[0]);
            $this->assertStringContainsString('href="tel:+528781234567"', $matches[0]);
            $this->assertStringContainsString('id="hours"', $matches[0]);
            $this->assertStringContainsString($label, $matches[0]);
            $this->assertStringContainsString('data-print-visit hidden', $matches[0]);
        }
        $this->actingAs($owner)->get('/admin/preview/es')->assertOk()
            ->assertSee('DIRECCION BORRADOR')->assertSee('datos del borrador')->assertDontSee('data-print-visit');
    }
}
