<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Support\ContentSchema;
use Carbon\CarbonImmutable;

class PublicContent
{
    public function snapshot(bool $preview = false): array
    {
        $clinic = config('clinic');
        $hours = ContentSchema::initial('hours');
        $relation = $preview ? 'currentRevision' : 'publishedRevision';
        $privacyUpdated = null;
        $mediaPayload = null;
        $clinic['faq'] = [];
        foreach (ContentBlock::with($relation)->get() as $block) {
            $revision = $block->{$relation};
            if (! $revision) {
                continue;
            }
            $payload = $revision->payload;
            if ($block->key === 'media') {
                $mediaPayload = $payload;
                continue;
            }
            $clinic = array_replace($clinic, $payload['shared'] ?? []);
            foreach (['es', 'en'] as $locale) {
                $clinic[$locale] = array_replace($clinic[$locale], $payload[$locale] ?? []);
            }
            if ($block->key === 'hours') {
                $hours = $payload;
            }
            if ($block->key === 'faq') {
                $clinic['faq'] = array_values(array_filter($payload['items'], fn ($item) => $item['visible']));
            }
            if ($block->key === 'legal') {
                $privacyUpdated = $preview ? $revision->created_at : $block->published_at;
            }
        }
        $clinic['whatsapp'] = 'https://wa.me/'.ltrim($clinic['phone'], '+');
        foreach (['es', 'en'] as $locale) {
            $message = $locale === 'es'
                ? 'Hola, quisiera solicitar una cita con '.$clinic['physician'].'. ¿Qué horarios tienen disponibles?'
                : 'Hello, I would like to request an appointment with '.$clinic['physician'].'. What appointment times are available?';
            $clinic['appointment_url'][$locale] = $clinic['whatsapp'].'?text='.rawurlencode($message);
        }
        $clinic['phone_display'] = preg_match('/^\+52(\d{3})(\d{3})(\d{4})$/', $clinic['phone'], $parts)
            ? '+52 '.$parts[1].' '.$parts[2].' '.$parts[3] : $clinic['phone'];
        $clinic['has_privacy'] = filled($clinic['es']['privacy_content'] ?? null) && filled($clinic['en']['privacy_content'] ?? null);
        $clinic['privacy_updated_at'] = $privacyUpdated;
        $clinic['images'] = app(PublicMedia::class)->resolve($clinic, $mediaPayload, $preview);

        return ['clinic' => $clinic, 'hours' => $hours];
    }

    public function hoursForDisplay(array $hours, string $locale): array
    {
        $groups = [];
        foreach (ContentSchema::days() as $day => $names) {
            $schedule = $hours['week'][$day];
            $key = json_encode($schedule);
            $groups[$key] ??= ['days' => [], 'keys' => [], 'closed' => $schedule['closed'], 'sessions' => $schedule['sessions']];
            $groups[$key]['days'][] = $names[$locale];
            $groups[$key]['keys'][] = $day;
        }
        foreach ($groups as &$group) {
            $names = $group['days'];
            $last = array_pop($names);
            $group['label'] = $names ? implode(', ', $names).($locale === 'es' ? ' y ' : ' and ').$last : $last;
        }
        unset($group);

        return array_values($groups);
    }

    /**
     * Whether the office is open at this moment and when it opens or closes next.
     * Uses the same published hours and exceptions as the schedule, in the office time zone.
     */
    public function openingStatus(array $hours, string $locale, ?CarbonImmutable $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now('America/Matamoros'))->setTimezone('America/Matamoros');
        $today = $this->scheduleFor($hours, $now);
        $time = $now->format('H:i');
        $status = [
            'open' => false, 'date' => $now->toDateString(), 'day' => strtolower($now->englishDayOfWeek),
            'exception' => $today['exception'], 'closed_today' => $today['closed'],
            'today_hours' => $today['closed'] ? '' : implode($locale === 'es' ? ' y ' : ' and ', array_map(
                fn ($session) => $this->formatTime($session['start'], $locale).'–'.$this->formatTime($session['end'], $locale), $today['sessions'])),
            'label' => '', 'detail' => '',
        ];
        foreach ($today['sessions'] as $session) {
            if ($session['start'] <= $time && $time < $session['end']) {
                $status['open'] = true;
                $status['label'] = $locale === 'es' ? 'Abierto ahora' : 'Open now';
                $closes = $this->formatTime($session['end'], $locale);
                $status['detail'] = $this->sentence($locale === 'es' ? 'Cierra a las '.$closes : 'Closes at '.$closes);
                if ($time < $session['last']) {
                    $last = $this->formatTime($session['last'], $locale);
                    $status['detail'] .= ' '.$this->sentence($locale === 'es' ? 'Última cita: '.$last : 'Last appointment: '.$last);
                }

                return $status;
            }
        }
        $status['label'] = $locale === 'es' ? 'Cerrado ahora' : 'Closed now';
        foreach ($today['sessions'] as $session) {
            if ($session['start'] > $time) {
                $opens = $this->formatTime($session['start'], $locale);
                $status['detail'] = $this->sentence($locale === 'es' ? 'Abre hoy a las '.$opens : 'Opens today at '.$opens);

                return $status;
            }
        }
        for ($ahead = 1; $ahead <= 14; $ahead++) {
            $date = $now->addDays($ahead);
            $schedule = $this->scheduleFor($hours, $date);
            if ($schedule['closed'] || ! $schedule['sessions']) {
                continue;
            }
            $opens = $this->formatTime($schedule['sessions'][0]['start'], $locale);
            $when = match (true) {
                $ahead === 1 => $locale === 'es' ? 'mañana' : 'tomorrow',
                $ahead < 7 => ($locale === 'es' ? 'el ' : 'on ').$date->locale($locale)->isoFormat('dddd'),
                default => ($locale === 'es' ? 'el ' : 'on ').$date->locale($locale)->isoFormat('D MMM'),
            };
            $status['detail'] = $this->sentence($locale === 'es' ? 'Abre '.$when.' a las '.$opens : 'Opens '.$when.' at '.$opens);

            return $status;
        }

        return $status;
    }

    private function sentence(string $text): string
    {
        return str_ends_with($text, '.') ? $text : $text.'.';
    }

    private function scheduleFor(array $hours, CarbonImmutable $date): array
    {
        foreach ($hours['exceptions'] ?? [] as $exception) {
            if ($exception['date'] === $date->toDateString()) {
                return ['closed' => (bool) $exception['closed'], 'sessions' => $exception['closed'] ? [] : $exception['sessions'], 'exception' => true];
            }
        }
        $schedule = $hours['week'][strtolower($date->englishDayOfWeek)] ?? ['closed' => true, 'sessions' => []];

        return ['closed' => (bool) $schedule['closed'], 'sessions' => $schedule['closed'] ? [] : $schedule['sessions'], 'exception' => false];
    }

    public function formatTime(string $time, string $locale): string
    {
        if ($locale === 'es') {
            return $time;
        }
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour % 12 ?: 12).':'.str_pad((string) $minute, 2, '0', STR_PAD_LEFT).($hour < 12 ? ' a.m.' : ' p.m.');
    }
}
