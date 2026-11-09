<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
    }

    private function block(): ContentBlock
    {
        return ContentBlock::where('key', 'faq')->firstOrFail();
    }

    public function test_faq_drafts_preview_and_publication_preserve_language_order_visibility_and_escaping(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Administrator]));
        $block = $this->block();
        $payload = $block->currentRevision->payload;
        $payload['items'] = array_reverse($payload['items']);
        $payload['items'][1]['visible'] = false;
        $payload['items'][0]['es']['answer'] = 'Respuesta revisada <script>alert(1)</script>';
        $payload['items'][0]['en']['answer'] = 'Reviewed answer';
        $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertDontSee('id="faq"', false)->assertDontSee('href="#faq"', false);
        $this->get('/admin/preview/es')->assertOk()->assertSee('id="faq"', false)
            ->assertSeeInOrder([$payload['items'][0]['es']['question'], $payload['items'][2]['es']['question']])
            ->assertDontSee($payload['items'][1]['es']['question'])
            ->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/admin/preview/en')->assertSee('Reviewed answer');
        $block->refresh();
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('id="faq"', false)->assertSee('Respuesta revisada');
        $this->post('/idioma', ['locale' => 'en', 'page' => 'home', 'anchor' => 'faq'])->assertRedirect(url('/').'#faq');
        $this->get('/')->assertSee('Reviewed answer')->assertDontSee('Respuesta revisada');
        $this->get(route('admin.content.revision', [$block, $block->current_revision_id]))->assertOk()
            ->assertSee('Oculta')->assertSee('Reviewed answer');
    }

    public function test_editors_cannot_publish_and_restore_creates_a_draft_without_replacing_public_faq(): void
    {
        $block = $this->block();
        $initial = $block->current_revision_id;
        $this->actingAs(User::factory()->create(['role' => Role::Editor]));
        $this->post(route('admin.content.publish', $block), ['revision_id' => $initial])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $this->post(route('admin.content.publish', $block), ['revision_id' => $initial])->assertSessionHasNoErrors();
        $payload = $block->currentRevision->payload;
        unset($payload['items']); // Browsers omit the list when every question is removed.
        $this->put(route('admin.content.update', $block), ['revision_id' => $initial, 'payload' => $payload])->assertSessionHasNoErrors();
        $block->refresh();
        $this->assertSame([], $block->currentRevision->payload['items']);
        $this->get('/admin/preview/es')->assertDontSee('id="faq"', false);
        $this->get('/')->assertSee('id="faq"', false);
        $this->post(route('admin.content.publish', $block), ['revision_id' => $block->current_revision_id])->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('id="faq"', false);
        $published = $block->current_revision_id;
        $this->post(route('admin.content.restore', [$block, $initial]), ['revision_id' => $published])->assertSessionHasNoErrors();
        $block->refresh();
        $this->assertSame($published, $block->published_revision_id);
        $this->assertNotSame($initial, $block->current_revision_id);
        $this->get('/admin/preview/es')->assertSee('id="faq"', false);
        $this->get('/')->assertDontSee('id="faq"', false);
        $this->assertDatabaseHas('audit_events', ['content_block_id' => $block->id, 'action' => 'content.restored']);
    }

    public function test_faq_rejects_incomplete_translations_oversized_lists_and_unexpected_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $block = $this->block();
        $initial = $block->currentRevision->payload;
        foreach ([
            ['items.0.en.answer', ''], ['items.0.es.question', str_repeat('a', 201)],
            ['items.0.es.answer', str_repeat('a', 1501)], ['items.0.visible', 'invalid'],
            ['items.0.en', ['question' => 'Question', 'answer' => 'Answer', 'html' => 'unexpected']],
            ['items', array_fill(0, 13, $initial['items'][0])],
        ] as [$key, $value]) {
            $payload = $initial;
            data_set($payload, $key, $value);
            $this->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasErrors($key);
        }
        $this->assertSame($block->current_revision_id, $block->fresh()->current_revision_id);
    }

    public function test_validation_keeps_removed_questions_removed_and_recovers_unsaved_translations(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $block = $this->block();
        $url = route('admin.content.edit', $block);
        $payload = $block->currentRevision->payload;
        $payload['items'] = [array_pop($payload['items'])];
        $payload['items'][0]['es']['question'] = 'Pregunta todavía sin guardar';
        $payload['items'][0]['en']['answer'] = '';
        $this->from($url)->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasErrors('items.0.en.answer');
        $this->withCookie(session()->getName(), session()->getId())->get($url)->assertOk()
            ->assertSee('Pregunta todavía sin guardar')->assertSee('data-error-field="items.0.en.answer"', false)
            ->assertDontSee('¿Cómo solicito una cita?');

        unset($payload['items']);
        $payload['es']['faq_title'] = '';
        $this->from($url)->put(route('admin.content.update', $block), ['revision_id' => $block->current_revision_id, 'payload' => $payload])->assertSessionHasErrors('es.faq_title');
        $this->withCookie(session()->getName(), session()->getId())->get($url)->assertOk()->assertDontSee('¿Cómo solicito una cita?');
    }
}
