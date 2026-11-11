<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevicePreviewTest extends TestCase
{
    use RefreshDatabase;

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
