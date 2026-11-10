<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\{BackupRun, ContentBlock, Service, User};
use Illuminate\Console\Command;
use ZipArchive;

class LaunchCheck extends Command
{
    protected $signature = 'app:launch-check {--json : Mostrar el resultado estructurado sin secretos}';
    protected $description = 'Comprobar preparación técnica y editorial sin publicar ni enviar mensajes';

    public function handle(): int
    {
        $url = parse_url(config('app.url'));
        $host = strtolower($url['host'] ?? '');
        $privacy = ContentBlock::where('key', 'legal')->first()?->publishedRevision?->payload;
        $backup = BackupRun::where('status', 'complete')->latest('id')->first();
        $checks = [
            'production' => ['Entorno de producción', app()->environment('production')],
            'debug_disabled' => ['Depuración desactivada', ! config('app.debug')],
            'public_domain' => ['Dominio HTTPS configurado', ($url['scheme'] ?? '') === 'https' && $host !== '' && ! filter_var($host, FILTER_VALIDATE_IP) && $host !== 'localhost' && ! str_ends_with($host, '.test') && ! str_ends_with($host, '.local')],
            'secure_cookies' => ['Cookies de sesión seguras', (bool) config('session.secure')],
            'persistent_limits' => ['Caché persistente para límites de acceso', ! in_array(config('cache.default'), ['array', 'null'], true)],
            'outgoing_mail' => ['Transporte de correo configurado', ! in_array(config('mail.default'), ['log', 'array'], true)],
            'owner_two_factor' => ['Todos los propietarios activos con dos pasos', User::where('role', Role::Owner)->where('is_active', true)->exists() && ! User::where('role', Role::Owner)->where('is_active', true)->whereNull('two_factor_confirmed_at')->exists()],
            'privacy_published' => ['Aviso de privacidad publicado en ambos idiomas', filled($privacy['es']['privacy_content'] ?? null) && filled($privacy['en']['privacy_content'] ?? null)],
            'services_published' => ['Catálogo con versiones publicadas', Service::whereNotNull('published_revision_id')->exists()],
            'backup_recent' => ['Respaldo verificado en las últimas 48 horas', $backup?->verified_at?->greaterThan(now()->subHours(48)) ?? false],
            'restore_tested' => ['Ensayo de restauración registrado', BackupRun::whereNotNull('restore_checked_at')->exists()],
            'backup_schedule' => ['Programación de respaldos habilitada', (bool) config('backup.enabled')],
            'zip_encryption' => ['ZIP con cifrado AES-256', extension_loaded('zip') && ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256)],
        ];
        $result = [];
        foreach ($checks as $key => [$label, $passed]) { $result[] = ['check' => $key, 'label' => $label, 'passed' => (bool) $passed]; }
        if ($this->option('json')) { $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)); }
        else {
            $this->table(['Comprobación', 'Resultado'], array_map(fn ($item) => [$item['label'], $item['passed'] ? 'Correcto' : 'Pendiente'], $result));
            $this->comment('Esto no comprueba entrega de correo, ejecución real de cron, copias externas, aprobación médica/legal, navegación visual ni métricas de rendimiento. Mantén la no indexación hasta la revisión final.');
        }

        return collect($result)->every('passed') ? self::SUCCESS : self::FAILURE;
    }
}
