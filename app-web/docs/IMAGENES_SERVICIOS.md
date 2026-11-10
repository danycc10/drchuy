# Ilustraciones para las tarjetas de servicios

Generadas con la herramienta integrada `image_gen`, sin CLI ni API externa configurada. Cinco imágenes editoriales por categoría, revisadas individualmente. Son ilustraciones decorativas; no representan fotografías del consultorio ni modelos concretos de equipos.

## Archivos finales

En `app-web/public/images/services/`, cada categoría tiene dos derivados WebP: `consultation`, `diagnostics`, `treatment`, `procedures` y `occupational`, con sufijos `-640.webp` (640 × 427) y `-960.webp` (960 × 640). Diez archivos en total, entre 7 y 19 KB cada uno. Se generaron desde los PNG originales mediante PHP/GD, conservando la composición y sin sobrescribir los originales. Las tarjetas usan `srcset`, dimensiones declaradas y carga diferida. La imagen corresponde a la categoría; el nombre y la descripción corresponden a cada servicio.

Estas ilustraciones forman parte de los archivos del sitio y se conservan como opción inicial. Cada servicio puede reemplazar su ilustración con una fotografía de la biblioteca: subirla en **Imágenes**, seleccionarla en **Servicios → Editar → Imagen del servicio**, guardar, revisar la vista previa y publicar. La selección es independiente por servicio, compartida entre sus dos idiomas y se conserva en cada versión del historial. La opción **Ilustración original de la categoría** recupera el recurso inicial. Los derivados de imágenes personalizadas se sirven desde la biblioteca privada cuando la versión del servicio está publicada y visible; las imágenes de borradores requieren acceso al panel.

## Prompts completos

### consultation

Use case: stylized-concept. Asset type: landscape category cover image for service cards on a serious respiratory medicine doctor's website. Create one polished editorial 3D still life representing medical consultation: a navy and muted teal stethoscope resting beside a plain cream consultation clipboard with subtle abstract lines, no readable text. Matte ceramic-like materials, subtle fine texture, soft daylight from upper left, warm off-white background, restrained pale sage accents, elegant realistic shadows, uncluttered centered composition with generous breathing room, landscape 3:2 frame, objects fully inside central 70% safe crop. Professional calm healthcare aesthetic, no people, no logos, no typography, no watermark, no invented clinical claims. This is an illustrative category cover, not a photo of an actual clinic.

### diagnostics

Referencia de estilo: la imagen `consultation` generada en esta entrega.

Use case: stylized-concept. Create ONE new landscape 3:2 cover image for a service category on a professional respiratory medicine website. The reference image is ONLY a style reference: retain its matte tactile ceramic-like materials, warm cream background, muted sage and navy palette, soft daylight from upper left and elegant shadows. Replace its composition and objects completely with this subject: Respiratory diagnostics and sleep: a small unbranded stylized spirometer with a simple mouthpiece, a folded cream paper with one abstract airflow curve and a subtle crescent moon shape behind. No numbers, no medical readings, no text. Center the whole still life within generous margins suitable for a card crop. Calm modern editorial 3D aesthetic, no logos, no watermark, no readable text, not a real clinic photograph. Do not create a collage or multiple panels.

### treatment

Referencia de estilo: la imagen `consultation` generada en esta entrega.

Use case: stylized-concept. Create ONE new landscape 3:2 cover image for a service category on a professional respiratory medicine website. The reference image is ONLY a style reference: retain its matte tactile ceramic-like materials, warm cream background, muted sage and navy palette, soft daylight from upper left and elegant shadows. Replace its composition and objects completely with this subject: Respiratory treatment and education: one stylized unbranded inhaler standing beside a simple respiratory mask and gently coiled tube, as calm illustrative objects rather than instructions for use. No people, no drug names or labels, no anatomy. Center the whole still life within generous margins suitable for a card crop. Calm modern editorial 3D aesthetic, no logos, no watermark, no readable text, not a real clinic photograph. Do not create a collage or multiple panels.

### procedures

Referencia de estilo: la imagen `consultation` generada en esta entrega.

Use case: stylized-concept. Create ONE new landscape 3:2 cover image for a service category on a professional respiratory medicine website. The reference image is ONLY a style reference: retain its matte tactile ceramic-like materials, warm cream background, muted sage and navy palette, soft daylight from upper left and elegant shadows. Replace its composition and objects completely with this subject: Respiratory medical procedures: a clean rounded medical instrument tray holding a single stylized flexible endoscope with its tube loosely curved, beside folded cream gauze. Non-graphic, no needles, no blades, no blood, no body parts, no people. Symbolic professional equipment illustration, not a technical diagram. Center the whole still life within generous margins suitable for a card crop. Calm modern editorial 3D aesthetic, no logos, no watermark, no readable text, not a real clinic photograph. Do not create a collage or multiple panels.

### occupational

Referencia de estilo: la imagen `consultation` generada en esta entrega.

Use case: stylized-concept. Create ONE new landscape 3:2 cover image for a service category on a professional respiratory medicine website. The reference image is ONLY a style reference: retain its matte tactile ceramic-like materials, warm cream background, muted sage and navy palette, soft daylight from upper left and elegant shadows. Replace its composition and objects completely with this subject: Occupational respiratory health: a pale sage safety helmet beside a navy stethoscope and a simple cream clipboard with an abstract airflow curve. No people, no industry logos, no writing. Center the whole still life within generous margins suitable for a card crop. Calm modern editorial 3D aesthetic, no logos, no watermark, no readable text, not a real clinic photograph. Do not create a collage or multiple panels.

## Validación

Inspección visual de las cinco imágenes generadas; comprobación de formatos y dimensiones de los derivados. La revisión visual de las tarjetas renderizadas en escritorio y móvil sigue pendiente por ausencia de un navegador conectado.

