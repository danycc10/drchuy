<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{ContentBlock, Service, User};
use App\Services\{ContentPublisher, ServicePublisher};
use Database\Seeders\{ContentSeeder, ServiceCatalogSeeder};
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_markup_has_language_labels_unique_anchors_and_image_dimensions_in_both_languages(): void
    {
        $this->withoutVite();
        $this->seed([ContentSeeder::class, ServiceCatalogSeeder::class]);
        $owner = User::factory()->create(['role' => Role::Owner]);
        foreach (Service::all() as $service) { app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner); }
        $faq = ContentBlock::where('key', 'faq')->firstOrFail();
        app(ContentPublisher::class)->publish($faq, $faq->current_revision_id, $owner);
        foreach (['es', 'en'] as $locale) {
            $response = $this->withSession(['public_locale' => $locale])->get('/')->assertOk();
            $document = new DOMDocument();
            $previous = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $xpath = new DOMXPath($document);
            $this->assertSame($locale, explode('-', $document->documentElement->getAttribute('lang'))[0]);
            $this->assertSame(1, $xpath->query('//h1')->length);
            $this->assertSame(1, $xpath->query('//main')->length);
            $this->assertSame(0, $xpath->query('//img[not(@alt) or not(@width) or not(@height)]')->length);
            $ids = [];
            foreach ($xpath->query('//*[@id]') as $element) {
                $id = $element->getAttribute('id');
                $this->assertNotContains($id, $ids, 'Duplicate anchor: '.$id);
                $ids[] = $id;
            }
            foreach ($xpath->query('//a[starts-with(@href, "#")]') as $link) { $this->assertContains(substr($link->getAttribute('href'), 1), $ids); }
            foreach ($xpath->query('//a[@target="_blank"]') as $link) { $this->assertStringContainsString('noopener', $link->getAttribute('rel')); }
            foreach ($xpath->query('//button | //a[@href]') as $control) {
                $this->assertNotSame('', trim($control->textContent.$control->getAttribute('aria-label').implode('', array_map(fn ($img) => $img->getAttribute('alt'), iterator_to_array($control->getElementsByTagName('img'))))));
            }
            $this->assertGreaterThan(0, $xpath->query('//section[@id="faq"]//details/summary')->length);
        }
    }
}
