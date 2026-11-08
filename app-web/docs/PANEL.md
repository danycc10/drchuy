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

**Vista previa** muestra los últimos borradores visibles en ES/EN y requiere autenticación. Las páginas públicas `/es` y `/en` muestran solo las versiones publicadas. Los 14 servicios iniciales llegan como borradores.

## Contenido y horarios

Desde **Contenido y horarios** puedes editar inicio, perfil profesional, instalaciones, contacto, SEO, privacidad y horarios. Todos usan el mismo flujo: guardar borrador, revisar, publicar y recuperar versiones. Los datos compartidos se editan una vez; los textos tienen campos ES/EN.

El propietario publica perfil profesional y avisos legales. Los administradores pueden publicar los demás bloques. El editor prepara borradores. El tablero cuenta los borradores de servicios y de contenido.

Para contacto, introduce el teléfono con `+` y código de país. Ese único valor genera teléfono visible, enlace de llamada y WhatsApp. Los enlaces de ubicación y Facebook deben usar HTTPS y sus dominios oficiales. El correo público es opcional; no es el correo privado de acceso al panel.

## Horarios y fechas especiales

Define hasta dos turnos por día y la última cita de cada turno. Marca **Cerrado** cuando no haya atención. La última cita debe ser igual o posterior a la apertura y anterior al cierre; no se permiten turnos solapados ni que atraviesen medianoche.

Las excepciones por fecha reemplazan el horario de ese día. Puedes agregar hasta 20 fechas, con cierre completo o turnos especiales. Las notas son opcionales, pero si incluyes una debe estar en ambos idiomas. El sitio muestra las fechas actuales y futuras; elimina las antiguas desde el editor cuando ya no se necesiten.

La misma versión publicada alimenta horarios visibles y datos estructurados para buscadores. La vista previa utiliza los borradores. No se ofrece un estado automático de «abierto ahora» ni reservas automáticas.

## Privacidad

El aviso inicial está vacío. Pega el texto validado en ambos idiomas y publica con la cuenta propietaria. Hasta entonces no se muestra un enlace de privacidad al visitante. El aviso admite texto simple con saltos de línea; cualquier HTML se muestra como texto, sin ejecutarse.

El editor habilita la vista previa del aviso cuando el borrador guardado contiene ambos idiomas. Antes muestra una indicación para completar los textos.

Los módulos de biblioteca de medios, usuarios por navegador y segundo factor siguen pendientes. Consultar `ESTADO_IMPLEMENTACION.md` en la raíz del repositorio.
