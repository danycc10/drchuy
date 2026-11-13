# Medicina Respiratoria

Sitio bilingüe y panel propio para el Dr. Jesús García Espinoza. Laravel 13, Blade, Tailwind y MySQL. Destino previsto: HostGator Turbo.

## Abrir en desarrollo

Desde esta carpeta, en PowerShell:

```powershell
cd app-web
composer dev
```

Con el servidor iniciado:

- Sitio: http://127.0.0.1:8085/
- Idioma: selector ES/EN en el encabezado; conserva la misma URL y recuerda la selección durante la sesión.
- Panel: http://127.0.0.1:8085/admin
- Biblioteca de imágenes: http://127.0.0.1:8085/admin/media (requiere acceso al panel).
- Usuarios: http://127.0.0.1:8085/admin/users (solo propietario).
- Verificación en dos pasos: http://127.0.0.1:8085/admin/security (cada usuario configura su cuenta).
- Mantenimiento: http://127.0.0.1:8085/admin/maintenance (solo propietario; estado de respaldos y exportación).

Para crear tu cuenta, abre otra terminal en `app-web` y ejecuta:

```powershell
php artisan app:create-admin
```

El comando solicita nombre, correo, rol y contraseña oculta. Elige `owner` para la primera cuenta propietaria. No hay cuentas ni contraseñas predeterminadas. Después, el propietario puede crear y administrar las cuentas desde **Usuarios**.

El catálogo se importa como borrador. En el entorno local actual, los 14 servicios originales ya están publicados por solicitud del titular y aparecen con **Solicitar servicio**, que prepara un mensaje de WhatsApp con el nombre elegido. En nuevas instalaciones, el resumen del panel lista los servicios importados como **Pendiente de publicar**: abre cada uno y usa **Publicar ahora**, o edítalo y pulsa **Guardar y publicar**. Desde entonces cada guardado actualiza el sitio al instante, sin revisiones intermedias. Esta publicación modifica solo el entorno donde se ejecuta la aplicación; no despliega cambios en HostGator.

## Organización

- `app-web/`: aplicación; su carpeta `public/` es la única raíz web válida.
- `Medicina_Respiratoria_Web/`: documentación y originales conservados.
- `PLAN_IMPLEMENTACION.md`: alcance y etapas acordados.
- `ESTADO_IMPLEMENTACION.md`: avances, comprobaciones y próximos pasos.
- `app-web/docs/`: uso y preparación del despliegue.

La instalación desde cero se documenta en `app-web/README.md`. Los commits son locales; no se ha configurado un repositorio remoto ni realizado un despliegue.
