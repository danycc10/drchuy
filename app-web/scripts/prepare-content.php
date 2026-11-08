<?php

// Build a public-only seed from the supplied brief. Never copy internal credential notes.
$source = dirname(__DIR__, 2).'/Medicina_Respiratoria_Web/01_BRIEF_Y_CONTENIDOS';
$data = json_decode(file_get_contents($source.'/DATOS_ESTRUCTURADOS.json'), true, 512, JSON_THROW_ON_ERROR);
$english = file_get_contents($source.'/CONTENIDO_PUBLICO_EN.md');
preg_match_all('/^(\d+)\. \*\*(.+?):\*\* (.+)$/m', $english, $matches, PREG_SET_ORDER);
$translations = [];
foreach ($matches as $match) {
    $translations[(int) $match[1]] = ['name' => trim($match[2]), 'description' => trim($match[3])];
}
if (count($translations) !== 14) {
    throw new RuntimeException('Expected 14 English services.');
}
$groups = [
    ['consultation', 'Consultation and follow-up'],
    ['diagnostics', 'Diagnostics and sleep'],
    ['treatment', 'Treatment and education'],
    ['procedures', 'Procedures'],
    ['occupational', 'Occupational health'],
];
$catalog = ['categories' => [], 'services' => []];
foreach ($data['grupos_presentacion'] as $index => $group) {
    $catalog['categories'][] = ['slug' => $groups[$index][0], 'name' => ['es' => $group['titulo'], 'en' => $groups[$index][1]], 'position' => $index + 1];
    foreach ($group['service_ids'] as $id) {
        $item = $data['servicios_google_business'][$id - 1];
        $catalog['services'][] = [
            'source_id' => $id,
            'category' => $groups[$index][0],
            'payload' => [
                'es' => ['name' => $item['nombre'], 'description' => $item['descripcion']],
                'en' => $translations[$id],
                'visible' => true,
                'position' => $id,
            ],
        ];
    }
}
$target = dirname(__DIR__).'/database/content';
if (! is_dir($target)) {
    mkdir($target, 0755, true);
}
file_put_contents($target.'/services.json', json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);
echo "Prepared 14 bilingual services in five categories.\n";
