<?php

namespace App\Services;

class ClinicStructuredData
{
    public function encode(array $clinic, array $hours, string $locale): string
    {
        $base = rtrim(config('app.url'), '/');
        $openingHours = [];
        foreach ($hours['week'] as $day => $schedule) {
            foreach ($schedule['closed'] ? [[]] : $schedule['sessions'] as $session) {
                $row = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'https://schema.org/'.ucfirst($day)];
                if (! $schedule['closed']) {
                    $row['opens'] = $session['start'];
                    $row['closes'] = $session['end'];
                }
                $openingHours[] = $row;
            }
        }
        $specialHours = [];
        foreach ($hours['exceptions'] as $exception) {
            if ($exception['date'] < now('America/Matamoros')->toDateString()) {
                continue;
            }
            foreach ($exception['closed'] ? [[]] : $exception['sessions'] as $session) {
                $row = ['@type' => 'OpeningHoursSpecification', 'validFrom' => $exception['date'], 'validThrough' => $exception['date']];
                if (! $exception['closed']) {
                    $row['opens'] = $session['start'];
                    $row['closes'] = $session['end'];
                }
                $specialHours[] = $row;
            }
        }
        $data = [
            '@context' => 'https://schema.org', '@type' => 'MedicalClinic', '@id' => $base.'/#clinic',
            'name' => $clinic['name'], 'url' => $base.'/',
            'description' => $clinic[$locale]['intro'],
            'telephone' => $clinic['phone'], 'address' => $clinic['address'].', '.$clinic['city'],
            'image' => $base.'/images/fachada.webp', 'logo' => $base.'/images/logo.png',
            'hasMap' => $clinic['maps'], 'sameAs' => [$clinic['facebook']],
            'openingHoursSpecification' => $openingHours,
            'employee' => ['@type' => 'Person', 'name' => $clinic['physician'], 'jobTitle' => $clinic[$locale]['designation']],
        ];
        if ($specialHours) {
            $data['specialOpeningHoursSpecification'] = $specialHours;
        }
        if (filled($clinic['email'] ?? null)) {
            $data['email'] = $clinic['email'];
        }

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
