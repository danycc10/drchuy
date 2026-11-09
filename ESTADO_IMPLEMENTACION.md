# Estado de implementación

Fecha: 9 de noviembre de 2026. Entrega local con administración de usuarios, verificación en dos pasos, preguntas frecuentes editables, biblioteca de imágenes y publicación editorial. El plan completo sigue en desarrollo.

## Implementado

- Repositorio Git con commits separados para documentación, base técnica, acceso, edición y sitio.
- Laravel 13.40.0 sobre PHP 8.3.28; MySQL local 8.4.3, base exclusiva `drchuy_web`.
- Catálogo de 14 servicios ES/EN en cinco categorías, importación repetible y estado inicial de borrador.
- Panel propio en Blade/Tailwind: acceso, recuperación de contraseña, tablero, servicios, filtros, paginación, revisión de versiones y actividad.
- Roles propietario, administrador y editor; creación inicial mediante comando interactivo y administración del equipo por navegador, exclusiva del propietario. Búsqueda, creación, nombre/rol/estado, enlaces privados de contraseña y bitácora; protección del propietario y cierre de sesiones/tokens al cambiar permisos o estado.
- Verificación TOTP optativa por cuenta, con RobThree/TwoFactorAuth 3.0.3 y QR SVG local con BaconQrCode 3.1.1. Activación confirmada, clave cifrada, diez códigos de recuperación de un uso almacenados como hashes, renovación y desactivación con contraseña y segundo factor. Acceso pendiente sin autenticar, caducidad, límites de intentos, rechazo de códigos reutilizados y comprobación de versión de seguridad en cada petición privada. Recuperación excepcional por consola con confirmación y auditoría.
- Borradores separados de la versión pública; publicación explícita, recuperación como nuevo borrador, bitácora y rechazo de ediciones desactualizadas.
- Restricción de publicación de procedimientos al propietario.
- Sitio ES/EN con fotografías reales, identidad original, contacto, horarios, instalaciones y aviso de urgencias. El catálogo público lee únicamente versiones publicadas y visibles.
- Vista previa privada de borradores, etiquetas SEO/OG, robots y sitemap controlados por configuración.
- Derivados WebP y recorte exclusivo de márgenes transparentes de logos. Los archivos originales permanecen intactos.
- Guía de uso y preparación del despliegue.
- Nueve bloques editables: inicio, perfil, consultorio, contacto, SEO, privacidad, horarios, fotografías y preguntas frecuentes. Todos con borradores, publicación, historial, recuperación y control de ediciones simultáneas.
- Perfil profesional y avisos legales publicables únicamente por el propietario. El aviso de privacidad se mantiene vacío hasta incorporar contenido validado; las páginas públicas aparecen después de publicar ambos idiomas.
- Teléfono único para número visible, llamada y WhatsApp; validación de enlaces oficiales y correo público opcional.
- Horario semanal con dos turnos por día, últimas citas y hasta 20 excepciones por fecha; validación de solapamientos, intervalos y fechas duplicadas.
- Horarios publicados y datos estructurados MedicalClinic generados desde los mismos datos. JSON protegido contra cierre de etiquetas de script; no incluye especialidades ni certificaciones pendientes.
- Edición con barra de guardado visible al desplazarse, pestañas ES/EN accesibles por teclado, contadores de texto y errores que llevan al campo correspondiente. Los formularios siguen disponibles sin JavaScript.
- Estado de cambios pendientes que reconoce ediciones revertidas, protección al abandonar y frente a envíos duplicados, publicación bloqueada hasta guardar y vistas previas por idioma. La vista previa legal se habilita al guardar ambos textos.
- Navegación móvil con fondo, cierre por botón/Escape/clic exterior, control del foco y adaptación al cambiar el tamaño de pantalla.
- Refinamiento visual del panel: iconos SVG compartidos con el sitio, acciones identificadas por función, sección activa destacada, tarjetas de contenido con iconos por módulo, métricas y actividad más claras, tablas y estados con mayor legibilidad. Formularios móviles con texto de 16 px y acceso con identidad del consultorio. El botón de guardar conserva su icono al enviar y al regresar con el navegador.
- Sitio público: accesos a horarios y ubicación desde la portada, horario enlazable directamente y barra móvil permanente de llamada/WhatsApp con espacio reservado al pie. Tipografía de lectura ampliada, controles táctiles mayores y encabezado adaptable a pantallas estrechas.
- Menú público con cierre por Escape/clic exterior, foco en la sección elegida y navegación disponible sin JavaScript. Cambiar de idioma conserva el ancla de la sección.
- Buscador local de servicios por nombre, descripción o categoría, tolerante a mayúsculas y acentos; contador de resultados, expansión de coincidencias y recuperación cuando no hay resultados. Aparece solo cuando existen servicios visibles en el catálogo consultado: publicados en público, borradores en la vista previa. Sin JavaScript el catálogo completo permanece disponible.
- Cada consulta de servicio abre WhatsApp con su nombre en un mensaje editable. El sitio no envía mensajes automáticamente. Cuando no hay catálogo publicado, muestra una acción directa para consultar disponibilidad.
- Iconos SVG locales con trazo, tamaño y alineación comunes para contacto, ubicación, horarios, búsqueda y menú. Reemplazan flechas tipográficas; los servicios usan un indicador que gira al abrirse. Botones, accesos rápidos y selector de idioma tienen bordes, espaciado y estados visuales unificados. Los iconos son decorativos para lectores de pantalla y conservan etiquetas de texto; no requieren fuentes de iconos ni servicios externos.
- Movimiento público discreto: entrada de portada, aparición de secciones una sola vez al recorrerlas, galería con entrada escalonada breve, sombra del encabezado al desplazar, subrayado de navegación y transiciones de menú/servicios/botones. Usa APIs nativas, sin dependencias nuevas. Respeta movimiento reducido, cancela animaciones al enfocar o navegar por anclas y mantiene contenido disponible sin JavaScript o sin soporte de animación. Las acciones principales de cita no esperan la entrada de la portada.
- URL pública limpia: `/` para inicio y `/aviso-de-privacidad` para privacidad, en ambos idiomas. Selector POST con CSRF, idioma en sesión, español inicial y conservación de anclas permitidas. Rutas antiguas redirigidas; canonical, Open Graph, JSON-LD y sitemap sin prefijos de idioma. Respuestas de contenido privadas/no-store para evitar mezclar idiomas mediante cachés compartidas.
- Biblioteca privada de fotografías con búsqueda, filtros de uso, carga JPG/PNG/WebP, límites de tamaño/dimensiones/memoria, orientación EXIF, WebP y miniaturas, preservación del original y transparencia. Descarga de originales y eliminación sin uso para propietario/administradores; protección mediante relaciones de base de datos de imágenes conservadas en revisiones.
- Selección del retrato e imágenes de interior/fachada/entrada, descripciones alternativas ES/EN, vistas previas y publicación exclusiva del propietario. Los derivados de borradores requieren autenticación; los originales permanecen privados. El contenido publicado alimenta fotos, imagen social y foto del marcado estructurado. Se mantiene la opción de las fotografías iniciales y los logos originales.

