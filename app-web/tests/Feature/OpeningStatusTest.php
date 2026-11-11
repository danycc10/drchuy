<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\User;
use App\Services\ContentPublisher;
use App\Services\PublicContent;
use App\Support\ContentSchema;
use Carbon\CarbonImmutable;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningStatusTest extends TestCase
{
    use RefreshDatabase;

    private function at(string $moment): CarbonImmutable
    {
        return CarbonImmutable::parse($moment, 'America/Matamoros');
    }

    public function test_status_follows_the_published_week_and_exceptions_in_both_languages(): void
    {
        $content = app(PublicContent::class);
        $hours = ContentSchema::initial('hours');
        // 10 November 2026 is a Tuesday with two sessions; Wednesday is closed by default.
        $open = $content->openingStatus($hours, 'es', $this->at('2026-11-10 11:00'));
        $this->assertTrue($open['open']);
        $this->assertSame('Abierto ahora', $open['label']);
        $this->assertSame('Cierra a las 15:00. Última cita: 14:00.', $open['detail']);
        $this->assertSame('10:00–15:00 y 16:00–19:00', $open['today_hours']);
        $this->assertSame('tuesday', $open['day']);
        $this->assertFalse($open['exception']);
        $this->assertSame('Cierra a las 15:00.', $content->openingStatus($hours, 'es', $this->at('2026-11-10 14:30'))['detail']);
        $this->assertSame('Closes at 3:00 p.m. Last appointment: 2:00 p.m.', $content->openingStatus($hours, 'en', $this->at('2026-11-10 11:00'))['detail']);

        $between = $content->openingStatus($hours, 'es', $this->at('2026-11-10 15:30'));
        $this->assertFalse($between['open']);
        $this->assertSame('Cerrado ahora', $between['label']);
        $this->assertSame('Abre hoy a las 16:00.', $between['detail']);
        $this->assertSame('Abre el jueves a las 10:00.', $content->openingStatus($hours, 'es', $this->at('2026-11-10 20:00'))['detail']);
        $this->assertSame('Opens on Thursday at 10:00 a.m.', $content->openingStatus($hours, 'en', $this->at('2026-11-10 20:00'))['detail']);

        $wednesday = $content->openingStatus($hours, 'es', $this->at('2026-11-11 12:00'));
        $this->assertTrue($wednesday['closed_today']);
        $this->assertSame('', $wednesday['today_hours']);
        $this->assertSame('Abre mañana a las 10:00.', $wednesday['detail']);
        $this->assertSame('Opens tomorrow at 10:00 a.m.', $content->openingStatus($hours, 'en', $this->at('2026-11-11 12:00'))['detail']);

        $hours['exceptions'] = [
            ['date' => '2026-11-12', 'closed' => true, 'sessions' => [], 'es_note' => '', 'en_note' => ''],
            ['date' => '2026-11-13', 'closed' => false, 'sessions' => [['start' => '09:00', 'end' => '12:00', 'last' => '11:00']], 'es_note' => '', 'en_note' => ''],
        ];
        $this->assertSame('Abre el viernes a las 09:00.', $content->openingStatus($hours, 'es', $this->at('2026-11-11 12:00'))['detail']);
        $special = $content->openingStatus($hours, 'es', $this->at('2026-11-13 10:00'));
        $this->assertTrue($special['open']);
        $this->assertTrue($special['exception']);
        $this->assertSame('09:00–12:00', $special['today_hours']);
        $this->assertSame('Cierra a las 12:00. Última cita: 11:00.', $special['detail']);

        foreach (array_keys($hours['week']) as $day) {
            $hours['week'][$day] = ['closed' => true, 'sessions' => []];
        }
        $hours['exceptions'] = [];
        $none = $content->openingStatus($hours, 'es', $this->at('2026-11-10 11:00'));
        $this->assertSame('Cerrado ahora', $none['label']);
        $this->assertSame('', $none['detail']);
        // The office time zone applies even when the given instant is expressed elsewhere.
        $this->assertTrue($content->openingStatus(ContentSchema::initial('hours'), 'es', CarbonImmutable::parse('2026-11-10 17:30', 'UTC'))['open']);
    }

    public function test_public_page_shows_the_current_status_and_marks_today(): void
    {
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
        $this->travelTo($this->at('2026-11-10 11:00'));
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()
            ->assertSee('Abierto ahora')->assertSee('Cierra a las 15:00. Última cita: 14:00.')
            ->assertSee('class="hours-status is-open"', false)->assertSee('class="shortcut-status is-open"', false)
            ->assertSee('martes 10 de noviembre')->assertSee('aria-current="date"', false)->assertSee('today-badge', false);
        $this->withSession(['public_locale' => 'en'])->get('/')->assertOk()->assertSee('Open now')->assertSee('Tuesday, November 10');

        $this->travelTo($this->at('2026-11-11 12:00'));
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()
            ->assertSee('Cerrado ahora')->assertSee('Abre mañana a las 10:00.')->assertSee('class="shortcut-status is-closed"', false)
            ->assertSee('miércoles 11 de noviembre: cerrado')->assertSee('aria-current="date"', false);

        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = ContentBlock::where('key', 'hours')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['exceptions'] = [['date' => '2026-11-11', 'closed' => false, 'sessions' => [['start' => '09:00', 'end' => '13:00', 'last' => '12:00']], 'es_note' => 'Jornada especial', 'en_note' => 'Special day']];
        $publisher = app(ContentPublisher::class);
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('Cerrado ahora')->assertDontSee('horario especial');
        $this->actingAs($owner)->get('/admin/preview/es')->assertOk()->assertSee('Abierto ahora')->assertSee('horario especial');
        $block->refresh();
        $publisher->publish($block, $block->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()
            ->assertSee('Abierto ahora')->assertSee('Cierra a las 13:00.')->assertSee('09:00–13:00 · horario especial');
    }
}
