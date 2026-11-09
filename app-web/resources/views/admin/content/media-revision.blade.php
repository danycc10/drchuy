@php
    $media = $mediaOptions->get($revision->payload[$group][$field] ?? null);
    $slot = \App\Support\MediaSlots::definitions()[$definition['slot']];
@endphp
{{ $media?->title ?? 'Fotografía original del sitio' }}
<img class="media-selection-preview" src="{{ $media ? $media->url(true, 'thumb') : asset('images/'.$slot['file']) }}" alt="{{ $revision->payload['es'][$definition['slot'].'_alt'] ?? $definition['label'] }}" width="160" height="120" loading="lazy">
