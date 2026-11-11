@forelse($changes as $change)
    <article class="review-change"><h3>{{ \App\Support\ReviewFields::label($target, $change['field']) }}</h3><div class="review-comparison">
        @foreach(['before' => 'ANTERIOR', 'after' => 'PROPUESTO'] as $side => $label)<div><span class="eyebrow">{{ $label }}</span><p class="review-text">{{ $change[$side] }}</p>@if($change[$side.'_image'])<img class="comparison-photo" src="{{ $change[$side.'_image'] }}" alt="{{ $label }}: {{ $change[$side] }}" loading="lazy">@endif</div>@endforeach
    </div></article>
@empty<p class="field-help">No hay diferencias en los valores respecto a la versión base.</p>@endforelse
