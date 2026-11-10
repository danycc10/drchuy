@php
    $reviewType = $target instanceof \App\Models\Service ? 'service' : 'content';
    $latestRequest = \App\Models\ReviewRequest::where(\App\Services\EditorialReview::targetKey($target), $target->id)->where('revision_id', $target->current_revision_id)->first();
@endphp
<div class="workflow-review">
    @if(!$target->hasDraft())
        <p class="field-help">Esta versión ya está publicada. Guarda nuevos cambios para solicitar otra revisión.</p>
    @elseif($latestRequest)
        <p class="field-help">{{ \App\Models\ReviewRequest::statuses()[$latestRequest->status] }} · versión #{{ $latestRequest->revision_id }}</p><a class="small-link" href="{{ route('admin.reviews.show', $latestRequest) }}"><x-site-icon name="document" /> Abrir solicitud y comentarios</a>
        @if($latestRequest->status === 'changes_requested')<p class="field-help">Corrige las observaciones y guarda un nuevo borrador para volver a enviarlo.</p>@endif
    @else
        <p class="muted small">Envía la versión guardada a la bandeja del equipo antes de publicarla.</p>
        <form method="post" action="{{ route('admin.reviews.store') }}" class="stack-form" data-confirm="¿Solicitar revisión de la última versión guardada?" data-confirm-detail="{{ $target instanceof \App\Models\Service ? $target->currentRevision->payload['es']['name'] : $target->label() }} · Versión #{{ $target->current_revision_id }}">@csrf
            <input type="hidden" name="type" value="{{ $reviewType }}"><input type="hidden" name="target_id" value="{{ $target->id }}"><input type="hidden" name="revision_id" value="{{ $target->current_revision_id }}">
            <details @if(old('note') || $errors->has('note')) open @endif><summary>Agregar comentario (opcional)</summary><label>Comentario para quien revisa<textarea name="note" rows="3" maxlength="1500">{{ old('note') }}</textarea></label></details>
            <button class="button secondary" data-publish-button aria-describedby="publish-help" @disabled(!$target->hasDraft())><x-site-icon name="document" /> Enviar a revisión</button>
        </form>
    @endif
</div>
