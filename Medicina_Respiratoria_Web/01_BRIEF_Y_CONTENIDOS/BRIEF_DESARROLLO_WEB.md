# BRIEF DE DESARROLLO WEB — MEDICINA RESPIRATORIA

**Versión de entrega:** 6 de noviembre de 2026  
**Destinatario:** programador / diseñador del sitio web  
**Propietario del proyecto:** Dr. Jesús García Espinoza  
**Estado:** especificación inicial; requiere validación antes de publicar.

## Resumen operativo

| Dato | Valor |
|---|---|
| Marca | Medicina Respiratoria |
| Médico | Dr. Jesús García Espinoza |
| Denominación pública | Médico en medicina respiratoria y salud pulmonar |
| Cédula de licenciatura | 12731500 |
| WhatsApp y teléfono | 878 114 6336 |
| WhatsApp clicable | https://wa.me/528781146336 |
| Dirección | Calle Argentina 119, Colonia Delicias, Consultorio 3, Piedras Negras, Coahuila, C. P. 26070 |
| Horarios | Lun, mar, jue, vie: 10:00–15:00 y 16:00–19:00 |
| Última cita en cada turno | 14:00 / 18:00 |
| Cerrado | Miércoles, sábado y domingo |
| Pago | Efectivo y transferencia; facturación disponible |
| Google Maps | https://maps.google.com/maps?cid=13020694073304218560 |
| Facebook | https://www.facebook.com/p/Dr-Jesus-Garcia-Medicina-Respiratoria-61573890430941/ |
| Reseñas Google | https://search.google.com/local/writereview?placeid=ChIJWWUW5oGLX4YRwPtU6EXMsrQ |
| Certificación Consejo | CNN-1956 referido por titular; obtener evidencia documental antes de publicar sello |
| RETMEDE | En trámite; no anunciar acreditación obtenida |

## Objetivo general y límites

Conseguir una página web profesional bilingüe, veloz, accesible y con identidad visual auténtica. El indicador principal será el clic hacia WhatsApp y las citas confirmadas. No generar una tienda electrónica, pasarela de pagos ni plataforma de expedientes médicos sin especificación y presupuesto adicionales.


## 1. Propósito del proyecto

- Sitio oficial propio de Medicina Respiratoria con control de dominio, alojamiento, cuentas y código por parte del titular.
- Objetivo principal de conversión: solicitar una cita por WhatsApp. Objetivos secundarios: comprobar legitimidad profesional, comunicar servicios reales, mostrar instalaciones y facilitar cómo llegar.
- Uso previsto: referencia desde Google Business y Facebook, soporte para trámites RETMEDE y posible promoción dirigida a visitantes de Eagle Pass (Texas) con un sitio bilingüe.
- No afirmar que el establecimiento cuenta con acreditación RETMEDE o beneficios de la Línea Médica mientras no estén formalmente concedidos.

## 2. Identidad y diseño — reglas obligatorias

- Usar el logotipo azul/gris original LOGO YISUS PNG.png de Ángel Hernández. Se facilitan también variantes originales blanca y negra. No redibujar, reinterpretar, generar por IA, modificar letras ni cambiar relaciones anatómicas del símbolo.
- Usar fotografías originales del médico, fachada y consultorio; conservar rostro, cuerpo, vestimenta, placa INER, señalética y arquitectura. No crear falsos consultorios ni fotografías de pacientes ficticios.
- Identidad editorial: blanco, azul marino, azul petróleo/cian sobrio, gris claro. Paleta orientativa de prototipo: #0D2B3E (azul marino), #057CA7 (azul medio), #26A0B9 (acento), #F8FAFB (fondo), #667984 (gris). Ajustar visualmente contra logo original, no contra imitaciones.
- Preferir tipografía sans serif limpia para UI y serif discreta en titulares si funciona; sin especificar la familia del logotipo como si estuviera identificada. El logo es una imagen original, no texto recreado.
- Máxima legibilidad, espacio en blanco, fotografías auténticas y diseño móvil primero. Evitar pulmones fluorescentes, humo exagerado, efectos genéricos de IA, iconos repetidos y banners saturados.

## 3. Arquitectura de información propuesta

