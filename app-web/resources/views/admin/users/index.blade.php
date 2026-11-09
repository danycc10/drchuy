<x-admin-layout title="Usuarios">
    <div class="page-heading"><div><span class="eyebrow">EQUIPO DEL CONSULTORIO</span><h1>Usuarios</h1><p>Controla quién puede acceder, preparar contenido y publicar.</p></div><a class="button primary" href="{{ route('admin.users.create') }}"><x-site-icon name="user" /> Crear cuenta</a></div>
    <section class="panel">
        <form method="get" class="filters"><label>Buscar por nombre o correo<input type="search" name="q" value="{{ request('q') }}" maxlength="100"></label><button class="button primary"><x-site-icon name="search" /> Buscar</button><a class="small-link" href="{{ route('admin.users.index') }}">Limpiar</a></form>
        <div class="table-wrap"><table><thead><tr><th>Cuenta</th><th>Rol</th><th>Acceso</th><th>Dos pasos</th><th><span class="sr-only">Acciones</span></th></tr></thead><tbody>
            @forelse($users as $account)
                <tr><td><strong>{{ $account->name }}</strong><small>{{ $account->email }}</small>@if($account->id === auth()->id())<small>Tu cuenta</small>@endif</td><td>{{ $account->role->label() }}</td><td><span class="badge {{ $account->is_active ? 'published' : 'draft' }}">{{ $account->is_active ? 'Activo' : 'Desactivado' }}</span></td><td>{{ $account->two_factor_confirmed_at ? 'Activado' : 'Sin activar' }}</td><td><a class="small-link" href="{{ route('admin.users.edit', $account) }}" aria-label="Administrar cuenta de {{ $account->name }}"><x-site-icon name="edit" /> Administrar</a></td></tr>
            @empty<tr><td colspan="5"><p class="empty-state">No encontramos cuentas con esa búsqueda.</p></td></tr>@endforelse
        </tbody></table></div><div class="pagination">{{ $users->links() }}</div>
    </section>
</x-admin-layout>
