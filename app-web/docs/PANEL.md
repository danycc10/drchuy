# Uso del panel — primera entrega

## Acceso

Crear la cuenta desde una terminal en la carpeta de la aplicación con `php artisan app:create-admin`. El comando pide contraseña de al menos 12 caracteres con letras y números, sin mostrarla. No pasar contraseñas como argumentos ni guardarlas en el repositorio.

Entrar a `/admin` con el correo y la contraseña elegidos. El rol `owner` publica todos los servicios; `administrator` publica servicios ordinarios; `editor` prepara borradores. Los procedimientos necesitan al propietario.

La recuperación usa enlaces de Laravel. En desarrollo, `MAIL_MAILER=log` guarda el mensaje en el log privado en lugar de enviarlo. En producción se necesita correo configurado y probado. El log puede contener el enlace de recuperación: no compartirlo ni subirlo a Git.

En móvil, **Menú** abre la navegación. Puedes cerrarla con **Cerrar menú**, la tecla Escape o pulsando fuera. El foco del teclado permanece dentro mientras está abierta y vuelve al botón al cerrar. La sección actual se identifica también para lectores de pantalla.

## Editar un servicio

1. Abrir **Servicios** y buscar por nombre, categoría o estado.
2. Pulsar **Editar** y completar nombre/descripción en las pestañas **Español** e **Inglés**. Cambiar de pestaña conserva lo escrito; las flechas del teclado permiten recorrerlas.
3. Ajustar orden y visibilidad si corresponde.
4. Pulsar **Guardar borrador** en la barra que permanece visible al desplazarse. El estado indica si hay cambios sin guardar. La versión pública todavía no cambia.
5. Revisar el texto guardado y abrir la vista previa en **Español** o **Inglés** desde el editor. Se abre en otra pestaña y muestra la última versión guardada.
6. Si el rol lo permite, pulsar **Publicar versión guardada** y confirmar.

Para ocultar un servicio publicado, desmarcar su visibilidad, guardar y publicar. Un servicio oculto conserva su historial y puede recuperarse. El contador de versiones publicadas incluye las versiones cuya visibilidad es «Oculto».

Mientras hay cambios sin guardar, la publicación queda deshabilitada. Si reviertes lo escrito a su valor guardado, el estado se actualiza. El navegador pide confirmar antes de abandonar cambios pendientes. Los errores de validación enlazan al campo correspondiente y abren su idioma; los textos largos muestran un contador de caracteres. Tras un error, lo escrito sigue disponible para corregirlo y guardarlo.

Estas ayudas se activan con JavaScript. Sin él, ambos idiomas y los botones siguen disponibles, y el servidor conserva las validaciones y los permisos.

Si otra persona guardó cambios desde que se abrió el editor, se rechaza la edición antigua. Copiar los cambios pendientes y recargar antes de continuar.

## Historial

Abrir una versión desde el historial del servicio. **Recuperar como borrador** crea una copia nueva para revisar; no cambia la web hasta publicarla. La actividad conserva quién hizo cada cambio.

## Vista previa

**Vista previa** muestra los últimos borradores visibles en ES/EN y requiere autenticación. El sitio público `/` muestra solo las versiones publicadas; el selector del encabezado cambia el idioma sin agregar prefijos ni parámetros a la URL. Los 14 servicios iniciales llegan como borradores.

La preferencia pública se guarda en la sesión y comienza en español para una visita nueva. El aviso usa `/aviso-de-privacidad` en ambos idiomas. Los enlaces públicos antiguos con `/es` o `/en` redirigen a las direcciones limpias conservando el idioma solicitado. La vista previa privada mantiene rutas por idioma para poder revisar ambos borradores de forma independiente.

## Contenido y horarios

Desde **Contenido y horarios** puedes editar inicio, perfil profesional, instalaciones, contacto, SEO, privacidad, horarios, fotografías y preguntas frecuentes. Todos usan el mismo flujo: guardar borrador, revisar, publicar y recuperar versiones. Los datos compartidos se editan una vez; los textos tienen campos ES/EN.

El propietario publica perfil profesional, avisos legales y fotografías. Los administradores pueden publicar los demás bloques. El editor prepara borradores. El tablero cuenta los borradores de servicios y de contenido.

Para contacto, introduce el teléfono con `+` y código de país. Ese único valor genera teléfono visible, enlace de llamada y WhatsApp. Los enlaces de ubicación y Facebook deben usar HTTPS y sus dominios oficiales. El correo público es opcional; no es el correo privado de acceso al panel.

## Preguntas frecuentes

Abrir **Contenido y horarios → Preguntas frecuentes**. Se incluyen tres preguntas iniciales sobre citas, pagos y disponibilidad de procedimientos, con respuestas tomadas de los textos del consultorio. Llegan como borrador; revisarlas con el médico antes de publicar.

