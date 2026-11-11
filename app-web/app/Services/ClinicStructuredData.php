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
        $physician = [
            '@type' => 'Person', '@id' => $base.'/#physician', 'name' => $clinic['physician'], 'jobTitle' => $clinic[$locale]['designation'],
            'image' => $clinic['images']['doctor']['url'] ?? $base.'/images/doctor.webp', 'worksFor' => ['@id' => $base.'/#clinic'],
        ];
        if (filled($clinic['license'] ?? null)) {
            $physician['identifier'] = ['@type' => 'PropertyValue', 'name' => $clinic[$locale]['license_label'], 'value' => $clinic['license']];
        }
        $data = [
            '@context' => 'https://schema.org', '@type' => 'MedicalClinic', '@id' => $base.'/#clinic',
            'name' => $clinic['name'], 'url' => $base.'/',
            'description' => $clinic[$locale]['description'],
            'telephone' => $clinic['phone'], 'address' => $this->postalAddress($clinic),
            'image' => $clinic['images']['fachada']['url'] ?? $base.'/images/fachada.webp', 'logo' => $base.'/images/logo.png',
            'hasMap' => $clinic['maps'], 'sameAs' => [$clinic['facebook']],
            'currenciesAccepted' => 'MXN', 'paymentAccepted' => $clinic[$locale]['payment'],
            'openingHoursSpecification' => $openingHours,
            'employee' => $physician,
        ];
        if ($specialHours) {
            $data['specialOpeningHoursSpecification'] = $specialHours;
        }
        if (filled($clinic['email'] ?? null)) {
            $data['email'] = $clinic['email'];
        }

        return $this->json($data);
    }

    /**
     * Published questions as an FAQPage entity; returns null when there is nothing published.
     */
    public function encodeFaq(array $clinic, string $locale): ?string
    {
        if (empty($clinic['faq'])) {
            return null;
        }

        return $this->json([
            '@context' => 'https://schema.org', '@type' => 'FAQPage', '@id' => rtrim(config('app.url'), '/').'/#faq',
            'inLanguage' => $locale === 'es' ? 'es-MX' : 'en-US',
            'mainEntity' => array_map(fn ($item) => [
                '@type' => 'Question', 'name' => $item[$locale]['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item[$locale]['answer']],
            ], array_values($clinic['faq'])),
        ]);
    }

    /**
     * Splits the editable "city, state, postal code" line into a PostalAddress; unknown parts stay as free text.
     */
    private function postalAddress(array $clinic): array
    {
        $address = ['@type' => 'PostalAddress', 'streetAddress' => $clinic['address'], 'addressCountry' => 'MX'];
        $city = $clinic['city'];
        if (preg_match('/\bC\.?\s*P\.?\s*(\d{5})\b/iu', $city, $postal)) {
            $address['postalCode'] = $postal[1];
            $city = str_replace($postal[0], '', $city);
        }
        $parts = array_values(array_filter(array_map('trim', explode(',', $city)), 'strlen'));
        $address['addressLocality'] = $parts[0] ?? trim($clinic['city']);
        if (isset($parts[1])) {
            $address['addressRegion'] = $parts[1];
        }

        return $address;
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
