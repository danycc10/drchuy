<?php

namespace App\Console\Commands;

use App\Services\SiteBackup;
use Illuminate\Console\Command;
use Throwable;

class RestoreBackup extends Command
{
    protected $signature = 'app:restore-backup {archive} {--connection= : Conexión distinta con migraciones aplicadas y sin datos} {--media-path= : Carpeta vacía para los medios recuperados}';
    protected $description = 'Restaurar únicamente en una base vacía separada; nunca sobrescribe la instalación activa';

    public function handle(SiteBackup $backup): int
    {
        if (! $this->option('connection') || ! $this->option('media-path')) {
            $this->error('Indica --connection y --media-path para el destino separado.');

            return self::FAILURE;
        }
        try {
            $tables = $backup->restore($this->argument('archive'), $this->option('connection'), $this->option('media-path'));
            $this->info('Restauración comprobada en el destino separado: '.array_sum($tables).' registros.');
            $this->comment('Revisa el sitio en ese entorno antes de promover la recuperación. Las sesiones anteriores no se recuperan.');

            return self::SUCCESS;
        } catch (Throwable $error) {
            $this->error('No se completó la restauración: '.$error->getMessage());

            return self::FAILURE;
        }
    }
}
