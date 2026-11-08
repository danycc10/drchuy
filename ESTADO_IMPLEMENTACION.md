# Estado de implementación

Fecha: 8 de noviembre de 2026. Segunda entrega funcional local con mejoras de UX del panel y del sitio público. El plan completo sigue en desarrollo.

## Implementado

- Repositorio Git con commits separados para documentación, base técnica, acceso, edición y sitio.
- Laravel 13.40.0 sobre PHP 8.3.28; MySQL local 8.4.3, base exclusiva `drchuy_web`.
- Catálogo de 14 servicios ES/EN en cinco categorías, importación repetible y estado inicial de borrador.
- Panel propio en Blade/Tailwind: acceso, recuperación de contraseña, tablero, servicios, filtros, paginación, revisión de versiones y actividad.
- Roles propietario, administrador y editor; creación de cuentas mediante comando interactivo. Sesión bloqueada si la cuenta está inactiva.
- Borradores separados de la versión pública; publicación explícita, recuperación como nuevo borrador, bitácora y rechazo de ediciones desactualizadas.
- Restricción de publicación de procedimientos al propietario.
- Sitio ES/EN con fotografías reales, identidad original, contacto, horarios, instalaciones y aviso de urgencias. El catálogo público lee únicamente versiones publicadas y visibles.
- Vista previa privada de borradores, etiquetas SEO/OG/hreflang, robots y sitemap controlados por configuración.
- Derivados WebP y recorte exclusivo de márgenes transparentes de logos. Los archivos originales permanecen intactos.
- Guía de uso y preparación del despliegue.
- Siete bloques editables: inicio, perfil, consultorio, contacto, SEO, privacidad y horarios. Todos con borradores, publicación, historial, recuperación y control de ediciones simultáneas.
- Perfil profesional y avisos legales publicables únicamente por el propietario. El aviso de privacidad se mantiene vacío hasta incorporar contenido validado; las páginas públicas aparecen después de publicar ambos idiomas.
- Teléfono único para número visible, llamada y WhatsApp; validación de enlaces oficiales y correo público opcional.
- Horario semanal con dos turnos por día, últimas citas y hasta 20 excepciones por fecha; validación de solapamientos, intervalos y fechas duplicadas.
- Horarios publicados y datos estructurados MedicalClinic generados desde los mismos datos. JSON protegido contra cierre de etiquetas de script; no incluye especialidades ni certificaciones pendientes.
- Edición con barra de guardado visible al desplazarse, pestañas ES/EN accesibles por teclado, contadores de texto y errores que llevan al campo correspondiente. Los formularios siguen disponibles sin JavaScript.
- Estado de cambios pendientes que reconoce ediciones revertidas, protección al abandonar y frente a envíos duplicados, publicación bloqueada hasta guardar y vistas previas por idioma. La vista previa legal se habilita al guardar ambos textos.
- Navegación móvil con fondo, cierre por botón/Escape/clic exterior, control del foco y adaptación al cambiar el tamaño de pantalla.
- Sitio público: accesos a horarios y ubicación desde la portada, horario enlazable directamente y barra móvil permanente de llamada/WhatsApp con espacio reservado al pie. Tipografía de lectura ampliada, controles táctiles mayores y encabezado adaptable a pantallas estrechas.
- Menú público con cierre por Escape/clic exterior, foco en la sección elegida y navegación disponible sin JavaScript. Cambiar de idioma conserva el ancla de la sección.
- Buscador local de servicios por nombre, descripción o categoría, tolerante a mayúsculas y acentos; contador de resultados, expansión de coincidencias y recuperación cuando no hay resultados. Aparece solo cuando existen servicios visibles en el catálogo consultado: publicados en público, borradores en la vista previa. Sin JavaScript el catálogo completo permanece disponible.
- Cada consulta de servicio abre WhatsApp con su nombre en un mensaje editable. El sitio no envía mensajes automáticamente. Cuando no hay catálogo publicado, muestra una acción directa para consultar disponibilidad.

## Comprobaciones

- 34 pruebas Laravel de funcionalidad: importaciones repetibles, acceso, cuentas inactivas, límite de intentos, recuperación y uso único de tokens, roles, revisiones, conflictos de edición, traducciones, escape de contenido, visibilidad pública, vistas privadas, enlaces oficiales, privacidad, horarios/excepciones, coherencia del JSON-LD y conservación de errores/cambios tras redirecciones.
- 11 pruebas JavaScript de interacción con DOM: cambios/reversiones, validación entre idiomas, navegación por teclado, enlaces de error, envíos duplicados, regreso con el navegador, cierre/adaptación de menús y búsqueda pública con recuperación de resultados. Ejecutar con `npm.cmd test` dentro de `app-web`.
- Pruebas aisladas en SQLite en memoria; migración e importación ejecutadas también sobre MySQL local.
- Compilación de recursos para producción correcta.
- Composer válido, requisitos de plataforma local satisfechos y auditoría PHP sin avisos de vulnerabilidad al comprobarla. Auditoría npm de dependencias de producción sin vulnerabilidades reportadas.
- Sitio ES/EN y formulario de acceso comprobados por HTTP local con respuesta 200. Las páginas públicas incluyen los accesos y la barra de contacto nuevos; se mantiene la no indexación.
- Revisión visual interactiva, Android/iPhone y mediciones Lighthouse pendientes: el control de navegador disponible en esta sesión informó que no hay navegador conectado. No se ha verificado visualmente el resultado renderizado.

## Siguiente bloque

1. Revisar visualmente el sitio y el panel en escritorio y móvil, y ajustar el patrón de diseño.
2. Incorporar biblioteca de medios con validación de cargas, derivados y control de usos. Por ahora las imágenes originales se preparan mediante script local.
3. Incorporar administración de usuarios por navegador y segundo factor con un componente mantenido.
4. Añadir solicitudes explícitas de revisión. Los servicios y bloques actuales permiten guardar, revisar, publicar y recuperar, sin una bandeja de solicitudes separada.
5. Revisar contenido legal, validar el marcado en herramientas externas y completar accesibilidad/rendimiento.
6. Respaldos automatizados y restauración ensayada, medición de clics si se aprueba y revisión final de publicación.

`config/clinic.php` conserva los valores iniciales del brief y textos de interfaz. La web aplica encima las revisiones publicadas de cada bloque; los borradores nunca sustituyen la versión pública. Editar el archivo de configuración no es el flujo habitual de actualización del contenido.

## Pendientes externos

- Cuenta propietaria local creada con el correo proporcionado por el titular y enlace temporal para elegir contraseña. Los datos de acceso se mantienen fuera del repositorio.
- HostGator Turbo: cuenta contratada, sin acceso proporcionado ni comprobación de PHP/CLI, extensiones, SSH, raíz pública, límites, correo y cron.
- Dominio, correo operativo, aviso de privacidad, traducción inglesa y condiciones de procedimientos por confirmar.
- Repositorio remoto: no configurado. Los commits se encuentran en este equipo.

No activar indexación ni desplegar esta entrega como versión final antes de completar los pendientes de lanzamiento. `SITE_INDEXABLE=false` impide indexación solicitada, pero no sustituye una contraseña o restricción de acceso para un entorno de pruebas remoto.
