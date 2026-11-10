<?php

namespace App\Console\Commands;

use App\Services\SiteBackup;
use Illuminate\Console\Command;
use Throwable;

class CreateBackup extends Command
{
    protected $signature = 'app:backup {--prune : Aplicar la retención después de crear un respaldo verificado}';
    protected $description = 'Crear un respaldo privado cifrado y verificar su integridad';

    public function handle(SiteBackup $backup): int
    {
        try {
            $run = $backup->create();
            $this->info('Respaldo cifrado y verificado: '.$run->filename);
            if ($this->option('prune')) { $this->info('Respaldos vencidos retirados: '.$backup->prune()); }

            return self::SUCCESS;
        } catch (Throwable $error) {
            $this->error('No se completó el respaldo. Comprueba espacio, permisos, ZIP/AES-256 y medios privados.');

            return self::FAILURE;
        }
    }
}