- Inicio: encabezado con logotipo, mensaje breve, fotografía real del médico y CTA “Solicitar cita por WhatsApp”.
- Servicios: cinco categorías con fichas expandibles y descripción breve, sin duplicar nombres. Mantener en datos internos los 14 registros exactos de Google Business.
- El médico: fotografía real, formación UAdeC e INER/UNAM, cédula de licenciatura verificada y denominación pública aprobada.
- Consultorio e instalaciones: fotografías de fachada, interior y acceso al Consultorio 3; datos de accesibilidad.
- Ubicación y horarios: dirección, mapa, medios de pago, horario dividido y última cita en cada turno.
- Contacto: enlace wa.me, llamada telefónica y Facebook. Idioma ES/EN en menú.
- Pie: aviso de privacidad, política de cookies si se incorporan mediciones o servicios que las requieran y advertencia de que el sitio no es canal de urgencias.

## 4. Funcionalidad del mínimo viable

- Enlace de WhatsApp en hero, menú, servicios y contacto; deben apuntar al número +52 878 114 6336 y funcionar en móviles.
- Enlaces externos abiertos con rel="noopener noreferrer". Teléfono clicable tipo tel:+528781146336.
- Horario correcto: lunes, martes, jueves y viernes 10:00–15:00 y 16:00–19:00; miércoles, sábado y domingo cerrados; últimas citas 14:00 y 18:00.
- Dos idiomas con traducción revisada; metadata SEO/OG y etiquetas hreflang para es-MX y en-US.
- Sitio responsive, accesible con navegación por teclado, texto alternativo realista, contraste WCAG AA y respeto a prefers-reduced-motion.
- Optimización de imágenes: AVIF/WebP derivados para rendimiento, conservando fuentes originales sin retoques; lazy loading bajo el primer pliegue.
- SEO local: NAP coherente con Google Business, enlace Maps, JSON-LD MedicalClinic / Physician sujeto a validez de marcado, títulos y descripciones por página; Google Search Console y sitemap.xml.
- No almacenar datos clínicos por WhatsApp en el servidor del sitio ni solicitar historia clínica en formularios abiertos sin solución de privacidad y seguridad adecuada.

## 5. Publicación, propiedad y mantenimiento

- El titular debe ser dueño del nombre de dominio, cuenta de hosting, DNS, repositorio de código, Search Console, Analytics/Tag Manager (si se usa), certificado TLS y copias de respaldo.
- Entregar repositorio Git o ZIP de fuentes, instrucciones de despliegue y guía para actualizar horarios, fotos, textos y servicios sin depender del programador.
- Al entregar una versión funcional, probar desde Android, iPhone y escritorio, URLs y WhatsApp, pantallas pequeñas, tiempo de carga, encabezados SEO, mapa y enlaces.
- Verificar legalmente los textos públicos y el aviso de privacidad antes de publicar; no copiar documentos de identidad o títulos completos en el frontend.

## 6. Información que falta por definir

- Dominio deseado y proveedor de alojamiento; correo de contacto público (no inventado).
- Si se permitirá usar Instagram y confirmar enlace oficial.
- Si se mostrarán precios fijos; por ahora no hay tarifas aprobadas para publicar.
- Disponibilidad exacta y sede autorizada para procedimientos invasivos, y si requieren coordinación hospitalaria.
- Confirmar documento del Consejo Nacional de Neumología para exhibir la certificación CNN-1956 de forma verificable.
- Revisar requisitos específicos RETMEDE; no exhibir distintivos ni prometer cobertura, cruces o atención de seguros estadounidenses.
- Texto de aviso de privacidad y cookies validado por asesoría competente.

## 7. Servicios oficiales a registrar (14; sin duplicados)

