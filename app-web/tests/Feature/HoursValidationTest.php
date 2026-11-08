<?php

namespace Tests\Feature;

use App\Services\ContentValidator;
use App\Support\ContentSchema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HoursValidationTest extends TestCase
{
    public function test_standard_schedule_and_closed_exception_are_valid(): void
    {
        $payload = ContentSchema::initial('hours');
        $payload['exceptions'][] = ['date' => '2026-12-25', 'closed' => true, 'sessions' => [], 'es_note' => 'Cerrado', 'en_note' => 'Closed'];
        $result = app(ContentValidator::class)->validate('hours', $payload);
        $this->assertTrue($result['week']['wednesday']['closed']);
        $this->assertSame([], $result['exceptions'][0]['sessions']);
        $this->assertSame('18:00', $result['week']['monday']['sessions'][1]['last']);
    }

    public static function invalidSessions(): array
    {
        return [
            'overlap' => [[['start' => '10:00', 'end' => '15:00', 'last' => '14:00'], ['start' => '14:30', 'end' => '19:00', 'last' => '18:00']]],
            'last after closing' => [[['start' => '10:00', 'end' => '15:00', 'last' => '16:00']]],
            'last at closing' => [[['start' => '10:00', 'end' => '15:00', 'last' => '15:00']]],
            'last before opening' => [[['start' => '10:00', 'end' => '15:00', 'last' => '09:00']]],
            'overnight' => [[['start' => '19:00', 'end' => '10:00', 'last' => '20:00']]],
            'incomplete' => [[['start' => '10:00', 'end' => '', 'last' => '14:00']]],
            'invalid clock' => [[['start' => '25:00', 'end' => '26:00', 'last' => '25:30']]],
            'no shifts' => [[]],
        ];
    }

    #[DataProvider('invalidSessions')]
    public function test_invalid_open_day_is_rejected(array $sessions): void
    {
        $payload = ContentSchema::initial('hours');
        $payload['week']['monday']['sessions'] = $sessions;
        $this->expectException(ValidationException::class);
        app(ContentValidator::class)->validate('hours', $payload);
    }

    public function test_duplicate_exception_dates_are_rejected(): void
    {
        $payload = ContentSchema::initial('hours');
        $day = ['date' => '2026-12-25', 'closed' => true, 'sessions' => []];
        $payload['exceptions'] = [$day, $day];
        $this->expectException(ValidationException::class);
        app(ContentValidator::class)->validate('hours', $payload);
    }
}
