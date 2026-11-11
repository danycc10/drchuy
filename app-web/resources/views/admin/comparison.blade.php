<x-admin-layout title="Comparar antes de publicar">
    @php
        $isService = $target instanceof \App\Models\Service;
        $kind = $isService ? 'services' : 'content';
        $label = $isService ? $revision->payload['es']['name'] : $target->label();
    @endphp
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.'.$kind.'.edit', $target) }}">Volver a editar</a><h1>Comparar antes de publicar</h1><p>{{ $label }} · propuesta #{{ $revision->id }}</p></div></div>
    <p class="notice">Esta comparación incluye solo cambios guardados. {{ $base ? 'La base es la versión publicada #'.$base->id.'.' : ($isService ? 'Este servicio aún no tiene una versión publicada.' : 'El sitio puede seguir mostrando el contenido original de este bloque.') }}</p>
    <div class="editor-fields">
        @if($isService)@include('admin.services.comparison-cards')@endif
        <section class="panel"><h2>Qué cambiará</h2><p class="field-help">{{ count($changes) }} campos con diferencias.</p>@include('admin.partials.revision-changes')</section>
        <section class="panel"><h2>Continuar</h2><div class="dashboard-actions"><a class="button secondary" href="{{ route('admin.'.$kind.'.edit', $target) }}">Seguir editando</a>
            <a class="button secondary" target="_blank" rel="noopener noreferrer" href="{{ route('admin.preview.screen', ['service_id' => $isService ? $target->id : null, 'page' => !$isService && $target->key === 'legal' ? 'privacy' : 'home']) }}">Vista previa</a>
            @can('publish', $target)<form method="post" action="{{ route('admin.'.$kind.'.publish', $target) }}" data-confirm="¿Publicar la versión que acabas de comparar?" data-confirm-detail="{{ $label }} · Versión #{{ $revision->id }}">@csrf<input type="hidden" name="revision_id" value="{{ $revision->id }}"><button class="button primary" @disabled(!$target->hasDraft())>Publicar esta versión</button></form>@else<p class="field-help">Vuelve al editor para enviar el borrador a revisión.</p>@endcan
        </div></section>
    </div>
</x-admin-layout>