1. **Consulta de medicina respiratoria:** Valoración médica para el diagnóstico, tratamiento y seguimiento de enfermedades respiratorias.
2. **Atención del asma:** Evaluación y control del asma, seguimiento del tratamiento y educación en el uso correcto de inhaladores, con revisión de la técnica inhalatoria.
3. **Atención de EPOC:** Evaluación y seguimiento de EPOC, síntomas, exacerbaciones y tratamiento; educación y revisión de técnica de inhaladores.
4. **Espirometría:** Prueba de función pulmonar para medir flujos y volúmenes respiratorios. Puede incluir evaluación broncodilatadora cuando exista indicación clínica.
5. **Evaluación de tos crónica:** Estudio médico de la tos persistente, mediante historia clínica, exploración y pruebas complementarias cuando se requieran.
6. **Evaluación de disnea:** Valoración clínica de la falta de aire, con identificación de posibles causas respiratorias y estudios necesarios según cada caso.
7. **Estudios respiratorios del sueño:** Valoración de ronquido y sospecha de apnea del sueño; poligrafía respiratoria domiciliaria, oximetría nocturna e interpretación de resultados según indicación clínica.
8. **Oxigenoterapia:** Evaluación de oxigenación, indicación y seguimiento de oxigenoterapia domiciliaria. Capacitación a pacientes y familiares en el manejo seguro de concentradores, tanques y accesorios.
9. **CPAP y BiPAP:** Evaluación y seguimiento del tratamiento con presión positiva, revisión de uso, tolerancia y respuesta clínica.
10. **Toracocentesis:** Procedimiento pleural diagnóstico o terapéutico, sujeto a valoración médica e indicación clínica.
11. **Colocación de sondas:** Colocación de sondas para procedimientos respiratorios, de acuerdo con la valoración clínica y el tipo de intervención.
12. **Biopsias pleurales:** Obtención de muestras de pleura para estudio diagnóstico cuando exista indicación médica.
13. **Broncoscopía:** Exploración endoscópica de las vías respiratorias para evaluación y procedimientos diagnósticos indicados.
14. **Evaluación de salud respiratoria ocupacional y empresarial:** Valoración respiratoria de trabajadores, espirometría ocupacional, evaluaciones para empresas e informes médicos de salud respiratoria según riesgos y exigencias del puesto.

## 8. Reglas de publicación clínica y profesional

- Denominación del médico en publicidad: **«Médico en medicina respiratoria y salud pulmonar»**. No cambiarla a «neumólogo» o «médico especialista en Neumología» en las piezas publicitarias mientras el titular mantenga la restricción actual vinculada al trámite de cédula de especialidad.
- La cédula verificada y autorizada para mostrar es la de licenciatura: **12731500**. No confundirla con la certificación del Consejo ni con una cédula de especialidad.
- Se dispone de constancia de conclusión de residencia INER y título de licenciatura; son documentos privados de verificación y no debe incluirse su escaneo íntegro en la web.
- La acreditación RETMEDE y las prestaciones de «Línea Médica» no se deben presentar como ya obtenidas.
- El sitio web debe incluir una advertencia sobre urgencias, sin convertir WhatsApp en canal de respuesta de emergencias.
- Los procedimientos invasivos quedan sujetos a evaluación individual, condiciones de seguridad, equipamiento y sede donde se realicen. No dar a entender disponibilidad inmediata ni en cualquier momento.
- Si en el futuro se incorporan fotos de pacientes, opiniones o casos clínicos, exigir autorización expresa y protección de datos; actualmente no se incluyen en esta entrega.

## 9. Estructura del paquete de entrega

- `01_BRIEF_Y_CONTENIDOS/`: brief `.docx` y `.md`, contenido español/inglés listo para revisión e información JSON.
- `02_LOGOS_Y_FOTOGRAFIAS_ORIGINALES/`: fotos y logotipos originales, copiados sin editar; sus SHA-256 se registran para integridad.
- `03_REFERENCIA_PROTOTIPO/`: prototipo HTML/CSS previo; guía visual para trabajo, NO versión final ni modelo de identidad inalterable.
- `04_DOCUMENTACION_Y_VERIFICACIONES/`: notas sobre documentos académicos y verificación de restricciones; NO hay INE, CURP, títulos completos ni cédulas escaneadas, para evitar compartir información personal innecesaria.

## 10. Criterios de aceptación antes de lanzar

- Verificar logotipo original y fotografías sin alteraciones de rostro o arquitectura.
- Todos los enlaces `wa.me`, teléfono, Maps, Facebook y navegación ES/EN funcionan.
- Horario y últimas citas se muestran sin ambigüedad en español e inglés.
- 14 prestaciones documentadas y agrupadas en cinco secciones, sin duplicados.
- Sitio funcional en Android, iPhone y escritorio; navegación con teclado y textos alternativos.
- El titular dispone de cuentas de dominio, alojamiento, repositorio y acceso administrativo.
- Información legal, privacidad y reclamos profesionales revisados antes de publicación.
- Dominio publicado y verificado; recién entonces se carga URL en Google Business y RETMEDE.
