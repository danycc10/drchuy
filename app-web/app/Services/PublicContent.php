<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Support\ContentSchema;

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
            $groups[$key] ??= ['days' => [], 'closed' => $schedule['closed'], 'sessions' => $schedule['sessions']];
            $groups[$key]['days'][] = $names[$locale];
        }
        foreach ($groups as &$group) {
            $names = $group['days'];
            $last = array_pop($names);
            $group['label'] = $names ? implode(', ', $names).($locale === 'es' ? ' y ' : ' and ').$last : $last;
        }
        unset($group);

        return array_values($groups);
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
