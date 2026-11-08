# Medicina Respiratoria

Sitio bilingüe y panel propio para el Dr. Jesús García Espinoza. Laravel 13, Blade, Tailwind y MySQL. Destino previsto: HostGator Turbo.

## Abrir en desarrollo

Desde esta carpeta, en PowerShell:

```powershell
cd app-web
composer dev
```

Con el servidor iniciado:

- Sitio: http://127.0.0.1:8085/es
- Inglés: http://127.0.0.1:8085/en
- Panel: http://127.0.0.1:8085/admin

Para crear tu cuenta, abre otra terminal en `app-web` y ejecuta:

```powershell
php artisan app:create-admin
```

El comando solicita nombre, correo, rol y contraseña oculta. Elige `owner` para la cuenta propietaria. No hay cuentas ni contraseñas predeterminadas. La creación de cuentas por navegador se incorporará en una etapa posterior.

El catálogo se importa como borrador. Para verlo completo, inicia sesión y usa **Vista previa**. En **Servicios**, guarda, revisa y publica cada versión cuando corresponda. Esta publicación modifica solo el entorno donde se ejecuta la aplicación; no despliega cambios en HostGator.

## Organización

- `app-web/`: aplicación; su carpeta `public/` es la única raíz web válida.
- `Medicina_Respiratoria_Web/`: documentación y originales conservados.
- `PLAN_IMPLEMENTACION.md`: alcance y etapas acordados.
- `ESTADO_IMPLEMENTACION.md`: avances, comprobaciones y próximos pasos.
- `app-web/docs/`: uso y preparación del despliegue.

La instalación desde cero se documenta en `app-web/README.md`. Los commits son locales; no se ha configurado un repositorio remoto ni realizado un despliegue.
