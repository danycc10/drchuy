<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\{Service, User};
use App\Services\ServicePublisher;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reorder_saves_drafts_atomically_preserves_publication_and_rejects_stale_or_partial_lists(): void
    {
        $this->withoutVite();
        $this->seed(ServiceCatalogSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner);
        $category = Service::firstOrFail()->service_category_id;
        $services = Service::where('service_category_id', $category)->get();
        foreach ($services as $service) { app(ServicePublisher::class)->publish($service, $service->current_revision_id, $owner); }
        $services = Service::where('service_category_id', $category)->get();
        $public = $services->pluck('published_revision_id', 'id')->all();
        $data = ['category' => $category, 'order' => $services->pluck('id')->reverse()->values()->all(), 'revisions' => $services->pluck('current_revision_id', 'id')->all()];
        $this->get(route('admin.services.order', ['category' => $category]))->assertOk();
        $this->actingAs(User::factory()->create(['role' => Role::Editor]));
        $this->post(route('admin.services.reorder'), $data)->assertSessionHasNoErrors();
        foreach ($data['order'] as $index => $id) {
            $service = Service::findOrFail($id);
            $this->assertSame($index + 1, $service->currentRevision->payload['position']);
            $this->assertSame($public[$id], $service->published_revision_id);
        }
        $current = Service::where('service_category_id', $category)->pluck('current_revision_id', 'id')->all();
        $this->post(route('admin.services.reorder'), $data)->assertSessionHasErrors('order');
        $this->assertSame($current, Service::where('service_category_id', $category)->pluck('current_revision_id', 'id')->all());
        $data['revisions'] = $current;
        array_pop($data['order']);
        $this->post(route('admin.services.reorder'), $data)->assertSessionHasErrors('order');
        $this->assertSame($current, Service::where('service_category_id', $category)->pluck('current_revision_id', 'id')->all());
        $data['order'][] = Service::where('service_category_id', '!=', $category)->firstOrFail()->id;
        $this->post(route('admin.services.reorder'), $data)->assertSessionHasErrors('order');
        auth()->logout();
        $this->post(route('admin.services.reorder'), $data)->assertRedirect(route('login'));
    }
}