- Preguntas frecuentes: hasta 12 entradas bilingües, orden, visibilidad, recuperación de formularios tras errores e historial. Tres preguntas prácticas iniciales como borradores para revisión médica. Sección pública desplegable sin dependencia de JavaScript, con acceso desde portada, enlace de contacto y ancla conservada al cambiar idioma. Solo aparece al publicar preguntas visibles; propietario y administradores pueden publicar.

## Comprobaciones

- 64 pruebas Laravel (671 aserciones): importaciones, autenticación, cuentas/roles, cierre de sesiones, enlaces privados, activación/caducidad/reutilización/límites de TOTP y recuperación, revisiones, conflictos, traducciones, escape de contenido, vistas privadas/públicas, enlaces oficiales, privacidad, horarios, JSON-LD, idioma, cargas y optimización de imágenes, fallos de almacenamiento y publicación/recuperación de fotografías y preguntas frecuentes.
- 22 pruebas JavaScript de interacción con DOM: cambios/reversiones, validación entre idiomas, navegación por teclado, enlaces de error y apertura del formulario de seguridad correcto, envíos duplicados, regreso con el navegador, cierre/adaptación de menús, selector de idioma con anclas, búsqueda pública, selección de imágenes/carga, alta/eliminación/orden/límite de preguntas y comportamiento del movimiento. Estas últimas verifican entrada única, cancelación al cambiar preferencias/enfocar/navegar y disponibilidad sin APIs de animación mediante dobles de prueba; no sustituyen una revisión visual de fluidez. Ejecutar con `npm.cmd test` dentro de `app-web`.
- Pruebas aisladas en SQLite en memoria; migración e importación ejecutadas también sobre MySQL local.
- Compilación de recursos para producción correcta.
- Composer válido, requisitos de plataforma local satisfechos y auditoría PHP sin avisos de vulnerabilidad al comprobarla. Auditoría npm de dependencias de producción sin vulnerabilidades reportadas.
- Sitio ES/EN y formulario de acceso comprobados por HTTP local con respuesta 200. Las páginas públicas incluyen los accesos y la barra de contacto nuevos; se mantiene la no indexación.
- Revisión visual interactiva, Android/iPhone y mediciones Lighthouse pendientes: el control de navegador disponible en esta sesión informó que no hay navegador conectado. No se ha verificado visualmente el resultado renderizado.

## Siguiente bloque

1. Revisar visualmente el sitio y el panel en escritorio y móvil, y ajustar el patrón de diseño.
2. Revisar con el médico las preguntas frecuentes iniciales y publicar las respuestas aprobadas.
3. Probar el escaneo QR con una aplicación autenticadora, recuperación y sesiones en dos navegadores; activar dos pasos en las cuentas reales.
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
