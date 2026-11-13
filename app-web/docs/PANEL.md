# Uso del panel — primera entrega

## Acceso

Crear la primera cuenta propietaria desde una terminal en la carpeta de la aplicación con `php artisan app:create-admin`. El comando pide contraseña de al menos 12 caracteres con letras y números, sin mostrarla. No pasar contraseñas como argumentos ni guardarlas en el repositorio. Las cuentas del equipo se administran después en **Usuarios**.

Entrar a `/admin` con el correo y la contraseña elegidos. El panel está pensado para una sola persona, el médico: cualquier cuenta activa edita y publica todo el contenido en un solo paso, sin revisiones ni aprobaciones intermedias. Los roles se conservan únicamente para **Usuarios** y **Mantenimiento**, que solo ve el `owner`.

El resumen ofrece accesos directos a servicios, orden del catálogo, imágenes, horarios y contacto, y a la vista del sitio en celular, tablet y escritorio. Distingue servicios visibles de los publicados como ocultos y enlaza a sus filtros. La lista **Pendiente de publicar** aparece solo si existe alguna versión guardada que el sitio todavía no muestra. La actividad reciente permanece al final.

La **Guía del panel** explica editar, guardar y publicar, y comprobar el resultado en tres pasos. Puede cerrarse o terminarse; el navegador recuerda la preferencia por cuenta. Para consultarla de nuevo, usar **Guía del panel** en el menú o **Abrir guía del panel** en el resumen. Funciona como lectura completa sin JavaScript y como recorrido con botones cuando está disponible; no publica ni modifica contenido.

La recuperación usa enlaces de Laravel. En desarrollo, `MAIL_MAILER=log` guarda el mensaje en el log privado en lugar de enviarlo. En producción se necesita correo configurado y probado. El log puede contener el enlace de recuperación: no compartirlo ni subirlo a Git.

En móvil, **Menú** abre la navegación. Puedes cerrarla con **Cerrar menú**, la tecla Escape o pulsando fuera. El foco del teclado permanece dentro mientras está abierta y vuelve al botón al cerrar. La sección actual se identifica también para lectores de pantalla.

## Inicio: pendiente de publicar

Con la publicación en un solo paso, lo único que puede quedar pendiente es una versión guardada que el sitio aún no muestra: el contenido inicial de una instalación nueva (servicios importados, preguntas frecuentes de ejemplo) o un guardado interrumpido. El resumen las lista con **Abrir y publicar**; dentro del editor, el aviso **Publicar ahora** las aplica tal cual, o basta con modificar algo y guardar. Cuando todo está en el sitio, el resumen lo indica y no hay tareas.

## Usuarios

Solo el propietario ve este módulo y puede acceder a sus acciones. Puede buscar por nombre/correo, crear cuentas, cambiar nombre/rol y activar o desactivar acceso. Cada cambio requiere su contraseña actual y se registra en Actividad. El correo de una cuenta existente no se cambia desde este módulo.

Al crear una cuenta se genera una contraseña aleatoria que no se muestra. Abrir la cuenta y usar **Generar enlace privado**: copiarlo y compartirlo directamente con su titular para que elija una contraseña. El enlace usa el dominio de `APP_URL`, vence según la configuración de Laravel (60 minutos inicialmente), reemplaza los anteriores y funciona una sola vez. El panel muestra el enlace únicamente tras generarlo; no envía correo con esta acción. La recuperación por correo sigue disponible desde el acceso cuando SMTP está configurado.

Cambiar el rol o desactivar/reactivar una cuenta invalida sus sesiones anteriores y los enlaces de contraseña pendientes. La desactivación conserva su historial y bloquea cualquier edición o publicación en curso. No se permite quitarse el rol de propietario ni desactivar la cuenta propia, y debe quedar un propietario activo. Crear otro propietario concede control completo del equipo. Cualquier cuenta activa, sea cual sea su rol, puede editar y publicar el contenido del sitio: crear cuentas solo para personas de confianza.

## Mi seguridad: verificación en dos pasos

