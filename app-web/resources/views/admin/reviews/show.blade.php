<x-admin-layout title="Revisar solicitud">
    @php($kind = $target instanceof \App\Models\Service ? 'services' : 'content')
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.reviews.index') }}"><x-site-icon name="document" /> Revisiones</a><h1>{{ $review->label() }}</h1><p>Versión #{{ $review->revision_id }} · enviada por {{ $review->requester?->name ?? 'Cuenta eliminada' }}</p></div><span class="badge {{ $review->status === 'published' ? 'published' : 'draft' }}">{{ \App\Models\ReviewRequest::statuses()[$review->status] }}</span></div>
    <div class="editor-fields">
        <section class="panel"><h2>Solicitud y respuesta</h2><p class="review-text">{{ $review->note ?: 'Sin comentario adicional.' }}</p>@if($review->response)<h3>Respuesta de {{ $review->reviewer?->name ?? 'quien revisó' }}</h3><p class="review-text">{{ $review->response }}</p>@endif
            <div class="dashboard-actions"><a class="small-link" href="{{ route('admin.'.$kind.'.revision', [$target, $revision]) }}"><x-site-icon name="history" /> Ver versión solicitada</a><a class="small-link" href="{{ route('admin.'.$kind.'.edit', $target) }}"><x-site-icon name="edit" /> Editar borrador actual</a>
            @if($target->current_revision_id === $review->revision_id)
                @foreach(['es' => 'Español', 'en' => 'Inglés'] as $locale => $label)
                    <a class="small-link" target="_blank" rel="noopener noreferrer" href="{{ route('admin.preview', $locale) }}"><x-site-icon name="eye" /> Vista previa · {{ $label }}</a>
                @endforeach
            @endif
            </div>
        </section>
        <section class="panel"><h2>Cambios enviados</h2><p class="muted small">{{ $base ? 'Comparación con la versión publicada #'.$base->id.' al enviar la solicitud.' : 'Primera versión enviada: este contenido todavía no tenía una versión editorial publicada.' }}</p>
            @forelse($changes as $change)<article class="review-change"><h3>{{ \App\Support\ReviewFields::label($target, $change['field']) }}</h3><div class="review-comparison"><div><span class="eyebrow">ANTERIOR</span><p class="review-text">{{ $change['before'] }}</p></div><div><span class="eyebrow">PROPUESTO</span><p class="review-text">{{ $change['after'] }}</p></div></div></article>@empty<p class="field-help">No hay diferencias en los valores respecto a la versión base.</p>@endforelse
        </section>
        @if($review->status === 'pending' && $target->current_revision_id === $review->revision_id)
            @can('publish', $target)<form class="panel stack-form" method="post" action="{{ route('admin.reviews.decide', $review) }}" data-confirm-detail="{{ $review->label() }} · Versión #{{ $review->revision_id }}" data-confirm="¿Registrar esta decisión sobre la versión enviada?">@csrf<h2>Resolver revisión</h2><label>Comentario<textarea name="response" rows="4" maxlength="1500">{{ old('response') }}</textarea></label><p class="field-help">Explica qué debe corregirse si solicitas cambios. Publicar aplica esta versión al sitio en ambos idiomas.</p><div class="dashboard-actions"><button class="button secondary" name="decision" value="changes" data-confirm="Se devolverá esta versión al equipo con tus comentarios. El sitio público se conserva."><x-site-icon name="edit" /> Solicitar cambios</button><button class="button primary" name="decision" value="publish" data-confirm="Esta versión se publicará en el sitio en ambos idiomas."><x-site-icon name="publish" /> Aprobar y publicar</button></div></form>@else<div class="notice">La decisión corresponde a una cuenta con permiso para publicar este contenido.</div>@endcan
        @elseif($target->current_revision_id !== $review->revision_id)<div class="notice">Existe un borrador más reciente. Esta solicitud conserva la versión enviada y no permite publicar cambios posteriores.</div>@endif
    </div>
</x-admin-layout>
