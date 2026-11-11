<?php

// Constructor CLI; no se incluye ni se expone en el servidor web.
if (PHP_SAPI !== 'cli' || empty($argv[1]) || ! is_dir($argv[1])) {
    exit(1);
}

$root = realpath($argv[1]);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
$entries = [];
foreach ($files as $file) {
    $name = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if ($file->isLink() || preg_match('~(^|/)\.env(?!\.production\.example$)(\.|/|$)|(^|/)(node_modules|\.git)(/|$)|\.(sqlite|log|sql)$~', $name)) {
        throw new RuntimeException("Archivo no permitido: $name");
    }
    if ($file->isFile() && (str_starts_with($name, 'medicina-respiratoria/storage/') || preg_match('~^medicina-respiratoria/bootstrap/cache/(config|routes.*|events)\.php$~', $name))) {
        throw new RuntimeException("Datos de ejecucion no permitidos: $name");
    }
    $entries[$name] = $file->isDir();
}
$zip = new ZipArchive;
if ($zip->open($root.'.zip', ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
    throw new RuntimeException('No se pudo crear el ZIP.');
}
foreach ($entries as $name => $directory) {
    $ok = $directory ? $zip->addEmptyDir($name) : $zip->addFile($root.'/'.$name, $name);
    if (! $ok) { throw new RuntimeException("No se pudo agregar $name"); }
}
if (! $zip->close()) { throw new RuntimeException('No se pudo cerrar el ZIP.'); }
echo count($entries)." entradas verificadas; sin datos locales.\n";
