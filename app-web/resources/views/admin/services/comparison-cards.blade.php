<section class="panel"><h2>La tarjeta antes y después</h2><p class="field-help">Compara las versiones guardadas y su encuadre. Los campos modificados se destacan en la propuesta.</p>
    @foreach(['es' => 'Español', 'en' => 'Inglés'] as $locale => $language)
        <details class="comparison-language" @if($loop->first) open @endif><summary>{{ $language }}</summary><div class="review-comparison service-comparison">
            @foreach(['before' => $base, 'after' => $revision] as $side => $version)
                <article class="comparison-card"><h3>{{ $side === 'before' ? ($baseLabel ?? 'Versión pública actual') : 'Propuesta guardada' }}</h3>
                    @if($version)
                        <span class="badge">Versión #{{ $version->id }} · {{ $version->payload['visible'] ? 'Visible' : 'Oculto' }}</span>
                        <div class="service-crop-preview"><img src="{{ $version->image ? $version->image->url(true) : asset('images/services/'.$target->category->slug.'-640.webp') }}" style="object-position: {{ (int) ($version->payload['image_position']['x'] ?? 50) }}% {{ (int) ($version->payload['image_position']['y'] ?? 50) }}%" alt="Imagen de {{ $version->payload[$locale]['name'] }}" loading="lazy"></div>
                        @foreach(['name' => 'Nombre', 'description' => 'Descripción', 'preparation' => 'Preparación previa', 'duration' => 'Duración aproximada', 'bring' => 'Qué llevar'] as $field => $label)
                            @php($changed = $side === 'after' && ($base?->payload[$locale][$field] ?? null) !== ($version->payload[$locale][$field] ?? null))
                            @if(filled($version->payload[$locale][$field] ?? null) || $changed)<div class="comparison-field {{ $changed ? 'is-changed' : '' }}"><h4>{{ $label }} @if($changed)<span>Modificado</span>@endif</h4><p>{{ ($version->payload[$locale][$field] ?? null) ?: 'Sin información' }}</p></div>@endif
                        @endforeach
                    @else<p class="field-help">Este servicio todavía no tiene una versión publicada.</p>@endif
                </article>
            @endforeach
        </div></details>
    @endforeach
</section>
