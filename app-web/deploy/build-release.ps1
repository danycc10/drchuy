$ErrorActionPreference = 'Stop'
$appRoot = Split-Path $PSScriptRoot -Parent
$repoRoot = Split-Path $appRoot -Parent
$releaseId = 'hostgator-' + (Get-Date -Format 'yyyyMMdd-HHmmss')
$releaseRoot = Join-Path $repoRoot ".local/releases/$releaseId"
if (Test-Path -LiteralPath $releaseRoot) { throw 'La carpeta de entrega ya existe.' }
$privateRoot = Join-Path $releaseRoot 'medicina-respiratoria'
$publicRoot = Join-Path $releaseRoot 'public_html'
New-Item -ItemType Directory -Path $privateRoot, $publicRoot -Force | Out-Null

Push-Location $appRoot
try {
    & npm.cmd ci
    if ($LASTEXITCODE -ne 0) { throw 'Fallo npm ci.' }
    & npm.cmd run build
    if ($LASTEXITCODE -ne 0) { throw 'Fallo la compilacion.' }

    # Lista explicita: no copiar .env, datos de storage, BD local, tests o cache local.
    foreach ($directory in @('app', 'config', 'resources', 'routes')) {
        Copy-Item -LiteralPath (Join-Path $appRoot $directory) -Destination $privateRoot -Recurse
    }
    foreach ($directory in @('database/content', 'database/migrations', 'database/seeders', 'database/factories', 'docs')) {
        $destination = Join-Path $privateRoot $directory
        New-Item -ItemType Directory -Path $destination -Force | Out-Null
        Copy-Item -Path (Join-Path $appRoot "$directory/*") -Destination $destination -Recurse
    }
    foreach ($file in @('artisan', 'composer.json', 'composer.lock', '.env.production.example')) {
        Copy-Item -LiteralPath (Join-Path $appRoot $file) -Destination $privateRoot
    }
    foreach ($directory in @('bootstrap/cache', 'storage/app/private/media', 'storage/app/private/backups', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs')) {
        New-Item -ItemType Directory -Path (Join-Path $privateRoot $directory) -Force | Out-Null
    }
    Copy-Item -LiteralPath "$appRoot/bootstrap/app.php", "$appRoot/bootstrap/providers.php" -Destination "$privateRoot/bootstrap"
    Copy-Item -LiteralPath "$PSScriptRoot/public-path.php" -Destination "$privateRoot/bootstrap/public-path.php"
    foreach ($directory in @('build', 'images')) {
        Copy-Item -LiteralPath "$appRoot/public/$directory" -Destination $publicRoot -Recurse
    }
    foreach ($file in @('favicon.ico', 'apple-touch-icon.png', 'icon-32.png', 'icon-192.png', 'icon-512.png')) {
        Copy-Item -LiteralPath "$appRoot/public/$file" -Destination $publicRoot
    }
    Copy-Item -LiteralPath "$PSScriptRoot/public_html/index.php", "$PSScriptRoot/public_html/.htaccess" -Destination $publicRoot
    Copy-Item -LiteralPath "$PSScriptRoot/INSTALAR.md" -Destination "$releaseRoot/INSTALAR.md"
    New-Item -ItemType Directory -Path "$privateRoot/deploy" -Force | Out-Null
    Copy-Item -LiteralPath "$PSScriptRoot/INSTALAR.md" -Destination "$privateRoot/deploy/INSTALAR.md"
    $revision = & git rev-parse HEAD
    $dirty = [bool](& git status --porcelain)
    "Commit: $revision`nCambios sin commit: $dirty`nDominio: https://medicinarespiratoriadrjesus.com`nInstalacion nueva; sin datos ni secretos locales." | Set-Content -LiteralPath "$releaseRoot/ENTREGA.txt" -Encoding UTF8
} finally { Pop-Location }

Push-Location $privateRoot
try {
    & composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
    if ($LASTEXITCODE -ne 0) { throw 'Fallo Composer de produccion.' }
    & composer check-platform-reqs --no-dev
    if ($LASTEXITCODE -ne 0) { throw 'Plataforma local incompatible.' }
    & php artisan --version
    if ($LASTEXITCODE -ne 0) { throw 'La entrega no inicia.' }
} finally { Pop-Location }

# No generar config/route/view cache con las rutas y configuracion del equipo de desarrollo.
& php "$PSScriptRoot/pack.php" $releaseRoot
if ($LASTEXITCODE -ne 0) { throw 'Fallo la verificacion o empaquetado.' }
Get-FileHash -Algorithm SHA256 -LiteralPath "$releaseRoot.zip" | Format-List
Write-Output "Paquete: $releaseRoot.zip"
