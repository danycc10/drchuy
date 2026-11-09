@forelse($events as $event)
<div class="activity-row"><span class="activity-icon"><x-site-icon name="history" /></span><div><strong>{{ $event->label() }}</strong><p>{{ $event->subjectLabel() }} · {{ $event->user?->name ?? 'Sistema' }}</p></div><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}</time></div>
@empty<p class="empty-state">Los cambios de tu equipo aparecerán aquí.</p>@endforelse
