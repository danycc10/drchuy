<nav class="dashboard-shortcuts" aria-label="Accesos rápidos">
    <a href="{{ route('admin.services.index') }}"><span class="module-icon"><x-site-icon name="edit" /></span><strong>Editar servicios</strong><span>Nombres, descripciones e imágenes</span></a>
    <a href="{{ route('admin.services.order') }}"><span class="module-icon"><x-site-icon name="list" /></span><strong>Ordenar catálogo</strong><span>Organiza las tarjetas por categoría</span></a>
    <a href="{{ route('admin.media.index') }}"><span class="module-icon"><x-site-icon name="image" /></span><strong>Administrar imágenes</strong><span>Sube y encuentra tus fotografías</span></a>
    <a href="{{ route('admin.content.index') }}"><span class="module-icon"><x-site-icon name="document" /></span><strong>Horarios y contacto</strong><span>Datos del consultorio y avisos</span></a>
    <a href="{{ route('admin.preview.screen') }}"><span class="module-icon"><x-site-icon name="eye" /></span><strong>Ver el sitio</strong><span>Vista de celular, tablet y escritorio</span></a>
</nav>
