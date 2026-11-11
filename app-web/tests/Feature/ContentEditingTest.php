<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentEditingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
    }

    private function block(string $key): ContentBlock
    {
        return ContentBlock::where('key', $key)->firstOrFail();
    }

    public function test_drafts_are_private_and_publication_changes_both_locales(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner);
        $block = $this->block('home');
        $payload = $block->currentRevision->payload;
        $payload['es']['hero'] = 'TÍTULO NUEVO';
        $payload['en']['hero'] = 'NEW TITLE';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertRedirect(route('admin.content.edit', $block));
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()->assertDontSee('TÍTULO NUEVO');
        $this->get('/admin/preview/es')->assertOk()->assertSee('TÍTULO NUEVO');
        $this->get('/admin/preview/en')->assertOk()->assertSee('NEW TITLE');
        $block->refresh();
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('TÍTULO NUEVO');
        $this->withSession(['public_locale' => 'en'])->get('/')->assertSee('NEW TITLE');
        $this->get('/admin/activity')->assertOk()->assertSee('Contenido publicado')->assertSee('Inicio y presentación');
    }

    public function test_roles_stale_changes_and_cross_block_restores_are_enforced(): void
    {
        $editor = User::factory()->create(['role' => Role::Editor]);
        $this->actingAs($editor);
        $block = $this->block('home');
        $id = $block->current_revision_id;
        $data = ['revision_id' => $id, 'payload' => $block->currentRevision->payload];
        $this->put(route('admin.content.update', $block), $data)->assertSessionHasNoErrors();
        $this->put(route('admin.content.update', $block), $data)->assertSessionHasErrors('revision_id');
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->fresh()->current_revision_id])->assertForbidden();
        $other = $this->block('physician');
        $this->get(route('admin.content.revision', [$block, $other->current_revision_id]))->assertNotFound();
        $this->post(route('admin.content.restore', [$block, $other->current_revision_id]), ['revision_id' => $block->fresh()->current_revision_id])->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => Role::Administrator]));
        $this->post(route('admin.content.publish', $other), ['revision_id' => $other->current_revision_id])->assertForbidden();
        $this->post(route('admin.content.publish', $block), ['revision_id' => $id])->assertSessionHasErrors('revision_id');
    }

    public function test_contact_rejects_unsafe_links_and_derives_clickable_phone_consistently(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $block = $this->block('contact');
        $payload = $block->currentRevision->payload;
        $payload['shared']['maps'] = 'https://attacker.example/maps';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasErrors('shared.maps');
        $payload['shared']['maps'] = config('clinic.maps');
        $payload['shared']['phone'] = '+528781110000';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasNoErrors();
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->fresh()->current_revision_id])->assertSessionHasNoErrors();
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('https://wa.me/528781110000', false)->assertSee('tel:+528781110000', false)->assertSee('+52 878 111 0000');
    }

    public function test_privacy_requires_both_languages_and_owner_publication(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $block = $this->block('legal');
        $this->withSession(['public_locale' => 'es'])->get('/aviso-de-privacidad')->assertNotFound();
        $this->get(route('admin.content.edit', $block))->assertOk()
            ->assertSee('Guarda el aviso en español e inglés')
            ->assertDontSee(route('admin.preview.privacy', 'es'));
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertSessionHasErrors('es.privacy_content');
        $payload = $block->currentRevision->payload;
        $payload['es']['privacy_content'] = 'Texto de prueba aprobado. <script>alert(1)</script>';
        $payload['en']['privacy_content'] = 'Approved test copy.';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasNoErrors();
        $this->withSession(['public_locale' => 'es'])->get('/aviso-de-privacidad')->assertNotFound();
        $this->get('/admin/preview/es/privacy')->assertOk()->assertSee('Texto de prueba aprobado');
        $this->get(route('admin.content.edit', $block))->assertOk()
            ->assertSee(route('admin.preview.privacy', 'es'))
            ->assertSee(route('admin.preview.privacy', 'en'));
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->fresh()->current_revision_id])->assertSessionHasNoErrors();
        $this->withSession(['public_locale' => 'es'])->get('/aviso-de-privacidad')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
        $this->withSession(['public_locale' => 'en'])->get('/aviso-de-privacidad')->assertOk()->assertSee('Approved test copy.')
            ->assertSee('class="site-footer"', false)->assertSee('href="'.route('home').'#hours"', false)->assertSee('class="privacy-end"', false)->assertSee('href="#main"', false);
        $this->get('/admin/preview/en/privacy')->assertOk()->assertSee('href="'.route('admin.preview', 'en').'#hours"', false);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('Aviso de privacidad');
    }

    public function test_all_editors_render_and_restore_keeps_public_version(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $this->get('/admin/content')->assertOk();
        foreach (ContentBlock::all() as $block) {
            $this->get(route('admin.content.edit', $block))->assertOk()->assertSee($block->label());
            $this->get(route('admin.content.revision', [$block, $block->current_revision_id]))->assertOk();
        }
        $block = $this->block('home');
        $initial = $block->current_revision_id;
        $this->post(route('admin.content.publish', $block), ['revision_id' => $initial])->assertSessionHasNoErrors();
        $this->post(route('admin.content.restore', [$block, $initial]), ['revision_id' => $initial])->assertSessionHasNoErrors();
        $block->refresh();
        $this->assertSame($initial, $block->published_revision_id);
        $this->assertNotSame($initial, $block->current_revision_id);
        $this->assertDatabaseHas('audit_events', ['content_block_id' => $block->id, 'action' => 'content.restored']);
    }

    public function test_validation_preserves_unsaved_input_and_links_errors_to_the_language(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $block = $this->block('home');
        $url = route('admin.content.edit', $block);
        $payload = $block->currentRevision->payload;
        $payload['en']['hero'] = '';
        $payload['es']['hero'] = 'Cambio todavía sin guardar';

        $this->from($url)->put(route('admin.content.update', $block), [
            'revision_id' => $block->current_revision_id,
            'payload' => $payload,
        ])->assertRedirect($url)->assertSessionHasErrors('en.hero');

        $this->withCookie(session()->getName(), session()->getId())->get($url)->assertOk()
            ->assertSee('data-recovered-input="true"', false)
            ->assertSee('data-error-field="en.hero"', false)
            ->assertSee('Cambio todavía sin guardar');
        $this->assertSame($block->current_revision_id, $block->fresh()->current_revision_id);
    }
}
