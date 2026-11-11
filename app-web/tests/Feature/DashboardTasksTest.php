<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{ContentBlock, User};
use App\Services\{ContentPublisher, DashboardTasks, EditorialReview};
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
        $publisher->draft($services[1], $payload, $services[1]->current_revision_id, $owner);
        $hidden = $services[1]->fresh();
        $publisher->publish($hidden, $hidden->current_revision_id, $owner);
        $this->actingAs($owner)->get('/admin')->assertOk()->assertViewHas('published', 1)->assertViewHas('hidden', 1)
            ->assertSee('pendientes más')->assertSee(route('admin.services.order'))->assertSee(route('admin.preview.screen'));
        $this->get('/admin/services?status=hidden')->assertSee($hidden->currentRevision->payload['es']['name'])->assertDontSee($services[0]->currentRevision->payload['es']['name']);
    }

    public function test_tasks_prioritize_corrections_and_respect_publication_permissions_without_duplicates(): void
    {
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
        $editor = User::factory()->create();
        $owner = User::factory()->create(['role' => Role::Owner]);
        $admin = User::factory()->create(['role' => Role::Administrator]);
        $home = ContentBlock::where('key', 'home')->firstOrFail();
        $profile = ContentBlock::where('key', 'physician')->firstOrFail();
        $workflow = app(EditorialReview::class);
        $correction = $workflow->submit($home, $home->current_revision_id, null, $editor);
        $workflow->decide($correction, $owner, 'changes', 'Revisar el mensaje principal.');
        $pending = $workflow->submit($profile, $profile->current_revision_id, null, $editor);
        $tasks = app(DashboardTasks::class);
        $forEditor = $tasks->forUser($editor);
        $this->assertSame('Inicio y presentación', $forEditor['groups'][0]['items']->first()['title']);
        $this->assertCount(0, $forEditor['groups'][1]['items']);
        $this->assertCount(7, $forEditor['groups'][2]['items']);
        $this->assertSame(1, $forEditor['waiting']);
        $this->assertCount(0, $tasks->forUser($admin)['groups'][1]['items']);
        $this->assertSame(route('admin.reviews.show', $pending), $tasks->forUser($owner)['groups'][1]['items']->first()['url']);
        $this->actingAs($editor)->get('/admin')->assertOk()->assertSee('Revisar el mensaje principal.')->assertDontSee('Listo para tu revisión');
        app(ContentPublisher::class)->draft($home, $home->currentRevision->payload, $home->current_revision_id, $editor);
        $updated = $tasks->forUser($editor);
        $this->assertCount(0, $updated['groups'][0]['items']);
        $this->assertSame('Inicio y presentación', $updated['groups'][2]['items']->first()['title']);
    }
}
