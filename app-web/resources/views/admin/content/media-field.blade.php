@php
    $selectedId = old('payload.'.$group.'.'.$field, $payload[$group][$field] ?? null);
    $selectedMedia = $mediaOptions->firstWhere('id', $selectedId);
    $slot = \App\Support\MediaSlots::definitions()[$definition['slot']];
@endphp
<div class="media-selection">
    <label for="{{ $inputId }}">{{ $definition['label'] }}
        <select id="{{ $inputId }}" name="payload[{{ $group }}][{{ $field }}]" data-media-select data-preview-id="preview-{{ $inputId }}">
            <option value="" data-src="{{ asset('images/'.$slot['file']) }}">Fotografía original del sitio</option>
            @foreach($mediaOptions as $media)<option value="{{ $media->id }}" data-src="{{ $media->url(true, 'thumb') }}" @selected((string) $selectedId === (string) $media->id)>{{ $media->title }}</option>@endforeach
        </select>
    </label>
    <img id="preview-{{ $inputId }}" class="media-selection-preview" src="{{ $selectedMedia ? $selectedMedia->url(true, 'thumb') : asset('images/'.$slot['file']) }}" alt="Vista de la fotografía seleccionada para {{ $definition['label'] }}" width="160" height="120" loading="lazy">
</div>
