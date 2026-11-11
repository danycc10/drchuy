@props(['target', 'form'])
@php
    $isService = $target instanceof \App\Models\Service;
    $kind = $isService ? 'services' : 'content';
    $key = $isService ? null : $target->key;
    $payload = $target->currentRevision->payload;
    $label = $isService ? $payload['es']['name'] : $target->label();
    $legalIncomplete = $key === 'legal' && (!filled($payload['es']['privacy_content'] ?? '') || !filled($payload['en']['privacy_content'] ?? ''));
@endphp
<x-editor-toolbar :form="$form" />
<details class="panel editor-workflow" id="editor-workflow" @if(old('note') || $errors->has('note')) open @endif>
    <summary><span><strong>Revisar y publicar</strong><span class="field-help">Versión guardada #{{ $target->current_revision_id }} · {{ $target->hasDraft() ? 'Borrador pendiente de publicación' : 'Coincide con la versión pública' }}</span></span><x-site-icon name="chevron-down" /></summary>
    <p data-dirty-note class="dirty-note" id="publish-help" hidden>Guarda tus cambios antes de revisarlos o publicarlos.</p>
    <div class="workflow-columns">
        <div><h3>1. Comprueba el resultado</h3><p class="field-help">La vista previa abre la última versión guardada en una pestaña nueva. Revisa ambos idiomas.</p>
            <a class="button secondary" style="margin:12px 0" href="{{ route('admin.'.$kind.'.compare', $target) }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="document" /> Comparar antes de publicar</a>
            @if($legalIncomplete)
                <p class="field-help">Guarda el aviso en español e inglés para habilitar su vista previa.</p>
            @else
                <div class="preview-links" aria-label="Vista previa">
                    @foreach(['es' => 'Español', 'en' => 'Inglés'] as $locale => $language)
                        <a class="small-link" href="{{ route($key === 'legal' ? 'admin.preview.privacy' : 'admin.preview', $isService ? ['locale' => $locale, 'service_id' => $target->id] : $locale).($isService ? '#service-'.$target->id : ($key === 'faq' ? '#faq' : '')) }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> {{ $language }}</a>
                    @endforeach
                </div>
                <a class="button secondary" style="margin-top:16px" href="{{ route('admin.preview.screen', ['page' => $key === 'legal' ? 'privacy' : 'home', 'section' => $isService ? 'services' : ($key === 'faq' ? 'faq' : null), 'service_id' => $isService ? $target->id : null]) }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> Revisar en celular y escritorio</a>
            @endif
        </div>
        <div><h3>2. Revisa con el equipo</h3>@include('admin.reviews.request', ['target' => $target])</div>
        <div><h3>3. Actualiza el sitio</h3>
            @can('publish', $target)
                <p class="field-help">Publicar aplica la versión guardada en ambos idiomas. Puedes hacerlo con los permisos de tu cuenta.</p>
                <form method="post" action="{{ route('admin.'.$kind.'.publish', $target) }}" data-confirm="¿Publicar la última versión guardada? El sitio mostrará estos cambios en ambos idiomas." data-confirm-detail="{{ $label }} · Versión #{{ $target->current_revision_id }}">@csrf<input type="hidden" name="revision_id" value="{{ $target->current_revision_id }}"><button data-publish-button aria-describedby="publish-help" class="button secondary" @disabled(!$target->hasDraft())><x-site-icon name="publish" /> Publicar versión guardada</button></form>
            @else
                <p class="field-help">Envía el borrador a revisión. {{ ($isService ? $target->category->slug === 'procedures' : (\App\Support\ContentSchema::definitions()[$key]['owner_only'] ?? false)) ? 'Solo el propietario puede publicar este contenido.' : 'Un administrador o el propietario debe publicar tus cambios.' }}</p>
            @endcan
            @if($target->published_at)<p class="field-help">Última publicación: {{ $target->published_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}.</p>@endif
        </div>
    </div>
</details>
