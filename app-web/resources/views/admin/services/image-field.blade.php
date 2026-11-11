@php
    $selectedId = old('image_id', $service->currentRevision->image_id);
    $selectedMedia = $mediaOptions->firstWhere('id', $selectedId);
    $defaultImage = asset('images/services/'.$service->category->slug.'-640.webp');
@endphp
<section class="panel" data-service-image data-upload-url="{{ route('admin.media.store') }}">
    <div class="section-heading"><h2>Imagen del servicio</h2><a class="small-link" href="{{ route('admin.media.index') }}"><x-site-icon name="image" /> Abrir biblioteca</a></div>
    <p class="field-help">Elige una imagen de la biblioteca o sube una nueva aquí. La selección y el encuadre se guardan con este servicio.</p>
    <div data-image-picker hidden>
        <label for="service-image-search">Buscar en tus imágenes<input id="service-image-search" type="search" maxlength="150" autocomplete="off" placeholder="Nombre de la imagen" data-image-search></label>
        <p class="field-help" data-image-selection role="status" aria-live="polite"></p>
        <div class="image-picker-grid" data-image-grid role="group" aria-label="Imágenes para este servicio"></div>
        <p class="field-help" data-image-empty hidden>No encontramos imágenes con ese nombre. Prueba otro término o sube una nueva.</p>
        <p class="field-help" data-image-count role="status" aria-live="polite"></p>
    </div>
    <details class="media-selection" data-image-native open><summary>Elegir imagen por nombre</summary>
        <label for="service-image">Imagen de la tarjeta
            <select id="service-image" name="image_id" data-media-select data-preview-id="service-image-preview">
                <option value="" data-src="{{ $defaultImage }}">Ilustración original de la categoría</option>
                @foreach($mediaOptions as $media)<option value="{{ $media->id }}" data-src="{{ $media->url(true, 'thumb') }}" data-display="{{ $media->url(true) }}" @selected((string) $selectedId === (string) $media->id)>{{ $media->title }}</option>@endforeach
            </select>
        </label>
        <img id="service-image-preview" class="media-selection-preview" src="{{ $selectedMedia ? $selectedMedia->url(true, 'thumb') : $defaultImage }}" alt="Vista de la imagen seleccionada para este servicio" width="160" height="120" loading="lazy">
    </details>
    @error('image_id')<p class="field-error">{{ $message }}</p>@enderror
    <div data-inline-upload hidden>
        <label for="service-upload-title">Nombre de la nueva imagen<input id="service-upload-title" type="text" maxlength="150" data-upload-title placeholder="Ej. Equipo para espirometría"></label>
        <label for="service-upload-file">Archivo JPG, PNG o WebP<input id="service-upload-file" type="file" accept="image/jpeg,image/png,image/webp" data-upload-file></label>
        <p class="field-help">Hasta 8 MB. Subir guarda la imagen en la biblioteca; todavía debes guardar el borrador para asignarla al servicio.</p>
        <button type="button" class="button secondary" data-inline-upload-button><x-site-icon name="image" /> Subir y seleccionar</button>
        <p class="field-help" data-upload-status role="status" aria-live="polite" tabindex="-1"></p>
    </div>
    <noscript><p class="field-help">Para subir una imagen, abre la biblioteca. Después vuelve a seleccionar la imagen aquí.</p></noscript>
    <div class="service-crop-preview"><img data-crop-preview src="{{ $selectedMedia ? $selectedMedia->url(true) : $defaultImage }}" alt="Vista del encuadre horizontal de la tarjeta" style="object-position: {{ (int) old('image_position.x', $payload['image_position']['x'] ?? 50) }}% {{ (int) old('image_position.y', $payload['image_position']['y'] ?? 50) }}%"></div>
    <fieldset class="crop-controls"><legend>Encuadre de la tarjeta</legend>
        @foreach(['x' => 'Posición horizontal', 'y' => 'Posición vertical'] as $axis => $label)
            <label>{{ $label }}<input type="range" name="image_position[{{ $axis }}]" data-crop-axis="{{ $axis }}" min="0" max="100" step="1" value="{{ old('image_position.'.$axis, $payload['image_position'][$axis] ?? 50) }}"></label>
        @endforeach
        <p class="field-help">Mueve el encuadre para mantener el motivo visible. El ajuste depende de las proporciones de la foto y conserva el archivo original. Solo se aplica a imágenes de la biblioteca.</p>
    </fieldset>
    <p class="field-help">Guarda el borrador y revisa la vista previa antes de publicar.</p>
</section>
