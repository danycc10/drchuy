@php
    $selectedId = old('image_id', $service->currentRevision->image_id);
    $selectedMedia = $mediaOptions->firstWhere('id', $selectedId);
    $defaultImage = asset('images/services/'.$service->category->slug.'-640.webp');
@endphp
<section class="panel">
    <div class="section-heading"><h2>Imagen del servicio</h2><a class="small-link" href="{{ route('admin.media.index') }}"><x-site-icon name="image" /> Abrir biblioteca</a></div>
    <p class="field-help">Sube primero la imagen en la biblioteca y después selecciónala aquí. Se usará solo en este servicio; puedes conservar la ilustración de la categoría.</p>
    <div class="media-selection">
        <label for="service-image">Imagen de la tarjeta
            <select id="service-image" name="image_id" data-media-select data-preview-id="service-image-preview">
                <option value="" data-src="{{ $defaultImage }}">Ilustración original de la categoría</option>
                @foreach($mediaOptions as $media)<option value="{{ $media->id }}" data-src="{{ $media->url(true, 'thumb') }}" @selected((string) $selectedId === (string) $media->id)>{{ $media->title }}</option>@endforeach
            </select>
        </label>
        <img id="service-image-preview" class="media-selection-preview" src="{{ $selectedMedia ? $selectedMedia->url(true, 'thumb') : $defaultImage }}" alt="Vista de la imagen seleccionada para este servicio" width="160" height="120" loading="lazy">
    </div>
    @error('image_id')<p class="field-error">{{ $message }}</p>@enderror
    <p class="field-help">La tarjeta adapta la imagen a un recorte horizontal. Guarda el borrador y revisa la vista previa antes de publicar.</p>
</section>
