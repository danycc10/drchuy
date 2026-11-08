<?php

namespace App\Services;

use App\Support\ContentSchema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class ContentValidator
{
    public function validate(string $key, array $payload, bool $publishing = false): array
    {
        $schema = ContentSchema::definitions()[$key];
        $rules = [];
        foreach (['shared' => $schema['shared'], 'es' => $schema['translated'], 'en' => $schema['translated']] as $group => $fields) {
            $rules[$group] = empty($fields) ? 'sometimes|array|size:0' : 'required|array:'.implode(',', array_keys($fields));
            foreach ($fields as $field => $definition) {
                $required = ! ($definition['optional'] ?? false) && ! (($definition['draft_optional'] ?? false) && ! $publishing);
                $rules[$group.'.'.$field] = [$required ? 'required' : 'nullable', 'string', 'max:'.$definition['max']];
                if ($definition['type'] === 'email') {
                    $rules[$group.'.'.$field][] = 'email';
                }
                if ($definition['type'] === 'url') {
                    $rules[$group.'.'.$field][] = 'url:https';
                }
            }
        }
        if ($key === 'contact') {
            $rules['shared.phone'][] = 'regex:/^\+[1-9][0-9]{7,14}$/';
        }
        if ($key === 'physician') {
            $rules['shared.license'][] = 'regex:/^[0-9]{5,20}$/';
        }
        if ($key === 'hours') {
            $rules['week'] = 'required|array:'.implode(',', array_keys(ContentSchema::days())).'|size:7';
            foreach (ContentSchema::days() as $day => $names) {
                $rules['week.'.$day] = 'required|array:closed,sessions';
            }
            $rules['week.*.closed'] = 'required|boolean';
            $rules['week.*.sessions'] = 'present|array|max:2';
            $rules['week.*.sessions.*'] = 'array:start,end,last';
            $rules['week.*.sessions.*.start'] = 'nullable|date_format:H:i';
            $rules['week.*.sessions.*.end'] = 'nullable|date_format:H:i';
            $rules['week.*.sessions.*.last'] = 'nullable|date_format:H:i';
            $rules['exceptions'] = 'present|array|max:20';
            $rules['exceptions.*'] = 'array:date,closed,sessions,es_note,en_note';
            $rules['exceptions.*.date'] = 'required|date_format:Y-m-d|distinct';
            $rules['exceptions.*.closed'] = 'required|boolean';
            $rules['exceptions.*.sessions'] = 'present|array|max:2';
            $rules['exceptions.*.sessions.*'] = 'array:start,end,last';
            foreach (['start', 'end', 'last'] as $part) {
                $rules['exceptions.*.sessions.*.'.$part] = 'nullable|date_format:H:i';
            }
            $rules['exceptions.*.es_note'] = 'nullable|string|max:200';
            $rules['exceptions.*.en_note'] = 'nullable|string|max:200';
        }
        $validator = Validator::make($payload, $rules, [
            'required' => 'Completa este campo.',
            'max' => 'Este campo excede el límite de :max.',
            'email' => 'Introduce un correo válido.',
            'url' => 'Utiliza un enlace HTTPS válido.',
            'regex' => 'El formato no es válido.',
            'date_format' => 'El formato debe ser :format.',
            'distinct' => 'No repitas la misma fecha.',
        ]);
        $validator->after(function (ValidationValidator $validator) use ($key, $payload) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($key === 'contact') {
                $allowed = [
                    'maps' => ['maps.google.com', 'www.google.com', 'google.com', 'maps.app.goo.gl'],
                    'facebook' => ['facebook.com', 'www.facebook.com'],
                ];
                foreach ($allowed as $field => $hosts) {
                    $parts = parse_url($payload['shared'][$field]);
                    if (! in_array(strtolower($parts['host'] ?? ''), $hosts, true) || isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && $parts['port'] !== 443)) {
                        $validator->errors()->add('shared.'.$field, 'Usa el enlace oficial del servicio indicado.');
                    }
                }
            }
            if ($key === 'hours') {
                foreach ($payload['week'] as $day => $hours) {
                    $this->checkSessions($validator, $hours, 'week.'.$day);
                }
                foreach ($payload['exceptions'] as $index => $hours) {
                    $this->checkSessions($validator, $hours, 'exceptions.'.$index);
                    if (filled($hours['es_note'] ?? null) !== filled($hours['en_note'] ?? null)) {
                        $validator->errors()->add('exceptions.'.$index.'.en_note', 'Si agregas una nota, complétala en ambos idiomas.');
                    }
                }
            }
        });
        $data = $validator->validate();
        $data['shared'] ??= [];
        if ($key === 'hours') {
            foreach ($data['week'] as &$day) {
                $day = $this->normalizeDay($day);
            }
            unset($day);
            $data['exceptions'] = array_values(array_map(fn ($day) => $this->normalizeDay($day), $data['exceptions']));
        }

        return $data;
    }

    private function checkSessions(ValidationValidator $validator, array $day, string $prefix): void
    {
        if ((bool) $day['closed']) {
            return;
        }
        $sessions = array_filter($day['sessions'], fn ($session) => count(array_filter($session, fn ($value) => filled($value))) > 0);
        if (empty($sessions)) {
            $validator->errors()->add($prefix.'.sessions', 'Agrega al menos un turno o marca el día como cerrado.');
            return;
        }
        $previousEnd = null;
        foreach ($sessions as $index => $session) {
            if (blank($session['start'] ?? null) || blank($session['end'] ?? null) || blank($session['last'] ?? null)) {
                $validator->errors()->add($prefix.'.sessions.'.$index, 'Cada turno necesita inicio, fin y última cita.');
                continue;
            }
            if ($session['start'] >= $session['end'] || $session['last'] < $session['start'] || $session['last'] >= $session['end']) {
                $validator->errors()->add($prefix.'.sessions.'.$index, 'El turno debe terminar después de iniciar; la última cita debe estar dentro del turno y antes del cierre.');
            }
            if ($previousEnd !== null && $session['start'] < $previousEnd) {
                $validator->errors()->add($prefix.'.sessions.'.$index, 'Los turnos deben estar ordenados y no solaparse.');
            }
            $previousEnd = $session['end'];
        }
    }

    private function normalizeDay(array $day): array
    {
        $day['closed'] = (bool) $day['closed'];
        $day['sessions'] = $day['closed'] ? [] : array_values(array_filter($day['sessions'], fn ($session) => filled($session['start'] ?? null)));

        return $day;
    }
}
