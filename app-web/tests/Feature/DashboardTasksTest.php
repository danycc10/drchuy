<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{ContentBlock, User};
use App\Services\{ContentPublisher, DashboardTasks};
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_distinguishes_hidden_and_visible_services_and_exposes_all_pending_tasks(): void
    {
        $this->withoutVite();
        $this->seed([\Database\Seeders\ServiceCatalogSeeder::class, ContentSeeder::class]);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $services = \App\Models\Service::take(2)->get();
        $publisher = app(\App\Services\ServicePublisher::class);
        $publisher->publish($services[0], $services[0]->current_revision_id, $owner);
        $payload = $services[1]->currentRevision->payload;
        $payload['visible'] = false;
        $publisher->save($services[1], $payload, $services[1]->current_revision_id, $owner);
        $hidden = $services[1]->fresh();
        $this->actingAs($owner)->get('/admin')->assertOk()->assertViewHas('published', 1)->assertViewHas('hidden', 1)
            ->assertSee('pendientes más')->assertSee(route('admin.services.order'))->assertSee(route('admin.preview.screen'))->assertDontSee('Revisiones');
        $this->get('/admin/services?status=hidden')->assertSee($hidden->currentRevision->payload['es']['name'])->assertDontSee($services[0]->currentRevision->payload['es']['name']);
    }

    public function test_tasks_list_only_versions_the_site_does_not_show_yet(): void
    {
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $home = ContentBlock::where('key', 'home')->firstOrFail();
        $tasks = app(DashboardTasks::class);
        $this->assertCount(ContentBlock::count(), $tasks->forUser($owner)['groups'][0]['items']);
        $publisher = app(ContentPublisher::class);
        $publisher->publish($home, $home->current_revision_id, $owner);
        $this->assertCount(ContentBlock::count() - 1, $tasks->forUser($owner)['groups'][0]['items']);
        $publisher->draft($home, $home->currentRevision->payload, $home->current_revision_id, $owner);
        $pending = $tasks->forUser($owner)['groups'][0]['items'];
        $this->assertCount(ContentBlock::count(), $pending);
        $this->assertSame('Inicio y presentación', $pending->first()['title']);
        $this->assertSame('La última versión guardada todavía no se muestra en el sitio.', $pending->first()['description']);
        $this->assertSame(route('admin.content.edit', $home), $pending->first()['url']);
        $this->actingAs($owner)->get('/admin')->assertOk()->assertSee('Pendiente de publicar')->assertSee('Abrir y publicar');
        foreach (ContentBlock::all() as $block) {
            if ($block->key === 'legal') { // The privacy notice ships empty and cannot be published until both languages exist.
                $payload = $block->currentRevision->payload;
                $payload['es']['privacy_content'] = 'Aviso de prueba.';
                $payload['en']['privacy_content'] = 'Test notice.';
                $publisher->save($block, $payload, $block->current_revision_id, $owner);
                continue;
            }
            $publisher->publish($block, $block->current_revision_id, $owner);
        }
        $this->assertCount(0, $tasks->forUser($owner)['groups'][0]['items']);
        $this->get('/admin')->assertOk()->assertSee('Todo lo guardado ya se muestra en el sitio.')->assertDontSee('Abrir y publicar');
    }
}
