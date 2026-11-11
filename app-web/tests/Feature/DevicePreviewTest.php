<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevicePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_preview_targets_its_category_and_can_review_a_hidden_card_without_exposing_it_publicly(): void
    {
        $this->withoutVite();
        $this->seed(\Database\Seeders\ServiceCatalogSeeder::class);
        $owner = User::factory()->create(['role' => \App\Enums\Role::Owner]);
        $service = \App\Models\Service::where('source_id', 10)->firstOrFail();
        $payload = $service->currentRevision->payload;
        $payload['visible'] = false;
        app(\App\Services\ServicePublisher::class)->draft($service, $payload, $service->current_revision_id, $owner);
        $this->actingAs($owner);
        $this->get('/admin/preview/es?service_id='.$service->id)->assertOk()->assertSee('data-preview-focus', false)
            ->assertSee('data-service-filter="procedures" aria-pressed="true"', false)->assertSee('Servicio oculto:')
            ->assertSee(route('admin.preview', ['locale' => 'en', 'service_id' => $service->id]));
        $this->get('/admin/preview/es')->assertDontSee('id="service-'.$service->id.'"', false);
        $this->get('/admin/preview-screen?service_id='.$service->id)->assertOk()->assertSee('name="service_id"', false)->assertSee('#service-'.$service->id, false);
        $this->get('/?service_id='.$service->id)->assertDontSee('data-preview-focus', false)->assertDontSee('id="service-'.$service->id.'"', false);
        $this->getJson('/admin/preview/es?service_id=999999')->assertUnprocessable();
        auth()->logout();
        $this->get('/admin/preview/es?service_id='.$service->id)->assertRedirect(route('login'));
    }

    public function test_preview_requires_login_only_allows_local_pages_and_frames_only_preview_routes(): void
    {
        $this->withoutVite();
        $this->get('/admin/preview-screen')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get('/admin/preview-screen?locale=en&section=services')->assertOk()->assertSee(route('admin.preview', 'en').'#services')->assertHeader('X-Frame-Options', 'DENY');
        $this->get('/admin/preview/es')->assertOk()->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('Content-Security-Policy', "frame-ancestors 'self'");
        $this->getJson('/admin/preview-screen?page=https://example.com')->assertUnprocessable();
        $this->getJson('/admin/preview-screen?locale=fr')->assertUnprocessable();
        $this->get('/admin/services')->assertHeader('X-Frame-Options', 'DENY');
    }
}
