# Plan de implementación — Medicina Respiratoria

Fecha: 8 de noviembre de 2026. Estado: implementación iniciada; primera entrega funcional local. Consultar `ESTADO_IMPLEMENTACION.md` para distinguir lo implementado de las etapas pendientes.

## 1. Decisiones y punto de partida

- Tecnología solicitada: Laravel.
- Alojamiento confirmado por el cliente: HostGator compartido, plan Turbo, ya contratado.
- Dominio: por definir. No impide desarrollar ni revisar localmente.
- Administración: panel avanzado propio en Laravel, con interfaz a medida, solicitado por el cliente e incluido en el alcance principal. Se descarta Filament por preferencia expresa del cliente.
- Objetivo: presentar el consultorio y facilitar solicitudes de cita por WhatsApp, en español e inglés.
- Al levantar este plan, la carpeta contenía documentación y recursos sin aplicación Laravel. Ya se creó `app-web/` y un repositorio Git local con commits por cambio funcional.
- Entorno local comprobado: PHP 8.3.28, Composer 2.9.2, Node 24.12.0, npm 11.10.1 y MySQL 8.4.3. La cuenta HostGator todavía requiere verificación independiente.

Fuentes del proyecto: `Medicina_Respiratoria_Web/01_BRIEF_Y_CONTENIDOS/`, `02_LOGOS_Y_FOTOGRAFIAS_ORIGINALES/` y `04_DOCUMENTACION_Y_VERIFICACIONES/`. El ZIP de `03_REFERENCIA_PROTOTIPO/` contiene HTML/CSS previo: sirve como referencia visual, no como fuente definitiva de textos ni como aplicación terminada.

Ante diferencias, prevalecen el brief y los contenidos públicos revisados sobre el prototipo. El JSON alimentará la carga inicial de datos públicos seleccionados; no se expondrá íntegro porque contiene notas internas. Las afirmaciones de verificación documental corresponden al paquete entregado, no a una verificación independiente realizada en esta planificación.

## 2. Arquitectura propuesta

| Componente | Propuesta | Propósito |
|---|---|---|
| Backend | Laravel 13, PHP 8.3 o superior compatible | Sitio y administración en una aplicación |
| Sitio público | Blade + Tailwind CSS; JavaScript ligero | HTML renderizado en servidor, carga rápida y SEO |
| Panel | Desarrollo propio con Laravel, Blade, Tailwind CSS y JavaScript ligero | Interfaz a medida, formularios, tablas y gestión de contenido |
| Datos | MySQL, preferentemente 8.0 si la cuenta lo ofrece | Contenidos, traducciones, usuarios y revisiones |
| Recursos | Vite, compilado localmente o en CI | Subir CSS/JS terminados al hosting |
| Archivos | Almacenamiento local con separación pública/privada | Fotografías publicadas y originales protegidos |
| Procesos | Peticiones PHP; cron solo para tareas acotadas | Evitar depender de procesos permanentes |
| Versionado | Git, archivos lock y entregas identificadas | Reproducir despliegues y revertir cambios |

