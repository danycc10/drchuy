<?php

namespace App\Support;

class ContentSchema
{
    public static function definitions(): array
    {
        $text = fn (string $label, int $max = 200) => ['label' => $label, 'type' => 'text', 'max' => $max];
        $area = fn (string $label, int $max = 3000) => ['label' => $label, 'type' => 'textarea', 'max' => $max];
        $imageFields = $altFields = [];
        foreach (MediaSlots::definitions() as $slot => $details) {
            $imageFields[$slot.'_image_id'] = ['label' => $details['label'], 'type' => 'media', 'slot' => $slot, 'optional' => true];
            $altFields[$slot.'_alt'] = $text('Descripción de la imagen: '.$details['label'], 400);
        }

        return [
            'faq' => [
                'label' => 'Preguntas frecuentes', 'description' => 'Resuelve dudas prácticas antes de la cita. Ordena, oculta y revisa las respuestas en ambos idiomas.',
                'shared' => [], 'translated' => [
                    'faq_title' => $text('Título de la sección'), 'faq_intro' => $area('Introducción', 600),
                ],
            ],
            'media' => [
                'label' => 'Fotografías del sitio', 'description' => 'Selecciona fotografías del médico e instalaciones y describe cada imagen en español e inglés.',
                'owner_only' => true, 'shared' => $imageFields, 'translated' => $altFields,
            ],
            'home' => [
                'label' => 'Inicio y presentación', 'description' => 'Mensaje principal, botones y presentación de la atención.',
                'shared' => [], 'translated' => [
                    'hero' => $text('Título principal'), 'intro' => $area('Introducción', 600),
                    'appointment' => $area('Condiciones de la cita', 600),
                    'cta' => $text('Botón principal', 80), 'short_cta' => $text('Botón corto', 40),
                    'care_title' => $text('Título de presentación'), 'care_copy' => $area('Presentación del consultorio'),
                    'services_title' => $text('Título de servicios'), 'services_copy' => $area('Introducción de servicios', 600),
                ],
            ],
            'physician' => [
                'label' => 'Perfil profesional', 'description' => 'Biografía y datos profesionales; publicación exclusiva del propietario.',
                'owner_only' => true,
                'shared' => ['physician' => $text('Nombre público del médico', 150), 'license' => $text('Cédula de licenciatura', 20)],
                'translated' => [
                    'designation' => $text('Denominación pública autorizada'),
                    'bio' => $area('Formación profesional'), 'bio_second' => $area('Práctica clínica'),
                    'procedure_note' => $area('Condiciones de los procedimientos', 1000),
                ],
            ],
            'office' => [
                'label' => 'Consultorio', 'description' => 'Presentación de las instalaciones y accesibilidad confirmada.',
                'shared' => [], 'translated' => [
                    'office_title' => $text('Título de instalaciones'), 'office_copy' => $area('Instalaciones y accesibilidad'),
                ],
            ],
            'contact' => [
                'label' => 'Contacto y ubicación', 'description' => 'Número de contacto, ubicación, enlaces oficiales y medios de pago.',
                'shared' => [
                    'phone' => $text('Teléfono y WhatsApp en formato internacional', 16),
                    'address' => $text('Calle, colonia y consultorio', 300), 'city' => $text('Ciudad, estado y código postal'),
                    'maps' => ['label' => 'Enlace oficial de Google Maps', 'type' => 'url', 'max' => 1000],
                    'facebook' => ['label' => 'Enlace oficial de Facebook', 'type' => 'url', 'max' => 1000],
                    'email' => ['label' => 'Correo público (opcional)', 'type' => 'email', 'max' => 254, 'optional' => true],
                ],
                'translated' => ['contact_title' => $text('Título de contacto'), 'payment' => $area('Medios de pago y facturación', 600)],
            ],
            'seo' => [
                'label' => 'SEO y redes sociales', 'description' => 'Título y descripción que identifican el sitio en buscadores y enlaces compartidos.',
                'shared' => [], 'translated' => ['title' => $text('Título SEO', 200), 'description' => $area('Descripción SEO', 500)],
            ],
            'legal' => [
                'label' => 'Privacidad y avisos', 'description' => 'Aviso de privacidad revisado y mensaje de urgencias. Publica solo el propietario.',
                'owner_only' => true, 'shared' => [],
                'translated' => [
                    'disclaimer' => $area('Aviso de información general y urgencias', 1500),
                    'privacy_title' => $text('Título del aviso de privacidad'),
                    'privacy_content' => [...$area('Aviso de privacidad validado', 30000), 'draft_optional' => true],
                ],
            ],
            'hours' => [
                'label' => 'Horarios de atención', 'description' => 'Turnos semanales, últimas citas y excepciones por fecha.',
                'shared' => [], 'translated' => ['special_hours' => $text('Nota de horarios especiales', 400)],
            ],
        ];
    }

