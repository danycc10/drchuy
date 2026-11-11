<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\User;
use App\Services\ContentPublisher;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches);

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $matches[1]);
    }

    public function test_structured_data_describes_the_clinic_with_a_postal_address_and_the_physician(): void
    {
        $base = rtrim(config('app.url'), '/');
        $response = $this->withSession(['public_locale' => 'es'])->get('/')->assertOk();
        [$clinic] = $this->jsonLd($response->getContent());
        $this->assertSame('MedicalClinic', $clinic['@type']);
        $this->assertSame(config('clinic.es.description'), $clinic['description']);
        $this->assertSame([
            '@type' => 'PostalAddress', 'streetAddress' => 'Calle Argentina 119, Col. Delicias, Consultorio 3', 'addressCountry' => 'MX',
            'postalCode' => '26070', 'addressLocality' => 'Piedras Negras', 'addressRegion' => 'Coahuila',
        ], $clinic['address']);
        $this->assertSame('MXN', $clinic['currenciesAccepted']);
        $this->assertSame(config('clinic.es.payment'), $clinic['paymentAccepted']);
        $this->assertSame('Person', $clinic['employee']['@type']);
        $this->assertSame($base.'/#clinic', $clinic['employee']['worksFor']['@id']);
        $this->assertSame('12731500', $clinic['employee']['identifier']['value']);
        $this->assertStringContainsString('/images/doctor.webp', $clinic['employee']['image']);
        $this->assertArrayNotHasKey('medicalSpecialty', $clinic);
        $this->assertCount(1, $this->jsonLd($response->getContent()), 'The FAQ entity only appears once questions are published');
        $response->assertSee('property="og:site_name"', false)->assertSee('property="og:image:width"', false)
            ->assertSee('property="og:locale:alternate" content="en_US"', false)
            ->assertSee('name="twitter:card" content="summary"', false)
            ->assertSee('rel="apple-touch-icon"', false)->assertSee('icon-192.png', false)->assertSee('name="theme-color"', false);
        foreach (['favicon.ico', 'icon-32.png', 'icon-192.png', 'icon-512.png', 'apple-touch-icon.png'] as $icon) {
            $this->assertGreaterThan(0, filesize(public_path($icon)), $icon.' must not be empty');
        }

        // A free-text city line without a postal code degrades gracefully.
        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = ContentBlock::where('key', 'contact')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['shared']['city'] = 'Piedras Negras';
        $publisher = app(ContentPublisher::class);
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);
        $block->refresh();
        $publisher->publish($block, $block->current_revision_id, $owner);
        [$clinic] = $this->jsonLd($this->withSession(['public_locale' => 'en'])->get('/')->getContent());
        $this->assertSame('Piedras Negras', $clinic['address']['addressLocality']);
        $this->assertArrayNotHasKey('postalCode', $clinic['address']);
        $this->assertArrayNotHasKey('addressRegion', $clinic['address']);
    }

    public function test_published_questions_add_an_faq_entity_in_the_selected_language_but_not_in_preview(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = ContentBlock::where('key', 'faq')->firstOrFail();
        $this->actingAs($owner)->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        foreach (['es' => '¿Cómo solicito una cita?', 'en' => 'How do I request an appointment?'] as $locale => $question) {
            $entities = $this->jsonLd($this->withSession(['public_locale' => $locale])->get('/')->assertOk()->getContent());
            $this->assertCount(2, $entities);
            $this->assertSame('FAQPage', $entities[1]['@type']);
            $this->assertSame($question, $entities[1]['mainEntity'][0]['name']);
            $this->assertSame(config('clinic.'.$locale.'.appointment'), $entities[1]['mainEntity'][0]['acceptedAnswer']['text']);
            $this->assertCount(3, $entities[1]['mainEntity']);
        }
        $this->assertCount(0, $this->jsonLd($this->actingAs($owner)->get('/admin/preview/es')->assertOk()->getContent()));
    }

    public function test_sitemap_reports_publication_dates_and_privacy_page_has_its_own_metadata(): void
    {
        config(['clinic.indexable' => true]);
        $base = rtrim(config('app.url'), '/');
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>'.$base.'/</loc>', false)->assertDontSee('<lastmod>', false);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->travelTo(now()->setDate(2026, 11, 12)->setTime(15, 0));
        $block = ContentBlock::where('key', 'legal')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['es']['privacy_content'] = 'Aviso de prueba.';
        $payload['en']['privacy_content'] = 'Test notice.';
        $publisher = app(ContentPublisher::class);
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);
        $block->refresh();
        $publisher->publish($block, $block->current_revision_id, $owner);
        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('<loc>'.$base.'/</loc><lastmod>2026-11-12</lastmod>', false)
            ->assertSee('<loc>'.$base.'/aviso-de-privacidad</loc><lastmod>2026-11-12</lastmod>', false);
        $this->withSession(['public_locale' => 'es'])->get('/aviso-de-privacidad')->assertOk()
            ->assertSee('<meta name="description" content="Aviso de privacidad · Medicina Respiratoria, Dr. Jesús García Espinoza. Piedras Negras, Coahuila.">', false)
            ->assertSee('property="og:title" content="Aviso de privacidad · Medicina Respiratoria"', false)
            ->assertSee('rel="apple-touch-icon"', false);
    }

    public function test_not_found_page_is_branded_bilingual_and_offers_a_way_back(): void
    {
        $this->withHeader('Accept-Language', 'es-MX,es;q=0.9,en;q=0.5')->get('/pagina-que-no-existe')->assertNotFound()
            ->assertSee('No encontramos esta página.')->assertSee('Volver al inicio')->assertSee('href="'.route('home').'"', false)
            ->assertSee('https://wa.me/528781146336', false)->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee('lang="es-MX"', false);
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/missing')->assertNotFound()
            ->assertSee('We could not find this page.')->assertSee('Back to home')->assertSee('lang="en-US"', false);
    }
}
