# Uso del panel — primera entrega

## Acceso

Crear la cuenta desde una terminal en la carpeta de la aplicación con `php artisan app:create-admin`. El comando pide contraseña de al menos 12 caracteres con letras y números, sin mostrarla. No pasar contraseñas como argumentos ni guardarlas en el repositorio.

Entrar a `/admin` con el correo y la contraseña elegidos. El rol `owner` publica todos los servicios; `administrator` publica servicios ordinarios; `editor` prepara borradores. Los procedimientos necesitan al propietario.

La recuperación usa enlaces de Laravel. En desarrollo, `MAIL_MAILER=log` guarda el mensaje en el log privado en lugar de enviarlo. En producción se necesita correo configurado y probado. El log puede contener el enlace de recuperación: no compartirlo ni subirlo a Git.

## Editar un servicio

1. Abrir **Servicios** y buscar por nombre, categoría o estado.
2. Pulsar **Editar** y completar nombre/descripción en español e inglés.
3. Ajustar orden y visibilidad si corresponde.
4. Pulsar **Guardar borrador**. La versión pública todavía no cambia.
5. Revisar el texto guardado y la **Vista previa** del tablero.
6. Si el rol lo permite, pulsar **Publicar versión guardada** y confirmar.

Para ocultar un servicio publicado, desmarcar su visibilidad, guardar y publicar. Un servicio oculto conserva su historial y puede recuperarse. El contador de versiones publicadas incluye las versiones cuya visibilidad es «Oculto».

Si otra persona guardó cambios desde que se abrió el editor, se rechaza la edición antigua. Copiar los cambios pendientes y recargar antes de continuar.

## Historial

Abrir una versión desde el historial del servicio. **Recuperar como borrador** crea una copia nueva para revisar; no cambia la web hasta publicarla. La actividad conserva quién hizo cada cambio.

## Vista previa

**Vista previa** muestra los últimos borradores visibles en ES/EN y requiere autenticación. Las páginas públicas `/es` y `/en` muestran solo las versiones publicadas. Los 14 servicios iniciales llegan como borradores.

Los demás módulos del plan todavía no aparecen en el menú porque no están implementados. Consultar `ESTADO_IMPLEMENTACION.md` en la raíz del repositorio.