    public static function days(): array
    {
        return [
            'monday' => ['es' => 'Lunes', 'en' => 'Monday'],
            'tuesday' => ['es' => 'Martes', 'en' => 'Tuesday'],
            'wednesday' => ['es' => 'Miércoles', 'en' => 'Wednesday'],
            'thursday' => ['es' => 'Jueves', 'en' => 'Thursday'],
            'friday' => ['es' => 'Viernes', 'en' => 'Friday'],
            'saturday' => ['es' => 'Sábado', 'en' => 'Saturday'],
            'sunday' => ['es' => 'Domingo', 'en' => 'Sunday'],
        ];
    }

    public static function initial(string $key): array
    {
        $schema = self::definitions()[$key];
        $payload = ['shared' => [], 'es' => [], 'en' => []];
        foreach ($schema['shared'] as $field => $definition) {
            $payload['shared'][$field] = $definition['type'] === 'media' ? null : (config('clinic.'.$field) ?? '');
        }
        foreach (['es', 'en'] as $locale) {
            foreach ($schema['translated'] as $field => $definition) {
                $payload[$locale][$field] = config('clinic.'.$locale.'.'.$field)
                    ?? ($field === 'privacy_title' ? ($locale === 'es' ? 'Aviso de privacidad' : 'Privacy notice') : '');
            }
        }
        if ($key === 'faq') {
            $payload['es'] = ['faq_title' => 'Antes de tu visita', 'faq_intro' => 'Respuestas a las dudas más comunes sobre las citas y la atención en el consultorio.'];
            $payload['en'] = ['faq_title' => 'Before your visit', 'faq_intro' => 'Answers to common questions about appointments and visiting the office.'];
            $payload['items'] = [];
            foreach ([
                ['es' => '¿Cómo solicito una cita?', 'en' => 'How do I request an appointment?', 'answer' => 'appointment'],
                ['es' => '¿Qué medios de pago aceptan?', 'en' => 'Which payment methods are accepted?', 'answer' => 'payment'],
                ['es' => '¿Cómo se confirma la disponibilidad de un procedimiento?', 'en' => 'How is availability for a procedure confirmed?', 'answer' => 'procedure_note'],
            ] as $question) {
                $item = ['visible' => true];
                foreach (['es', 'en'] as $locale) {
                    $item[$locale] = ['question' => $question[$locale], 'answer' => config('clinic.'.$locale.'.'.$question['answer'])];
                }
                $payload['items'][] = $item;
            }
        }
        if ($key === 'media') {
            foreach (MediaSlots::definitions() as $slot => $details) {
                foreach (['es', 'en'] as $locale) {
                    $payload[$locale][$slot.'_alt'] = config('clinic.'.$locale.'.'.$details['alt']);
                }
            }
        }
        if ($key === 'hours') {
            $payload['week'] = [];
            foreach (self::days() as $day => $names) {
                $closed = in_array($day, ['wednesday', 'saturday', 'sunday'], true);
                $payload['week'][$day] = ['closed' => $closed, 'sessions' => $closed ? [] : [
                    ['start' => '10:00', 'end' => '15:00', 'last' => '14:00'],
                    ['start' => '16:00', 'end' => '19:00', 'last' => '18:00'],
                ]];
            }
            $payload['exceptions'] = [];
        }

        return $payload;
    }
}
