# Preparación del despliegue en HostGator Turbo

Guía inicial; no se ha conectado a la cuenta ni realizado un despliegue. Completar los requisitos de lanzamiento del plan antes de publicar.

## Verificar la cuenta

- PHP web y CLI compatibles con `composer.lock` y las extensiones requeridas por `composer check-platform-reqs`.
- Para la biblioteca: extensiones GD y EXIF; GD debe incluir lectura JPEG/PNG/WebP y escritura WebP. Comprobar `gd_info()` en el servidor real. Configurar `upload_max_filesize` de al menos 8M y `post_max_size` mayor (por ejemplo, 12M). La aplicación limita cada carga a 8 MB y comprueba dimensiones y memoria antes de decodificar.
- Para dos pasos: XMLWriter e iconv para generar el QR SVG localmente; no necesita Imagick, servicios externos ni procesos permanentes. Comprobar la sincronización del reloj del servidor y usar caché persistente para los límites de intentos (no `array` en producción).
- Para respaldos: extensión ZIP/libzip con soporte AES-256, espacio para temporales y copias, cron y destino externo definido. La guía [RESPALDOS.md](RESPALDOS.md) describe creación, retención, verificación y restauración en una base separada.
- MySQL, base y usuario exclusivos; SSH y Composer disponibles o un proceso de empaquetado compatible.
- HTTPS, raíz pública configurable, permisos sobre `storage` y `bootstrap/cache`, SMTP, cron, cuotas y respaldos.
- Dominio y subdominio de pruebas bajo control del titular.

## Preparar una entrega

1. Seleccionar un commit probado; ejecutar pruebas y compilar con `npm ci` / `npm run build` en un entorno de construcción.
2. Instalar dependencias con `composer install --no-dev --prefer-dist --optimize-autoloader` en un entorno compatible con el servidor.
3. Empaquetar la aplicación y `public/build`. Excluir `.env`, tests, node_modules, logs, datos locales y el paquete de documentos de la carpeta superior.
4. Respaldar la base y archivos del destino y conservar la entrega anterior antes de reemplazar código.

## Configurar el servidor

Apuntar el dominio únicamente a `public/`. Si el dominio principal obliga a `public_html`, colocar allí solo los archivos públicos y adaptar el punto de entrada y la ruta pública de Laravel. No mover todo el proyecto a una carpeta accesible por HTTP.

Configurar `.env` con `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://dominio-confirmado`, credenciales exclusivas, correo real y cookies seguras mediante `SESSION_SECURE_COOKIE=true`. Crear `APP_KEY` una sola vez al instalar y conservarla en despliegues posteriores. Mantener `SITE_INDEXABLE=false` durante revisión.

Fuera del entorno local, la aplicación solo acepta el hostname exacto de `APP_URL`; no confía automáticamente en subdominios. Configurar en el servidor las redirecciones de alias como `www` hacia el dominio elegido. Conservar `SESSION_HTTP_ONLY=true` y `SESSION_SAME_SITE=lax` (o `strict` si se verifica el flujo de acceso). `app:launch-check` comprueba estas opciones. La restricción de hosts sigue el mecanismo [TrustHosts de Laravel](https://github.com/laravel/docs/blob/13.x/requests.md#configuring-trusted-hosts).

Las claves TOTP se cifran con `APP_KEY`: incluirla en el respaldo privado y no regenerarla al actualizar o restaurar. La migración de seguridad no activa dos pasos ni cambia contraseñas existentes. Activarlo desde **Mi seguridad** y guardar los códigos antes de cerrar la sesión. Los enlaces generados desde **Usuarios** usan `APP_URL`, que debe coincidir con el dominio HTTPS real.

Ejecutar migraciones revisadas y después `php artisan db:seed --force` para importar registros iniciales faltantes de servicios y bloques. Los seeders no publican contenido, no sobrescriben ediciones existentes y no crean usuarios. Crear la cuenta propietaria con `php artisan app:create-admin` en la instalación inicial.

La edición de imágenes de servicios requiere la migración `2026_11_10_000003_add_image_to_service_revisions`. Respaldar antes de ejecutar `php artisan migrate --force`. Las versiones existentes conservan la ilustración de su categoría hasta que se seleccione y publique una imagen propia.

Regenerar cachés en destino con `php artisan optimize`. No copiar cachés de configuración generadas con el entorno local. Preservar `.env`, imágenes subidas y archivos persistentes entre versiones. No usar permisos 777 ni un endpoint web para ejecutar Artisan.

La biblioteca guarda originales y derivados en `storage/app/private/media`. Incluir esta carpeta y las tablas de medios/revisiones en los respaldos y conservarlas al desplegar. No crear enlaces públicos hacia esa carpeta. Las rutas de Laravel sirven derivados publicados y comprueban autenticación para borradores y originales; no se necesita `storage:link` para este módulo. Las cargas se procesan durante la petición, sin workers permanentes. Confirmar memoria, tiempo de ejecución y cuota disponibles en el plan Turbo.

El disco `local` mantiene `serve=false`: no se utilizan las rutas genéricas de URLs temporales de Laravel. Toda imagen debe pasar por los controladores del sitio y sus permisos. Conservar esta configuración al actualizar; las copias privadas tampoco deben tener un acceso HTTP directo.

## Aceptación y reversión

Ejecutar `php artisan app:launch-check` después de configurar el destino. Informa los requisitos verificables sin mostrar credenciales, cambiar contenido ni enviar mensajes; termina con código 1 si quedan pendientes. `--json` permite guardar un informe automatizado. Un resultado correcto no sustituye la aprobación médica/legal, probar correo, cron, copia externa, dispositivos y rendimiento.

Verificar HTTPS, ambos idiomas, acceso y permisos, recuperación por correo, guardado/publicación, imágenes, enlaces, horarios y restricciones de documentos privados. Revisar la restauración de base y archivos en pruebas. La reversión debe restaurar código y datos compatibles; no ejecutar automáticamente migraciones destructivas.

Antes del lanzamiento falta probar dos pasos con una aplicación autenticadora y el servidor real, activar y comprobar cron/copia externa, incorporar el aviso de privacidad aprobado y completar la revisión editorial/visual. Los módulos de usuarios, revisiones y mantenimiento ya están implementados localmente. Probar también la recuperación y el cierre de sesiones con dos navegadores. La no indexación no protege un staging: restringir su acceso mediante los mecanismos del hosting. Al lanzar, actualizar URLs, habilitar indexación y registrar Search Console con cuentas del titular.

El JSON-LD utiliza [MedicalClinic](https://schema.org/MedicalClinic), [OpeningHoursSpecification](https://schema.org/OpeningHoursSpecification) y [specialOpeningHoursSpecification](https://schema.org/specialOpeningHoursSpecification) para reflejar los datos visibles publicados. La dirección se expresa como [PostalAddress](https://schema.org/PostalAddress) (calle, localidad, estado, código postal y país MX, obtenidos de los campos de contacto publicados), el médico como `Person` con fotografía y cédula publicada, y los pagos con `paymentAccepted`/`currenciesAccepted`. Cuando hay preguntas frecuentes publicadas se agrega una entidad [FAQPage](https://schema.org/FAQPage) en el idioma mostrado; no se emite en la vista previa. Las pruebas comprueban JSON válido, coherencia de turnos/excepciones, dirección estructurada y escape de texto. La validación externa y cualquier elegibilidad para resultados enriquecidos siguen pendientes; no se garantizan resultados de búsqueda.
