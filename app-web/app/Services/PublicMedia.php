<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Support\MediaSlots;

class PublicMedia
{
    public function resolve(array $clinic, ?array $payload, bool $preview): array
    {
        $assets = MediaAsset::whereIn('id', array_filter($payload['shared'] ?? []))->get()->keyBy('id');
        $images = [];
        foreach (MediaSlots::definitions() as $slot => $details) {
            $asset = $assets->get($payload['shared'][$slot.'_image_id'] ?? null);
            $images[$slot] = [
                'url' => $asset ? $asset->url($preview) : asset('images/'.$details['file']),
                'thumb' => $asset ? $asset->url($preview, 'thumb') : asset('images/'.$details['file']),
                'width' => $asset?->display_width ?? $details['width'],
                'height' => $asset?->display_height ?? $details['height'],
            ];
            foreach (['es', 'en'] as $locale) {
                $images[$slot]['alt'][$locale] = $payload[$locale][$slot.'_alt'] ?? data_get($clinic[$locale], $details['alt']);
            }
        }

        return $images;
    }
}
