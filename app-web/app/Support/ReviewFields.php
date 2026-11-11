<?php

namespace App\Support;

use App\Models\{ContentBlock, Service};
use Illuminate\Support\Str;

class ReviewFields
{
    public static function label(Service|ContentBlock $target, string $path): string
    {
        $parts = explode('.', $path);
        $group = array_shift($parts);
        $language = ['es' => 'Español', 'en' => 'Inglés', 'shared' => 'Datos compartidos'][$group] ?? null;
        $field = $parts[0] ?? $group;
        if ($target instanceof Service && $group === 'image_position') {
            return 'Encuadre · '.(($parts[0] ?? '') === 'x' ? 'Posición horizontal' : 'Posición vertical');
        }
        if ($target instanceof ContentBlock) {
            $schema = ContentSchema::definitions()[$target->key];
            if ($language) {
                return $language.' · '.($schema[$group === 'shared' ? 'shared' : 'translated'][$field]['label'] ?? Str::headline($field));
            }
            if ($group === 'items') {
                return 'Pregunta '.((int) $parts[0] + 1).' · '.implode(' · ', array_map(fn ($part) => ['es' => 'Español', 'en' => 'Inglés', 'question' => 'Pregunta', 'answer' => 'Respuesta', 'visible' => 'Visible'][$part] ?? Str::headline($part), array_slice($parts, 1)));
            }
            if (in_array($group, ['week', 'exceptions'], true)) {
                $name = $group === 'week' ? ContentSchema::days()[$parts[0]]['es'] : 'Fecha especial '.((int) $parts[0] + 1);
                $terms = ['closed' => 'Cerrado', 'sessions' => 'Turnos', 'start' => 'Inicio', 'end' => 'Fin', 'last' => 'Última cita', 'date' => 'Fecha', 'es_note' => 'Nota en español', 'en_note' => 'Nota en inglés'];

                return $name.' · '.implode(' · ', array_map(fn ($part) => is_numeric($part) ? (string) ((int) $part + 1) : ($terms[$part] ?? Str::headline($part)), array_slice($parts, 1)));
            }
        }
        $labels = ['name' => 'Nombre', 'description' => 'Descripción', 'visible' => 'Visible', 'position' => 'Orden', 'image_id' => 'Imagen del servicio'];

        return ($language ? $language.' · ' : '').($labels[$field] ?? Str::headline($field));
    }
}
