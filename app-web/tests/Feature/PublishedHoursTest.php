<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\User;
use App\Services\ContentPublisher;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishedHoursTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_schedule_and_structured_data_share_published_hours(): void
    {
        $this->withoutVite();
        $this->travelTo(now()->setDate(2026, 11, 8));
        $this->seed(ContentSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = ContentBlock::where('key', 'hours')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['week']['wednesday'] = ['closed' => false, 'sessions' => [['start' => '09:30', 'end' => '12:30', 'last' => '11:30']]];
        $payload['exceptions'] = [['date' => '2026-12-25', 'closed' => true, 'sessions' => [], 'es_note' => 'Cierre especial', 'en_note' => 'Special closure']];
        $publisher = app(ContentPublisher::class);
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertDontSee('09:30')->assertDontSee('Cierre especial');
        $this->actingAs($owner)->get('/admin/preview/es')->assertSee('09:30')->assertSee('Cierre especial');
        $block->refresh();
        $publisher->publish($block, $block->current_revision_id, $owner);
        $response = $this->withSession(['public_locale' => 'es'])->get('/')->assertSee('09:30')->assertSee('11:30')->assertSee('Cierre especial');
        $this->withSession(['public_locale' => 'en'])->get('/')->assertSee('9:30 a.m.')->assertSee('11:30 a.m.')->assertSee('Special closure');
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $response->getContent(), $matches);
        $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('MedicalClinic', $data['@type']);
        $wednesday = collect($data['openingHoursSpecification'])->firstWhere('dayOfWeek', 'https://schema.org/Wednesday');
        $this->assertSame('09:30', $wednesday['opens']);
        $this->assertSame('12:30', $wednesday['closes']);
        $this->assertSame('2026-12-25', $data['specialOpeningHoursSpecification'][0]['validFrom']);
        $this->assertArrayNotHasKey('opens', $data['specialOpeningHoursSpecification'][0]);
        $this->assertArrayNotHasKey('medicalSpecialty', $data);
    }

    public function test_structured_json_cannot_close_script_with_edited_copy(): void
    {
        $this->withoutVite();
        $this->seed(ContentSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $block = ContentBlock::where('key', 'home')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['es']['intro'] = '</script><script>alert(1)</script>';
        $publisher = app(ContentPublisher::class);
        $publisher->draft($block, $payload, $block->current_revision_id, $owner);
        $block->refresh();
        $publisher->publish($block, $block->current_revision_id, $owner);
        $this->withSession(['public_locale' => 'es'])->get('/')->assertOk()->assertDontSee('</script><script>alert(1)</script>', false)->assertSee('\\u003C', false);
    }
}