1. Abrir **Mi seguridad** e introducir la contraseña actual.
2. Agregar una cuenta en una aplicación autenticadora compatible con TOTP. Escanear el QR o introducir la clave manual; elegir códigos basados en tiempo. El QR se genera dentro del sitio.
3. Introducir el código de seis dígitos. La configuración vence en diez minutos y la protección no se activa hasta confirmar un código correcto.
4. Copiar los diez códigos de recuperación en un gestor de contraseñas o guardarlos fuera de este dispositivo. Se muestran una sola vez; cada uno sirve para un único acceso o cambio de seguridad.

En el siguiente acceso se pedirá primero correo/contraseña y después el código de la aplicación o uno de recuperación. El segundo paso vence en cinco minutos. Un código TOTP aceptado no puede reutilizarse: esperar al siguiente si se acaba de usar. Existen límites de intentos por cuenta y por ruta. Mantener la hora automática del teléfono y del servidor.

Para renovar códigos de recuperación o desactivar la protección, se necesitan contraseña y un código nuevo. Estas acciones cierran las otras sesiones; renovar anula todos los códigos anteriores. Recuperar la contraseña no desactiva el segundo factor. El propietario puede ver qué cuentas lo activaron, pero no consultar sus claves ni desactivarlo desde Usuarios.

Si se pierden tanto el teléfono como todos los códigos, un operador con acceso al servidor puede ejecutar `php artisan app:reset-two-factor`, verificar la identidad del titular por un medio independiente y confirmar la cuenta. Es una recuperación excepcional que queda en Actividad, cierra todas las sesiones y anula los enlaces de contraseña pendientes. El titular debe configurar otra vez la protección. No existe un enlace público para omitirla.