Laravel 13 requiere PHP >= 8.3. La elección se basa en la documentación oficial y se confirmará con Composer y el servidor real antes de instalar dependencias. [Requisitos Laravel](https://laravel.com/docs/13.x/deployment).

El panel tendrá rutas bajo `/admin`, controladores, validación de formularios, políticas de autorización y componentes Blade propios. Se construirán navegación, tablas con búsqueda/filtros/paginación, formularios, selectores de medios y mensajes de estado reutilizables. La autenticación, sesiones, protección CSRF y recuperación de contraseña se apoyarán en los mecanismos de Laravel; el segundo factor utilizará un componente mantenido y compatible, por seleccionar al implementar. No se desarrollarán algoritmos propios de contraseñas o criptografía.

El diseño y los flujos administrativos se adaptarán al consultorio. Este enfoque permite controlar la experiencia completa y requiere más trabajo inicial y mantenimiento de los componentes de interfaz. Los recursos CSS/JS del panel se cargarán únicamente en sus páginas, separados de los del sitio público.

No se requieren Node activo, Redis, WebSockets ni workers permanentes en producción para este alcance. Las tareas pesadas de imágenes y respaldos se limitarán y programarán según los recursos efectivos del plan.

## 3. Sitio público

Primera versión: página principal por idioma con secciones navegables y páginas independientes para privacidad. Propuesta de rutas: `/es`, `/en`, `/es/aviso-de-privacidad`, `/en/privacy-policy`; `/` redirigirá a español. El selector de idioma conservará la sección equivalente cuando exista. No se dependerá de geolocalización para elegir idioma.

| Sección | Contenido y comportamiento |
|---|---|
| Inicio | Logo oficial, retrato original, mensaje principal y botón de WhatsApp |
| Servicios | 14 servicios en cinco categorías; fichas expandibles accesibles |
| El médico | Biografía bilingüe, formación y denominación pública autorizada |
| Consultorio | Galería real, referencias de acceso y accesibilidad confirmada |
| Ubicación y horarios | Dirección, turnos, últimas citas, cierres, medios de pago y enlace a Maps |
| Contacto | WhatsApp, teléfono clicable y Facebook oficial |
| Pie | Privacidad, aviso de urgencias y enlaces legales aplicables |

Las cinco categorías conservarán los identificadores del JSON: consulta y seguimiento (1, 2, 3, 5, 6); diagnóstico y sueño (4, 7); tratamiento y educación (8, 9); procedimientos (10–13); salud ocupacional (14). Se conservarán los nombres oficiales en español y sus equivalencias editoriales en inglés.

Datos de control: WhatsApp `+528781146336`; cédula de licenciatura `12731500`; lunes, martes, jueves y viernes de 10:00–15:00 y 16:00–19:00; últimas citas 14:00 y 18:00; miércoles, sábado y domingo cerrado. La solicitud por WhatsApp no representa una cita confirmada.

Diseño móvil primero, basado en el logo azul/gris original y fotografías auténticas. Menú accesible, contraste AA, foco visible, navegación por teclado, respeto a movimiento reducido y textos alternativos en ambos idiomas. Se crearán derivados WebP y, cuando convenga, AVIF sin alterar identidad, rostro ni arquitectura. Los originales se conservarán.

SEO: títulos y descripciones por idioma, canonical, hreflang es-MX/en-US, Open Graph, sitemap y robots. El marcado MedicalClinic/Physician se construirá exclusivamente con información pública aprobada y horarios divididos. No se incluirán certificaciones pendientes ni puntuaciones inventadas. La configuración del dominio definitivo completará las URLs canónicas y Search Console.

Maps comenzará como enlace con dirección visible; un mapa incrustado se añadirá solo después de revisar su carga, privacidad y necesidad de consentimiento. La política de cookies dependerá de los servicios realmente incorporados.

## 4. Panel avanzado incluido

Se desarrollará un administrador de contenido estructurado con diseño propio. Permitirá modificar y ordenar las secciones previstas conservando el diseño y las reglas editoriales. El panel se entregará inicialmente en español, con edición de contenido ES/EN.

La interfaz incluirá menú lateral adaptable a móvil, resumen de actividad, formularios agrupados por tarea, pestañas para español/inglés, indicadores de borrador/publicado, errores junto a cada campo y avisos ante cambios sin guardar. Las tablas tendrán búsqueda, filtros y paginación en servidor. Se revisará primero una muestra funcional con acceso, tablero y edición de un servicio para fijar el patrón visual antes de extenderlo al resto de módulos.

| Módulo | Alcance de la primera entrega |
|---|---|
| Resumen | Estado de publicación, traducciones pendientes y actividad reciente |
| Páginas y secciones | Editar encabezados, textos, botones, orden y visibilidad ES/EN |
| Servicios | Gestionar categorías, descripciones, orden, visibilidad y condiciones de disponibilidad |
| Perfil profesional | Biografía, formación y campos públicos; control de cambios sensibles |
| Horarios | Turnos, días cerrados, últimas citas y excepciones por fecha |
| Contacto | Teléfonos, enlaces oficiales, dirección y medios de pago |
| Biblioteca de medios | Carga de imágenes, tamaño máximo, textos alternativos, orden, usos y derivados optimizados |
| SEO | Títulos, descripciones e imagen social por idioma; sitemap con contenido publicado |
| Documentos públicos | Edición y versiones del aviso de privacidad y textos legales aprobados |
| Usuarios y permisos | Propietario, administrador y editor; acceso limitado por acción |
| Flujo editorial | Borrador, revisión, vista previa privada, publicación e historial recuperable |
| Bitácora | Quién modificó, aprobó o publicó contenido y cuándo |
| Mantenimiento | Estado del último respaldo y exportación de contenido; recuperación operada por responsable técnico |

Permisos propuestos: el propietario controla usuarios, cambios profesionales sensibles y publicación final; el administrador gestiona contenido y publica contenido ordinario; el editor prepara borradores y no publica ni administra usuarios. Los permisos se aplicarán en servidor, también en cargas y vistas previas. No habrá registro público de usuarios. Se incluirán limitación de intentos, recuperación de contraseña mediante correo configurado y segundo factor para cuentas administrativas.

Cada cambio se guardará primero como borrador. El sitio consultará solamente la revisión publicada; editar un registro activo no alterará inmediatamente la web. Publicar actualizará la referencia de versión y limpiará la caché relacionada. Restaurar recuperará una revisión como borrador antes de volver a publicarla. Las traducciones incompletas impedirán publicar una sección bilingüe requerida.

Los originales y la bitácora no serán editables por un editor común. La biblioteca aceptará formatos de imagen permitidos, comprobará contenido y dimensiones y evitará archivos ejecutables. Se retirarán metadatos innecesarios de los derivados públicos. Los documentos privados de identidad o credenciales no forman parte de la biblioteca pública.

La medición de clics hacia WhatsApp queda prevista con eventos por sección e idioma, activables tras definir el aviso y la política de medición. El panel distinguirá claramente clics de citas confirmadas. Las citas no se pueden inferir a partir de un enlace wa.me: una integración de WhatsApp o una agenda requeriría otro alcance. El sitio y el panel funcionarán aunque la medición esté desactivada.

## 5. Modelo de datos y organización

Entidades propuestas: usuarios y permisos; páginas/secciones y traducciones; categorías y servicios con IDs de origen; perfil y contacto; horarios semanales y excepciones; medios y textos alternativos; revisiones editoriales; bitácora; ejecuciones de respaldo; agregados de clics solo si se activa medición.

Separar valores compartidos —teléfono, dirección, horario— de traducciones editoriales. Usar restricciones de unicidad para idioma/entidad y para los IDs importados. La importación inicial será repetible sin duplicados ni sobreescritura de cambios editoriales publicados.

Se creará la aplicación en una carpeta nueva `app-web/` dentro del espacio de trabajo, conservando intacto el paquete recibido. Laragon apuntará a `app-web/public`. La documentación original quedará fuera de la raíz pública y del paquete de despliegue. Los controladores del sitio leerán únicamente contenido publicado; las políticas y servicios de publicación serán compartidos por todos los módulos del panel.

## 6. Comprobación de HostGator Turbo

Tener contratado Turbo confirma el destino, pero aún falta revisar esta cuenta concreta:

1. Versión PHP del dominio y de CLI, extensiones de Laravel y dependencias del panel, `pdo_mysql`, `intl` y GD o ImageMagick.
2. Versión real de MySQL, credenciales con permisos limitados a la base del proyecto y codificación utf8mb4.
3. Acceso SSH, Composer 2 y límites de memoria/ejecución. Si Composer no puede ejecutarse allí, preparar `vendor` en un entorno compatible con el servidor y verificar requisitos.
4. Raíz pública configurable, reglas `.htaccess`, permisos de escritura y posibilidad de publicar medios sin exponer carpetas privadas.
5. HTTPS, correo SMTP para recuperación de cuentas, cron, espacio disponible, inodos, límites de cargas y política de respaldos.
6. Subdominio de pruebas cuando exista dominio; mientras tanto, revisión local. Las pruebas remotas tendrán acceso protegido y no se indexarán.

HostGator México documenta PHP 8.3/8.4 entre sus versiones de compartidos y MySQL variable según servidor. Esto no sustituye la revisión de la cuenta Turbo. También indica que SSH en compartidos se habilita mediante soporte. [Software HostGator](https://soporte.hostgator.mx/hc/es-419/articles/28443364586259--Cu%C3%A1l-es-la-versi%C3%B3n-del-software-principal-utilizado-en-HostGator), [Acceso SSH](https://soporte.hostgator.mx/hc/es-419/articles/28440994540947-C%C3%B3mo-habilitar-el-acceso-SSH-en-el-plan-de-hosting).

Despliegue preferido: aplicación fuera de la carpeta pública y dominio apuntando a su directorio `public`. Si el dominio principal obliga a usar `public_html`, alojar allí únicamente los archivos públicos y adaptar las rutas del punto de entrada y la ruta pública de Laravel. Nunca colocar el proyecto completo en una carpeta servida por HTTP. Esta separación sigue el principio de despliegue de Laravel. [Configuración del servidor](https://laravel.com/docs/13.x/deployment).

El procedimiento incluirá respaldo previo; instalación reproducible de dependencias sin desarrollo; carga del build; `.env` exclusivo del servidor; clave de aplicación persistente; `APP_DEBUG=false`; migraciones; permisos mínimos; cachés regeneradas en destino; prueba de rutas, panel, imágenes, correo y HTTPS. Las subidas de usuarios y `.env` sobrevivirán a cada despliegue. Se conservará una entrega anterior y un respaldo coherente de base de datos para reversión; no se dependerá de migraciones inversas destructivas.

Respaldos propuestos: base diaria y archivos según cambios, más copia previa a cada publicación técnica. Confirmar retención, destino externo bajo control del titular y cuotas antes de activar automatización. Probar una restauración en pruebas. Cron procesará lotes pequeños si se requieren trabajos diferidos, sin solaparse ni mantener workers permanentes; frecuencia sujeta a límites del proveedor.

## 7. Etapas y estimación

Estimación orientativa para una persona dedicada, con el contenido entregado como base y una ronda de ajustes por hito. No es una fecha comprometida: se recalibrará tras revisar el hosting y cerrar el detalle del panel.

| Etapa | Trabajo | Entregable y condición para avanzar | Esfuerzo |
|---|---|---|---|
| 0. Preparación | Revisar Turbo, entorno local, permisos y pendientes | Matriz de compatibilidad y alcance cerrado | 1–2 días |
| 1. Base técnica y panel propio | Laravel, BD, autenticación, permisos, importación y componentes administrativos | Acceso, tablero y edición de un servicio para revisar diseño y comportamiento | 4–6 días |
| 2. Sitio bilingüe | Diseño móvil, secciones, fotografías, CTA y SEO inicial | Versión local ES/EN navegable para revisión | 3–5 días |
| 3. Administración avanzada | Pantallas propias, módulos, medios, traducciones, revisiones, permisos y bitácora | Ciclo editar → revisar → publicar → recuperar completo | 9–13 días |
| 4. Calidad y contenido | Pruebas de interfaz y seguridad, accesibilidad, rendimiento, reglas editoriales y respaldo | Lista de aceptación comprobada y pendientes identificados | 4–6 días |
| 5. Publicación y entrega | Dominio, HTTPS, despliegue, manuales y capacitación | Sitio público verificado y titular con accesos | 1–2 días |

Total orientativo: 22–34 días hábiles, aproximadamente 5–7 semanas de trabajo. Esta estimación sustituye la anterior basada en Filament e incluye construir y comprobar la interfaz administrativa propia. Esperas por dominio, soporte de hosting, validación documental o revisión de textos se contabilizan aparte. Una primera versión visual puede revisarse antes de terminar el panel avanzado.

## 8. Pruebas y aceptación

- Rutas ES/EN, idioma inválido, selector, canonical, hreflang y sitemap correctos; vistas previas y panel fuera de indexación.
- Catálogo inicial con 14 servicios únicos y cinco categorías; enlaces oficiales y horarios coherentes en ambos idiomas.
- Pruebas de permisos: un editor no publica, modifica usuarios ni accede a recursos ajenos por URL directa.
- Borradores invisibles al público; publicación y recuperación consistentes; invalidación de caché comprobada.
- Cargas inválidas rechazadas; originales privados, `.env`, documentos y respaldos inaccesibles por HTTP.
- Horarios sin solapamientos, últimas citas dentro del turno, excepciones válidas y zona horaria local de Piedras Negras comprobada.
- Prueba de recuperación de contraseña, segundo factor y registro de cambios.
- Pruebas de protección CSRF, sesiones, escape/saneamiento del contenido editable y validación en servidor; las restricciones no dependerán solo de ocultar botones.
- Comprobar búsqueda, filtros, paginación, errores de formulario, avisos de cambios sin guardar y navegación por teclado en las pantallas propias del panel.
- Revisión en Android, iPhone y escritorio; teclado, contraste, foco, zoom y textos alternativos. Registrar si alguna prueba en dispositivo físico queda pendiente.
- Medir carga móvil con Lighthouse y conexión limitada, corrigiendo imágenes y recursos pesados; objetivos de laboratorio LCP <= 2.5 s y CLS <= 0.1, sujetos a medición en el hosting. No prometer puntuaciones ni métricas de campo antes de disponer de tráfico.
- Prueba de respaldo/restauración y despliegue reversible; el titular puede editar horarios y publicar una traducción siguiendo el manual.

## 9. Pendientes editoriales y de publicación

Estos límites provienen del material entregado y deben reflejarse tanto en el sitio como en el panel:

- Mantener «Médico en medicina respiratoria y salud pulmonar» y la cédula de licenciatura 12731500. No cambiar la denominación pública a especialista/neumólogo sin resolver la restricción documentada.
- No publicar sello CNN-1956, acreditación RETMEDE ni beneficios de Línea Médica como obtenidos mientras no exista evidencia y autorización correspondientes.
- Confirmar sede y disponibilidad de procedimientos invasivos; conservar las condiciones de valoración, equipamiento y seguridad del brief.
- Revisar traducción inglesa y evitar implicar licencia médica de EE. UU., aceptación de seguros estadounidenses o resultados garantizados.
- Validar aviso de privacidad y tratamientos de datos realmente utilizados, incluido acceso administrativo, registros y medición si se activa.
- Confirmar dominio, correo público opcional y correo operativo para recuperación administrativa. No inventar cuentas.
- Instagram, precios y testimonios quedan fuera de publicación hasta contar con información aprobada.
- El JSON menciona estacionamiento gratuito reportado en Google Business; confirmarlo antes de añadirlo, pues el texto público entregado no lo afirma.

No se necesitan escaneos de documentos personales para comenzar el desarrollo. El médico o responsable designado revisará los textos profesionales y legales antes del lanzamiento. Se puede desarrollar con esos contenidos en borrador mientras se resuelven los pendientes.

## 10. Límites y entrega

El alcance comprende sitio institucional bilingüe y panel avanzado de contenido. Agenda clínica, expedientes, cobros en línea, recetas, portal de pacientes, integración con WhatsApp Business API y sincronización automática con Google Business requerirían especificación adicional. La facturación se comunica como disponibilidad del consultorio; no se implementa un sistema fiscal.

Entrega: código y archivos lock; guía de despliegue HostGator; manual del panel; inventario de recursos públicos; matriz de permisos; lista de comprobaciones; procedimiento de respaldo y restauración; acceso del titular al dominio, hosting, repositorio y cuentas de medición si se utilizan. Propuesta de mantenimiento: revisión mensual de dependencias, enlaces y respaldos, con incidencias de seguridad atendidas según necesidad.

Primer bloque de implementación: preparar `app-web/`, verificar versiones, instalar Laravel, configurar MySQL local, crear roles y modelo editorial, importar los 14 servicios y construir el acceso, tablero y editor de servicios del panel propio. Revisado ese patrón de interfaz, desarrollar el inicio bilingüe con el logotipo y fotografías originales y extender el panel a los demás módulos. El dominio pendiente no bloquea este trabajo.
