# Preparación del despliegue en HostGator Turbo

Guía inicial; no se ha conectado a la cuenta ni realizado un despliegue. Completar los requisitos de lanzamiento del plan antes de publicar.

## Verificar la cuenta

- PHP web y CLI compatibles con `composer.lock` y las extensiones requeridas por `composer check-platform-reqs`.
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

Ejecutar migraciones revisadas y después `php artisan db:seed --force` para importar registros iniciales faltantes de servicios y bloques. Los seeders no publican contenido, no sobrescriben ediciones existentes y no crean usuarios. Crear la cuenta propietaria con `php artisan app:create-admin` en la instalación inicial.

Regenerar cachés en destino con `php artisan optimize`. No copiar cachés de configuración generadas con el entorno local. Preservar `.env`, imágenes subidas y archivos persistentes entre versiones. No usar permisos 777 ni un endpoint web para ejecutar Artisan.

## Aceptación y reversión

Verificar HTTPS, ambos idiomas, acceso y permisos, recuperación por correo, guardado/publicación, imágenes, enlaces, horarios y restricciones de documentos privados. Revisar la restauración de base y archivos en pruebas. La reversión debe restaurar código y datos compatibles; no ejecutar automáticamente migraciones destructivas.

Antes del lanzamiento faltan segundo factor, módulos administrativos restantes, respaldo operativo, texto del aviso de privacidad aprobado y revisión editorial/visual. La no indexación no protege un staging: restringir su acceso mediante los mecanismos del hosting. Al lanzar, actualizar URLs, habilitar indexación y registrar Search Console con cuentas del titular.

El JSON-LD utiliza [MedicalClinic](https://schema.org/MedicalClinic), [OpeningHoursSpecification](https://schema.org/OpeningHoursSpecification) y [specialOpeningHoursSpecification](https://schema.org/specialOpeningHoursSpecification) para reflejar los datos visibles publicados. Las pruebas comprueban JSON válido, coherencia de turnos/excepciones y escape de texto. La validación externa y cualquier elegibilidad para resultados enriquecidos siguen pendientes; no se garantizan resultados de búsqueda.