La implementación utiliza [RobThree/TwoFactorAuth](https://github.com/RobThree/TwoFactorAuth) y [BaconQrCode](https://github.com/Bacon/BaconQrCode). Las claves TOTP se cifran con `APP_KEY`; los códigos de recuperación se guardan como hashes. No se registran claves o códigos en la bitácora. Respaldar y conservar `APP_KEY` de forma privada junto con los procedimientos de restauración.

## Editar un servicio

1. Abrir **Servicios** y buscar por nombre, categoría o estado.
2. Pulsar **Editar** y completar nombre/descripción en las pestañas **Español** e **Inglés**. Cambiar de pestaña conserva lo escrito; las flechas del teclado permiten recorrerlas.
3. Ajustar orden, imagen y visibilidad si corresponde.
4. Pulsar **Guardar y publicar** en la barra que permanece visible al desplazarse. El sitio se actualiza al instante en ambos idiomas; la barra indica la fecha de la última actualización y si hay cambios sin guardar.
5. Comprobar el resultado con **Ver cómo se ve**, que abre la vista de celular, tablet y escritorio centrada en ese servicio. Si algo no convence, corregirlo y guardar de nuevo, o restaurar una versión anterior desde el historial.

Para ocultar un servicio, desmarcar **Mostrar este servicio en el sitio** y guardar. Un servicio oculto conserva su historial y puede recuperarse. El contador de servicios publicados incluye las versiones cuya visibilidad es «Oculto».

Las validaciones que quedan protegen el sitio público, no a un revisor: nombre y descripción en ambos idiomas, información para la visita en los dos idiomas o en ninguno, horarios coherentes, enlaces HTTPS y aviso de privacidad completo. Los errores enlazan al campo correspondiente y abren su idioma; los textos largos muestran un contador de caracteres. Tras un error, lo escrito sigue disponible para corregirlo y guardarlo, y el sitio no cambia hasta que el guardado es válido.

Las acciones de publicar una versión pendiente, restaurar versiones, eliminar fotografías y cambiar la verificación en dos pasos muestran una confirmación con el diseño del panel. Identifica la versión, fotografía o cuenta afectada. **Cancelar** o Escape vuelve al control de origen y conserva los datos. Al quitar una pregunta, el cuadro aclara que el cambio se aplicará al guardar. El aviso al abandonar cambios sin guardar sigue siendo el propio del navegador.

Estas ayudas se activan con JavaScript. Sin él, ambos idiomas y los botones siguen disponibles, y el servidor conserva las validaciones.

Si otra sesión guardó cambios desde que se abrió el editor, se rechaza la edición antigua. Copiar los cambios pendientes y recargar antes de continuar.

## Historial de versiones

Cada guardado conserva una versión con autor y fecha. Abrir una versión desde el historial del servicio o del bloque. **Restaurar esta versión** crea una copia nueva y la muestra en el sitio de inmediato; la versión que estaba publicada se conserva en el historial y puede restaurarse a su vez. La actividad registra quién hizo cada cambio.

## Vista previa

**Vista previa** y **Ver cómo se ve** muestran la última versión guardada en ES/EN y requieren autenticación. Como guardar publica, coinciden con el sitio público salvo por las versiones pendientes de publicar y por los servicios ocultos, que solo aparecen en la vista privada con una advertencia explícita. El sitio público `/` muestra solo las versiones publicadas y visibles; el selector del encabezado cambia el idioma sin agregar prefijos ni parámetros a la URL. La importación inicial deja los servicios pendientes de publicar. En este entorno local, los 14 servicios del catálogo original se publicaron por solicitud del titular; eso no despliega el sitio a HostGator.

La vista adaptable ofrece anchos de 390, 768 y 1280 px, permite elegir idioma y página, desplazarse y actualizarla después de guardar. Es una comprobación del diseño a distintos anchos, no una emulación completa de dispositivos. Las pantallas de edición siguen bloqueadas para marcos; solo las vistas previas permiten mostrarse dentro del mismo sitio. Los cambios sin guardar no aparecen.

Al abrir la vista desde un servicio, se selecciona su categoría, se abre su descripción y se destaca su tarjeta. El servicio se conserva al cambiar de idioma y en la vista adaptable. Si está oculto, se muestra solo en esa vista privada; no se cambia su visibilidad ni se expone en el sitio público.

Los servicios se listan por categoría. Cada nombre tiene un botón **Solicitar servicio**, visible sin abrir la descripción, que abre WhatsApp con el nombre del servicio y una solicitud de disponibilidad y cómo agendar. El mensaje usa el idioma elegido y el teléfono del contenido público; el visitante puede editarlo antes de enviarlo. La descripción se despliega por separado. El buscador filtra nombre, descripción y categoría sin perder el botón de solicitud. Ocultar un servicio requiere desmarcar su visibilidad y guardar desde el panel.

En **Servicios → Ordenar servicios**, elegir una categoría completa y mover sus tarjetas por arrastre o con **Subir/Bajar** (también disponibles con teclado y en celular). **Guardar orden** crea una versión solo para los servicios cuyo número cambia y publica el nuevo orden en el catálogo al instante; conserva textos e imágenes. Si alguien editó un servicio mientras se ordenaba, se rechaza el conjunto para evitar sobrescribir cambios: recargar y repetir. Los filtros distinguen sin publicar, visibles y ocultos.

El editor incluye **Información para la visita** en ambos idiomas: preparación previa, duración aproximada y qué llevar. Son campos opcionales que deben contener indicaciones confirmadas por el médico. Si se completa uno, completar también su traducción; dejar ambos vacíos para omitirlo del sitio. Se muestran dentro de **Conocer el servicio**, después de la descripción, en cuanto se guardan. También forman parte del historial. No se agregaron indicaciones médicas nuevas al catálogo existente.

El catálogo se presenta en tarjetas con una ilustración por categoría, nombre, **Conocer el servicio** y la solicitud por WhatsApp. Se adapta a tres columnas en escritorio, dos en tablet y una en móvil. La primera categoría disponible queda seleccionada al iniciar y siempre hay una categoría activa; se eliminó la opción Todos. El buscador y el contador se limitan a esa categoría. **Limpiar búsqueda**, **Ver servicios de esta categoría** y Escape limpian el texto sin cambiar la selección. Las tarjetas y sus descripciones siguen disponibles sin JavaScript. El movimiento al pasar el puntero respeta la preferencia de movimiento reducido.

En celular, las categorías usan un selector compacto sincronizado con los botones de escritorio. La búsqueda ocupa el ancho disponible y los detalles usan texto de 16 px. La barra de contacto reserva espacio según su altura real, incluidos textos que ocupen más de una línea; se oculta mientras el buscador tiene el foco para dejar espacio al teclado y vuelve al salir del campo. La comprobación visual en dispositivos reales continúa pendiente.

Las cinco ilustraciones y sus prompts se documentan en [IMAGENES_SERVICIOS.md](IMAGENES_SERVICIOS.md). Se usan como imagen inicial por categoría. Cada servicio puede tener una imagen propia desde el panel:

1. Abrir **Servicios → Editar → Imagen del servicio**. Elegir una imagen existente o escribir un nombre, elegir el archivo y pulsar **Subir y seleccionar** sin salir del editor. La carga requiere JavaScript; la biblioteca sigue disponible como alternativa.
2. Ajustar **Posición horizontal** y **Posición vertical** mirando la vista de la tarjeta. Se conserva el original; el encuadre se aplica al mismo servicio en ambos idiomas y se guarda en su historial. Una imagen con las mismas proporciones que la tarjeta puede no necesitar desplazamiento.
3. Pulsar **Guardar y publicar**: la tarjeta del sitio usa la imagen y su encuadre de inmediato.

El selector visual muestra miniaturas, un buscador por nombre y una marca en la imagen seleccionada. Buscar no modifica la selección; **Escape** limpia la búsqueda. **Elegir imagen por nombre** conserva el selector tradicional y funciona sin JavaScript. Al subir una imagen dentro del editor, se agrega a las miniaturas y se selecciona automáticamente; aún hay que guardar.

La carga muestra el progreso de transferencia y después espera la optimización del servidor. Solo confirma la selección cuando recibe una respuesta válida. Ante un error conserva el archivo, su nombre y los textos del servicio para **Reintentar carga**. Si se pierde la conexión o vence el tiempo de espera, revisar primero la biblioteca: el servidor puede haber guardado la imagen aunque no llegara la respuesta. Durante la carga se bloquean envíos duplicados y el guardado.

Para volver a la imagen inicial, seleccionar **Ilustración original de la categoría** y guardar. Cambiar la imagen de un servicio no cambia los demás. Las tarjetas recortan horizontalmente la fotografía; conviene usar una imagen horizontal con el motivo centrado. La imagen forma parte del historial y se recupera al restaurar una versión. La biblioteca no permite eliminar imágenes utilizadas en cualquier versión de servicios, incluido el historial.

La preferencia pública se guarda en la sesión y comienza en español para una visita nueva. El aviso usa `/aviso-de-privacidad` en ambos idiomas. Los enlaces públicos antiguos con `/es` o `/en` redirigen a las direcciones limpias conservando el idioma solicitado. La vista previa privada mantiene rutas por idioma para poder revisar ambos idiomas de forma independiente.

El menú público resalta la sección que se está leyendo, también en las vistas previas. Horarios se distingue de Ubicación cuando entra en la zona de lectura. Las anclas dejan espacio para el encabezado según su altura; desplazar la página no cambia la dirección ni el foco del teclado. Se mantiene la preferencia de movimiento reducido.

## Contenido y horarios

Desde **Contenido y horarios** puedes editar inicio, perfil profesional, instalaciones, contacto, SEO, privacidad, horarios, fotografías y preguntas frecuentes. Todos usan el mismo flujo: editar, **Guardar y publicar**, comprobar con **Ver cómo se ve** y, si hace falta, restaurar una versión del historial. Los datos compartidos se editan una vez; los textos tienen campos ES/EN.

El bloque **SEO y redes sociales** alimenta el título y la descripción de la página, las etiquetas Open Graph y Twitter para enlaces compartidos y la descripción de los datos estructurados. En **Contacto**, el campo de ciudad admite «Ciudad, Estado, C. P. 00000»: así se obtienen localidad, estado y código postal para buscadores; si se escribe de otra forma, el texto se usa tal cual como localidad. El retrato publicado del médico es la imagen de los enlaces compartidos. El sitio incluye iconos derivados del isotipo del logo (pestaña, favoritos y pantalla de inicio), un `sitemap.xml` con la fecha de la última publicación y una página de error 404 con el mismo diseño, en el idioma preferido del navegador, con regreso al inicio y solicitud de cita.

Cualquier cuenta activa publica todos los bloques, incluidos perfil profesional, avisos legales y fotografías. El tablero cuenta las versiones guardadas que el sitio todavía no muestra.

Para contacto, introduce el teléfono con `+` y código de país. Ese único valor genera teléfono visible, enlace de llamada y WhatsApp. Los enlaces de ubicación y Facebook deben usar HTTPS y sus dominios oficiales. El correo público es opcional; no es el correo privado de acceso al panel.

Los botones generales de cita del sitio abren un mismo mensaje preparado en el idioma elegido, editable en WhatsApp. La sección de contacto explica que se solicita disponibilidad y se espera la confirmación de fecha y hora por el consultorio; abrir WhatsApp no reserva ni envía un mensaje automáticamente. Las consultas de servicios conservan el nombre del servicio en su mensaje.

La ficha **Para tu visita** reúne dirección, teléfono, pagos y horarios, incluidas las fechas especiales vigentes. **Copiar dirección** conserva calle y ciudad; si el navegador no permite copiar, selecciona la dirección y explica cómo hacerlo manualmente. **Imprimir ficha** abre la impresión del navegador solo al pulsarlo y prepara esos datos con fecha de consulta. El diseño de impresión no incluye el resto de las secciones. Ambos botones requieren JavaScript; los datos, la llamada y el enlace para llegar siguen disponibles sin él.

La ficha pública usa las versiones publicadas de Contacto y Horarios. La vista previa usa las últimas versiones guardadas, lo indica en la ficha y omite el botón de impresión. Editar o imprimir estos datos no confirma una cita ni cambia la disponibilidad del consultorio.

## Preguntas frecuentes

Abrir **Contenido y horarios → Preguntas frecuentes**. Se incluyen tres preguntas iniciales sobre citas, pagos y disponibilidad de procedimientos, con respuestas tomadas de los textos del consultorio. En una instalación nueva quedan pendientes de publicar hasta que el médico las confirme con **Publicar ahora** o las edite y guarde.

1. Editar el título y la introducción en las pestañas ES/EN.
2. Completar cada pregunta y respuesta en ambos idiomas. Se permiten hasta 12 preguntas, con 200 caracteres por pregunta y 1,500 por respuesta.
3. Usar **Subir**, **Bajar** o **Quitar** para organizar la lista. Desmarcar **Mostrar en el sitio** para conservar una pregunta oculta. Estas acciones requieren JavaScript; editar preguntas existentes sigue siendo posible sin él.
4. Pulsar **Guardar y publicar** y comprobar la sección con **Ver cómo se ve**.

La sección pública usa respuestas desplegables accesibles sin JavaScript, respeta la preferencia de movimiento reducido y conserva `#faq` al cambiar de idioma. Solo muestra preguntas visibles de la versión publicada. Guardar una lista vacía o sin preguntas visibles oculta la sección y su acceso desde la portada. Restaurar una versión del historial la vuelve a mostrar en el sitio de inmediato.

Las respuestas admiten texto simple y saltos de línea. No introducir indicaciones clínicas ni instrucciones de preparación sin confirmación del médico. Si cambian las condiciones de citas o pagos en otros bloques, revisar también las respuestas relacionadas: cada bloque conserva su propio historial.

## Horarios y fechas especiales

Define hasta dos turnos por día y la última cita de cada turno. Marca **Cerrado** cuando no haya atención. La última cita debe ser igual o posterior a la apertura y anterior al cierre; no se permiten turnos solapados ni que atraviesen medianoche.

Las excepciones por fecha reemplazan el horario de ese día. Puedes agregar hasta 20 fechas, con cierre completo o turnos especiales. Las notas son opcionales, pero si incluyes una debe estar en ambos idiomas. El sitio muestra las fechas actuales y futuras; elimina las antiguas desde el editor cuando ya no se necesiten.

La misma versión publicada alimenta horarios visibles, datos estructurados para buscadores y el estado **Abierto ahora** / **Cerrado ahora** que aparece en la ficha de visita y junto a **Ver horarios** en la portada. El estado se calcula al cargar la página con la hora de Piedras Negras, indica la hora de cierre o la próxima apertura (hoy, mañana, día de la semana o fecha dentro de dos semanas) y resalta el día actual; las excepciones por fecha tienen prioridad sobre la semana. No se ofrecen reservas automáticas y la ficha impresa omite el estado en vivo.

## Privacidad

El aviso inicial está vacío. Pega el texto validado en ambos idiomas y pulsa **Guardar y publicar**: el aviso se guarda y aparece en el sitio únicamente cuando está completo en español e inglés; con un idioma vacío el guardado se rechaza y el sitio no cambia. Hasta entonces no se muestra un enlace de privacidad al visitante. El aviso admite texto simple con saltos de línea; cualquier HTML se muestra como texto, sin ejecutarse.

## Biblioteca de imágenes

1. Abrir **Imágenes** en el menú y pulsar **Subir fotografía**.
2. Darle un nombre descriptivo y elegir un JPG, PNG o WebP de hasta 8 MB, 12 megapíxeles y 8,000 píxeles por lado. Los límites de memoria del servidor también pueden exigir una imagen menor.
3. Subirla. Se conserva el original, se corrige la orientación de fotos JPEG y se crean un WebP de hasta 1,600 píxeles por lado y una miniatura. Los derivados conservan transparencia y no llevan metadatos EXIF del original.
4. Abrir **Fotografías del sitio**. Seleccionar imágenes para el retrato del médico, interior, fachada y entrada. El retrato se utiliza en portada, perfil y vista previa social.
5. Ajustar las descripciones en Español e Inglés para que correspondan a cada fotografía y pulsar **Guardar y publicar**. Subir una fotografía a la biblioteca no modifica por sí solo la web; seleccionarla y guardar sí.

La biblioteca permite buscar por nombre/archivo y filtrar por uso. Todas las cuentas activas pueden subir imágenes y usarlas en el sitio. Propietarios y administradores pueden descargar originales y eliminar imágenes sin uso. Si una imagen aparece en cualquier versión guardada, no se elimina: se conserva para recuperar el historial. Cambiar una fotografía no borra la anterior.

La opción **Fotografía original del sitio** conserva la imagen inicial del paquete del consultorio. Los originales de ese paquete y los logos no se reemplazan al subir archivos. El orden de las tres fotografías corresponde a interior, fachada y entrada.

Las cargas se guardan fuera de la raíz pública. Las imágenes de versiones no publicadas y los originales requieren acceso al panel. Solo los derivados asociados a la versión publicada están disponibles mediante la ruta pública; pueden permanecer en la caché del navegador hasta cinco minutos después de un reemplazo.

Consultar `ESTADO_IMPLEMENTACION.md` en la raíz del repositorio para los siguientes módulos y la revisión pendiente de lanzamiento.

## Mantenimiento

Disponible solo para el propietario. Muestra el último respaldo completado, las verificaciones de integridad, los ensayos de restauración y el historial de fallas/retención. **Descargar contenido** exporta textos y versiones de servicios/bloques y referencias de imágenes; incluye todas las versiones guardadas, pero no cuentas, contraseñas ni archivos de fotografías. Es un documento privado de consulta, no una copia completa para restaurar.

Los respaldos cifrados y la recuperación se operan desde consola. Consultar [RESPALDOS.md](RESPALDOS.md). La programación necesita cron y la copia externa sigue pendiente de configurar.
