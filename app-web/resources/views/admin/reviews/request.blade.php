@php
    $reviewType = $target instanceof \App\Models\Service ? 'service' : 'content';
    $latestRequest = \App\Models\ReviewRequest::where(\App\Services\EditorialReview::targetKey($target), $target->id)->where('revision_id', $target->current_revision_id)->first();
@endphp
<section class="panel"><h2>Solicitar revisión</h2>
    @if($latestRequest)
        <p class="field-help">{{ \App\Models\ReviewRequest::statuses()[$latestRequest->status] }} · versión #{{ $latestRequest->revision_id }}</p><a class="small-link" href="{{ route('admin.reviews.show', $latestRequest) }}"><x-site-icon name="document" /> Abrir solicitud y comentarios</a>
    @else
        <p class="muted small">Envía la versión guardada a la bandeja del equipo antes de publicarla.</p>
        <form method="post" action="{{ route('admin.reviews.store') }}" class="stack-form" data-confirm="¿Solicitar revisión de la última versión guardada?">@csrf
            <input type="hidden" name="type" value="{{ $reviewType }}"><input type="hidden" name="target_id" value="{{ $target->id }}"><input type="hidden" name="revision_id" value="{{ $target->current_revision_id }}">
            <label>Comentario para quien revisa (opcional)<textarea name="note" rows="3" maxlength="1500">{{ old('note') }}</textarea></label>
            <button class="button secondary" data-publish-button @disabled(!$target->hasDraft())><x-site-icon name="document" /> Enviar a revisión</button>
        </form>
    @endif
</section>
