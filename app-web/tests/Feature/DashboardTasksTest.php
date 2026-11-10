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
