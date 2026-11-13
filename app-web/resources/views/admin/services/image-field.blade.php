@php
    $selectedId = old('image_id', $service->currentRevision->image_id);
    $selectedMedia = $mediaOptions->firstWhere('id', $selectedId);
    $defaultImage = asset('images/services/'.$service->category->slug.'-640.webp');
    $positionX = (int) old('image_position.x', $payload['image_position']['x'] ?? 50);
    $positionY = (int) old('image_position.y', $payload['image_position']['y'] ?? 50);
@endphp
<section class="panel service-image" data-service-image data-upload-url="{{ route('admin.media.store') }}">
    <div class="section-heading"><h2>Imagen del servicio</h2><a class="small-link" href="{{ route('admin.media.index') }}"><x-site-icon name="image" /> Abrir biblioteca</a></div>
    <p class="field-help">Elige una de tus fotos, sube una nueva o conserva la ilustración de la categoría. Así se verá en la tarjeta del sitio.</p>
    @error('image_id')<p class="field-error">{{ $message }}</p>@enderror
    <div class="service-image-layout">
        <div class="service-image-current">
            <div class="service-crop-preview"><img data-crop-preview src="{{ $selectedMedia ? $selectedMedia->url(true) : $defaultImage }}" alt="Vista de la tarjeta con la imagen seleccionada" style="object-position: {{ $positionX }}% {{ $positionY }}%"></div>
            <p class="service-image-caption">Imagen actual: <strong data-image-selection role="status" aria-live="polite">{{ $selectedMedia?->title ?? 'Ilustración de la categoría' }}</strong></p>
            <fieldset class="crop-controls" data-crop-controls @unless($selectedMedia) hidden @endunless><legend>Encuadre de la tarjeta</legend>
                @foreach(['x' => 'Mover a la izquierda o derecha', 'y' => 'Mover arriba o abajo'] as $axis => $label)
                    <label>{{ $label }}<input type="range" name="image_position[{{ $axis }}]" data-crop-axis="{{ $axis }}" min="0" max="100" step="1" value="{{ $axis === 'x' ? $positionX : $positionY }}"></label>
                @endforeach
                <p class="field-help">Mueve los controles hasta que el motivo quede visible en la tarjeta. La foto original no cambia.</p>
            </fieldset>
        </div>
        <div class="service-image-choices">
            <div data-image-picker hidden>
                <h3>Elige una imagen</h3>
                <label for="service-image-search" data-image-search-field hidden>Buscar en tus imágenes<input id="service-image-search" type="search" maxlength="150" autocomplete="off" placeholder="Nombre de la imagen" data-image-search></label>
                <div class="image-picker-grid" data-image-grid role="group" aria-label="Imágenes para este servicio"></div>
                <p class="field-help" data-image-empty hidden>No encontramos imágenes con ese nombre. Prueba otro término o sube una nueva.</p>
                <p class="field-help" data-image-count role="status" aria-live="polite" hidden></p>
            </div>
            <details class="media-selection" data-image-native open><summary>Elegir imagen por nombre</summary>
                <label for="service-image">Imagen de la tarjeta
                    <select id="service-image" name="image_id" data-media-select data-preview-id="service-image-preview">
                        <option value="" data-src="{{ $defaultImage }}">Ilustración de la categoría</option>
                        @foreach($mediaOptions as $media)<option value="{{ $media->id }}" data-src="{{ $media->url(true, 'thumb') }}" data-display="{{ $media->url(true) }}" @selected((string) $selectedId === (string) $media->id)>{{ $media->title }}</option>@endforeach
                    </select>
                </label>
                <img id="service-image-preview" class="media-selection-preview" src="{{ $selectedMedia ? $selectedMedia->url(true, 'thumb') : $defaultImage }}" alt="Vista de la imagen seleccionada para este servicio" width="160" height="120" loading="lazy">
            </details>
            <details class="inline-upload" data-inline-upload hidden @if($mediaOptions->isEmpty() || $errors->has('image_id')) open @endif>
                <summary><x-site-icon name="image" /> <span>Subir una foto nueva</span> <x-site-icon name="chevron-down" /></summary>
                <div class="inline-upload-fields">
                    <label for="service-upload-title">Nombre para identificarla<input id="service-upload-title" type="text" maxlength="150" data-upload-title placeholder="Ej. Equipo para espirometría"></label>
                    <label for="service-upload-file">Archivo JPG, PNG o WebP (hasta 8 MB)<input id="service-upload-file" type="file" accept="image/jpeg,image/png,image/webp" data-upload-file></label>
                    <button type="button" class="button secondary" data-inline-upload-button><x-site-icon name="image" /> <span data-inline-upload-label>Subir y usar esta foto</span></button>
                    <progress class="upload-progress" data-upload-progress max="100" aria-label="Progreso de carga" hidden></progress>
                    <p class="field-help" data-upload-status role="status" aria-live="polite" tabindex="-1"></p>
                    <p class="field-help">La foto se guarda en tu biblioteca y queda seleccionada aquí. Después pulsa Guardar y publicar.</p>
                </div>
            </details>
            <noscript><p class="field-help">Para subir una imagen, abre la biblioteca. Después vuelve a seleccionar la imagen aquí.</p></noscript>
        </div>
    </div>
</section>
