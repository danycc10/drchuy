<x-admin-layout title="Vista previa adaptable">
    <div class="page-heading"><div><h1>Así se ve tu sitio</h1><p>Vista privada con la última versión guardada, en celular, tablet y escritorio.</p></div><a class="button secondary" href="{{ $url }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> Abrir vista completa</a></div>
    <section class="panel preview-controls" data-device-preview>
        <form method="get" class="filters"><label>Idioma<select name="locale"><option value="es" @selected($locale === 'es')>Español</option><option value="en" @selected($locale === 'en')>Inglés</option></select></label><label>Página<select name="page"><option value="home" @selected($page === 'home')>Inicio</option><option value="privacy" @selected($page === 'privacy')>Aviso de privacidad</option></select></label>@if($serviceId)<input type="hidden" name="service_id" value="{{ $serviceId }}">@endif<input type="hidden" name="section" value="{{ $section }}"><button class="button secondary">Cargar vista</button></form>
        <div class="device-buttons" role="group" aria-label="Ancho de la vista previa" data-device-buttons hidden>
            <button type="button" class="button secondary" data-preview-width="390" aria-pressed="true">Celular · 390 px</button>
            <button type="button" class="button secondary" data-preview-width="768" aria-pressed="false">Tablet · 768 px</button>
            <button type="button" class="button secondary" data-preview-width="1280" aria-pressed="false">Escritorio · 1280 px</button>
            <button type="button" class="button secondary" data-preview-refresh>Actualizar vista</button>
        </div>
        <p class="field-help">Esta vista reproduce el ancho de pantalla. Revisa también en un teléfono real antes de lanzar el sitio. Si no aparece el aviso de privacidad, completa y guarda su contenido en ambos idiomas.</p>
        <p class="field-help" data-preview-status role="status" aria-live="polite">Vista de 390 px.</p>
        <div class="device-stage"><iframe class="device-frame" data-preview-frame src="{{ $url }}" title="Vista previa privada del sitio" width="390" height="760" referrerpolicy="same-origin"></iframe></div>
    </section>
</x-admin-layout>
