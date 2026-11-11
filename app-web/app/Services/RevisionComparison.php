<?php

namespace App\Services;

use App\Models\{ContentBlock, ContentRevision, MediaAsset, Service, ServiceRevision};
use App\Support\MediaSlots;
use Illuminate\Support\Arr;

class RevisionComparison
{
    public function changes(Service|ContentBlock $target, ServiceRevision|ContentRevision|null $base, ServiceRevision|ContentRevision $revision): array
    {
        $before = Arr::dot($base?->payload ?? []);
        $after = Arr::dot($revision->payload);
        if ($target instanceof Service) {
            $before['image_id'] = $base?->image_id;
            $after['image_id'] = $revision->image_id;
        }
        $changes = [];
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changes[] = ['field' => $field,
                    'before' => $this->display($before[$field] ?? null, $field), 'after' => $this->display($after[$field] ?? null, $field),
                    'before_image' => $this->image($target, $before[$field] ?? null, $field), 'after_image' => $this->image($target, $after[$field] ?? null, $field)];
            }
        }
        return $changes;
    }

    private function display(mixed $value, string $field): string
    {
        if ($field === 'image_id' || str_ends_with($field, '_image_id')) {
            return $value ? (MediaAsset::find($value)?->title ?? 'Imagen no disponible') : ($field === 'image_id' ? 'Ilustración original de la categoría' : 'Fotografía original del sitio');
        }
        return match (true) { $value === null => '—', is_bool($value) => $value ? 'Sí' : 'No', is_array($value) => 'Sin elementos', default => (string) $value };
    }

    private function image(Service|ContentBlock $target, mixed $value, string $field): ?string
    {
        if ($target instanceof Service && $field === 'image_id') {
            return $value ? MediaAsset::find($value)?->url(true) : asset('images/services/'.$target->category->slug.'-640.webp');
        }
        if ($target instanceof ContentBlock && $target->key === 'media' && preg_match('/^shared\.(.+)_image_id$/', $field, $matches)) {
            $slot = MediaSlots::definitions()[$matches[1]] ?? null;
            return $value ? MediaAsset::find($value)?->url(true) : ($slot ? asset('images/'.$slot['file']) : null);
        }
        return null;
    }
}
