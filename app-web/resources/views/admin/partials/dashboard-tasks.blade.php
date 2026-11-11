<section class="panel dashboard-tasks" aria-labelledby="tasks-heading">
    <div class="section-heading"><div><span class="eyebrow">QUÉ HACER AHORA</span><h2 id="tasks-heading">Tus próximos pasos</h2></div><a class="small-link" href="{{ route('admin.reviews.index') }}"><x-site-icon name="document" /> Todas las revisiones</a></div>
    @foreach($tasks['groups'] as $group)
        @if($group['items']->isNotEmpty())
            <div class="task-group"><h3>{{ $group['title'] }} <span class="badge">{{ $group['items']->count() }}</span></h3><p class="muted small">{{ $group['description'] }}</p>
                <ul class="task-list">@foreach($group['items']->take(3) as $task)@include('admin.partials.dashboard-task-row')@endforeach</ul>
                @if($group['items']->count() > 3)<details class="more-tasks"><summary>Ver {{ $group['items']->count() - 3 }} pendientes más</summary><ul class="task-list">@foreach($group['items']->skip(3) as $task)@include('admin.partials.dashboard-task-row')@endforeach</ul></details>@endif
            </div>
        @endif
    @endforeach
    @if(collect($tasks['groups'])->every(fn ($group) => $group['items']->isEmpty()))<p class="empty-state">No tienes correcciones, solicitudes para resolver ni borradores por preparar.</p>@endif
    @if($tasks['waiting'])<p class="notice task-waiting">{{ $tasks['waiting'] }} solicitudes esperan a una cuenta con permisos de publicación. Puedes consultar su estado en Revisiones.</p>@endif
</section>
