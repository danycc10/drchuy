<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Services\SiteBackup;
use Illuminate\Console\Command;
use Throwable;

class VerifyBackup extends Command
{
    protected $signature = 'app:verify-backup {archive}';
    protected $description = 'Verificar cifrado, hashes, archivos y conteos de un respaldo sin restaurarlo';

    public function handle(SiteBackup $backup): int
    {
        try {
            $manifest = $backup->inspect($this->argument('archive'));
            BackupRun::where('sha256', hash_file('sha256', $this->argument('archive')))->update(['verified_at' => now()]);
            $this->info('Integridad correcta: '.count($manifest['files']).' archivos. Esto no reemplaza un ensayo de restauración.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('No fue posible verificar el archivo con la clave de esta instalación.');

            return self::FAILURE;
        }
    }
}
