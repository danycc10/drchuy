# Respaldos y restauración

El módulo respalda los datos del sitio y del panel, las revisiones, cuentas, bitácora, solicitudes de revisión y las tres variantes de cada fotografía subida. Conserva las relaciones y los indicadores de versión publicada. No incluye sesiones, enlaces de contraseña, cachés, trabajos, `.env`, `APP_KEY`, código ni las imágenes iniciales versionadas en Git. Conservar también el commit, los archivos lock, el paquete de despliegue y la configuración privada.

## Crear y verificar

Desde `app-web`, con las migraciones aplicadas:

```sh
php artisan app:backup
php artisan app:verify-backup storage/app/private/backups/NOMBRE.zip
```

Los ZIP se guardan fuera de `public`, con entradas cifradas mediante AES-256, manifiesto, conteos y hashes SHA-256. La clave se deriva de `APP_KEY` exclusivamente para los respaldos; no se guarda en el archivo. **Conservar la misma `APP_KEY` en un lugar privado separado de las copias. Sin ella no se pueden recuperar el respaldo ni las claves TOTP.** No regenerarla al reinstalar o restaurar.

Cada copia se valida antes de aparecer como completada en **Mantenimiento**. Una falla queda registrada y no deja un ZIP marcado como completo. Los datos MySQL se leen con una instantánea en aislamiento repetible. Si falta una fotografía o cambia durante la copia y falla su comprobación, se rechaza el respaldo.

Límites iniciales: 8 MB de JSON por tabla, 32 MB por archivo y 512 MB de contenido total. La exportación de tablas escribe archivos temporales privados; las fotografías se agregan desde disco. Si el volumen excede estos límites, adaptar el procedimiento y comprobar memoria/cuotas antes de aumentarlos. Los límites protegen el entorno compartido; no son la capacidad contratada de HostGator.

## Programación y retención

La programación permanece desactivada hasta confirmar cron, espacio, retención y destino externo. Configuración disponible:

```dotenv
BACKUP_ENABLED=true
BACKUP_TIME=03:15
BACKUP_RETENTION_DAYS=14
```

El programador ejecuta `app:backup --prune` diariamente a la hora indicada, en `America/Matamoros`, sin solapamiento. Configurar el cron de HostGator para ejecutar `php /ruta/app-web/artisan schedule:run` con la frecuencia que admita el plan. No requiere workers permanentes. Comprobar la ruta y versión del PHP CLI. Tras cambiar configuración, regenerar la caché en el destino.

La retención se aplica solo después de completar un respaldo correcto y conserva como mínimo los dos últimos respaldos completos registrados. También puede ejecutarse manualmente `php artisan app:backup --prune`. Se conserva el historial de las copias retiradas.

**Una copia en el mismo servidor no protege contra la pérdida del hosting.** Copiar los archivos cifrados a un destino externo bajo control del titular; ese destino y su automatización todavía deben definirse. Comprobar los hashes al copiarlos. No crear enlaces públicos ni colocar copias en `public_html`. La clave se conserva separada.

## Restaurar en un entorno separado

La restauración rechaza la conexión activa, la base identificada como origen, una base con datos, migraciones distintas y una carpeta de medios ocupada o dentro de la raíz pública/medios activos. No existe una restauración por navegador ni una opción para sobrescribir la instalación activa.

1. Preparar una base **nueva y vacía**, con un usuario autorizado para ella, y una carpeta privada vacía para los medios. Usar el código del commit correspondiente al respaldo y la misma `APP_KEY` de origen.
2. Configurar la conexión `backup_restore` mediante `RESTORE_DB_HOST`, `RESTORE_DB_PORT`, `RESTORE_DB_DATABASE`, `RESTORE_DB_USERNAME` y `RESTORE_DB_PASSWORD` en la configuración privada. No poner contraseñas en argumentos ni en Git.
3. Aplicar las migraciones al destino; no ejecutar seeders:

```sh
php artisan migrate --database=backup_restore --force
php artisan app:restore-backup /ruta/privada/NOMBRE.zip --connection=backup_restore --media-path=/ruta/privada/medios-restaurados
```

4. Revisar conteos, referencias, fotografías, roles, contenido publicado, borradores y versiones en una instalación de pruebas protegida. El comando ya comprueba integridad y referencias antes de terminar. Invalida tokens de sesión recordada e incrementa la versión de seguridad de las cuentas restauradas; no recupera sesiones ni enlaces de contraseña. Las contraseñas y la configuración TOTP se conservan.
5. La carpeta recuperada contiene los subdirectorios UUID de las fotografías. En una instalación recuperada corresponde a `storage/app/private/media`. Promover el entorno solo después de verificarlo y con un procedimiento de reversión; ese cambio no lo hace el comando.

El indicador **Restauración comprobada** registra que el comando terminó contra un destino separado. No implica una revisión visual, aprobación editorial ni despliegue del sitio recuperado.

## Ensayo local del 10 de noviembre de 2026

Se creó un respaldo real cifrado y se restauraron **10 tablas y 53 registros** en `drchuy_restore_20261110_41bfbc41`, una base MySQL local separada. Los conteos de origen permanecieron iguales. No había fotografías subidas en la biblioteca local; la recuperación de archivos, su cifrado y las fallas por medios ausentes se verificaron además con archivos de prueba aislados.

El informe y la carpeta de medios del ensayo están en `.local/backup-restore-check.json` y su destino indicado, fuera de Git y de la raíz pública. La base de ensayo se conserva para revisión y no está conectada al sitio público. El ZIP real está en la carpeta privada de respaldos. No se ha conectado al hosting ni configurado una copia externa.