1. Editar el título y la introducción en las pestañas ES/EN.
2. Completar cada pregunta y respuesta en ambos idiomas. Se permiten hasta 12 preguntas, con 200 caracteres por pregunta y 1,500 por respuesta.
3. Usar **Subir**, **Bajar** o **Quitar** para organizar la lista. Desmarcar **Mostrar en el sitio** para conservar una pregunta oculta. Estas acciones requieren JavaScript; editar preguntas existentes sigue siendo posible sin él.
4. Guardar y abrir las vistas previas, que llevan directamente a la sección. Publicar con una cuenta propietaria o administradora; el editor puede preparar el borrador.

La sección pública usa respuestas desplegables accesibles sin JavaScript, respeta la preferencia de movimiento reducido y conserva `#faq` al cambiar de idioma. Solo muestra preguntas visibles de la versión publicada. Publicar una lista vacía o sin preguntas visibles oculta la sección y su acceso desde la portada. Recuperar una versión crea otro borrador y conserva intacta la versión pública hasta volver a publicar.

Las respuestas admiten texto simple y saltos de línea. No introducir indicaciones clínicas ni instrucciones de preparación sin revisión del médico. Si cambian las condiciones de citas o pagos en otros bloques, revisar también las respuestas relacionadas: cada bloque conserva su propio historial.

## Horarios y fechas especiales

Define hasta dos turnos por día y la última cita de cada turno. Marca **Cerrado** cuando no haya atención. La última cita debe ser igual o posterior a la apertura y anterior al cierre; no se permiten turnos solapados ni que atraviesen medianoche.

Las excepciones por fecha reemplazan el horario de ese día. Puedes agregar hasta 20 fechas, con cierre completo o turnos especiales. Las notas son opcionales, pero si incluyes una debe estar en ambos idiomas. El sitio muestra las fechas actuales y futuras; elimina las antiguas desde el editor cuando ya no se necesiten.

La misma versión publicada alimenta horarios visibles y datos estructurados para buscadores. La vista previa utiliza los borradores. No se ofrece un estado automático de «abierto ahora» ni reservas automáticas.

## Privacidad

El aviso inicial está vacío. Pega el texto validado en ambos idiomas y publica con la cuenta propietaria. Hasta entonces no se muestra un enlace de privacidad al visitante. El aviso admite texto simple con saltos de línea; cualquier HTML se muestra como texto, sin ejecutarse.

El editor habilita la vista previa del aviso cuando el borrador guardado contiene ambos idiomas. Antes muestra una indicación para completar los textos.

## Biblioteca de imágenes

1. Abrir **Imágenes** en el menú y pulsar **Subir fotografía**.
2. Darle un nombre descriptivo y elegir un JPG, PNG o WebP de hasta 8 MB, 12 megapíxeles y 8,000 píxeles por lado. Los límites de memoria del servidor también pueden exigir una imagen menor.
3. Subirla. Se conserva el original, se corrige la orientación de fotos JPEG y se crean un WebP de hasta 1,600 píxeles por lado y una miniatura. Los derivados conservan transparencia y no llevan metadatos EXIF del original.
4. Abrir **Fotografías del sitio**. Seleccionar imágenes para el retrato del médico, interior, fachada y entrada. El retrato se utiliza en portada, perfil y vista previa social.
5. Ajustar las descripciones en Español e Inglés para que correspondan a cada fotografía. Guardar el borrador y revisar ambas vistas previas.
6. Publicar con la cuenta propietaria. Subir o seleccionar una fotografía no modifica por sí solo la web pública.

La biblioteca permite buscar por nombre/archivo y filtrar por uso. Todos los roles activos pueden subir y preparar borradores. Propietarios y administradores pueden descargar originales y eliminar imágenes sin uso. Si una imagen aparece en cualquier versión guardada, no se elimina: se conserva para recuperar el historial. Cambiar una fotografía no borra la anterior.

La opción **Fotografía original del sitio** conserva la imagen inicial del paquete del consultorio. Los originales de ese paquete y los logos no se reemplazan al subir archivos. El orden de las tres fotografías corresponde a interior, fachada y entrada.

Las cargas se guardan fuera de la raíz pública. Las imágenes de borradores y los originales requieren acceso al panel. Solo los derivados asociados a la versión publicada están disponibles mediante la ruta pública; pueden permanecer en la caché del navegador hasta cinco minutos después de un reemplazo.

Los módulos de usuarios por navegador y segundo factor siguen pendientes. Consultar `ESTADO_IMPLEMENTACION.md` en la raíz del repositorio.
