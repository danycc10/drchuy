@props(['target', 'form'])
@php
    $isService = $target instanceof \App\Models\Service;
    $kind = $isService ? 'services' : 'content';
    $key = $isService ? null : $target->key;
    $payload = $target->currentRevision->payload;
    $label = $isService ? $payload['es']['name'] : $target->label();
    $legalIncomplete = $key === 'legal' && (!filled($payload['es']['privacy_content'] ?? '') || !filled($payload['en']['privacy_content'] ?? ''));
    $previewUrl = route('admin.preview.screen', ['page' => $key === 'legal' ? 'privacy' : 'home', 'section' => $isService ? 'services' : ($key === 'faq' ? 'faq' : null), 'service_id' => $isService ? $target->id : null]);
@endphp
<div class="editor-toolbar" aria-label="Acciones de edición">
    <div>
        <p class="save-state" data-save-state role="status" aria-live="polite">Todos los cambios guardados</p>
        <p class="field-help" id="save-help">Al guardar, el sitio se actualiza al instante en español e inglés.@if($target->published_at) Última actualización: {{ $target->published_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}.@endif</p>
    </div>
    <div class="editor-toolbar-actions">
        @unless($legalIncomplete)<a class="small-link" href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> Ver cómo se ve</a>@endunless
        <button class="button primary" type="submit" form="{{ $form }}" data-save-button aria-describedby="save-help"><x-site-icon name="save" /><span data-save-label>Guardar y publicar</span></button>
    </div>
</div>
@if($target->hasDraft())
    <div class="notice pending-notice" id="pending-version">
        @if($legalIncomplete)
            <p>El aviso todavía no se muestra en el sitio. Pega el texto en español e inglés y pulsa <strong>Guardar y publicar</strong>.</p>
        @else
            <p>Hay una versión guardada (#{{ $target->current_revision_id }}) que todavía no se muestra en el sitio. Publícala tal cual o modifica lo que necesites y guarda.</p>
            <form method="post" action="{{ route('admin.'.$kind.'.publish', $target) }}" data-confirm="¿Publicar esta versión? El sitio la mostrará de inmediato en ambos idiomas." data-confirm-detail="{{ $label }} · Versión #{{ $target->current_revision_id }}">@csrf<input type="hidden" name="revision_id" value="{{ $target->current_revision_id }}"><button class="button secondary" data-publish-button><x-site-icon name="publish" /> Publicar ahora</button></form>
        @endif
    </div>
@endif
