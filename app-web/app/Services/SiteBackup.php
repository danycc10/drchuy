<?php

namespace App\Services;

use App\Models\BackupRun;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\{Cache, DB, File, Storage};
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class SiteBackup
{
    public const TABLES = ['users', 'service_categories', 'services', 'service_revisions', 'content_blocks', 'content_revisions', 'media_assets', 'content_revision_media', 'audit_events', 'review_requests'];

    public function create(): BackupRun
    {
        return Cache::lock('site-backup', 3600)->block(1, function () {
            $run = BackupRun::create(['status' => 'running']);
            $directory = config('backup.directory');
            File::ensureDirectoryExists($directory, 0700);
            $name = 'backup-'.now('UTC')->format('Ymd-His').'-'.Str::uuid().'.zip';
            $temporary = $directory.'/'.$name.'.partial';
            $final = $directory.'/'.$name;
            $zip = new ZipArchive();
            $staging = [];
            try {
                if (! ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256) || $zip->open($temporary, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                    throw new RuntimeException('No se pudo crear un archivo con cifrado AES-256.');
                }
                if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true) && DB::transactionLevel() === 0) {
                    DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
                }
                $manifest = DB::transaction(function () use ($zip, $temporary, &$staging) {
                    $manifest = ['format' => 1, 'created_at' => now()->toIso8601String(), 'source' => $this->identity(DB::connection()),
                        'migrations' => $this->migrations(DB::connection()), 'tables' => [], 'files' => []];
                    foreach (self::TABLES as $table) {
                        $file = $temporary.'.'.$table;
                        $staging[] = $file;
                        $handle = fopen($file, 'xb');
                        if (! $handle) { throw new RuntimeException('No se pudo preparar la exportación.'); }
                        chmod($file, 0600);
                        $count = 0;
                        try {
                            fwrite($handle, '[');
                            foreach (DB::table($table)->cursor() as $row) {
                                $line = ($count ? ',' : '').json_encode((array) $row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                                if (ftell($handle) + strlen($line) > config('backup.max_database_bytes') || fwrite($handle, $line) !== strlen($line)) {
                                    throw new RuntimeException('Una tabla excede el límite o no hay espacio disponible.');
                                }
                                $count++;
                            }
                            fwrite($handle, ']');
                        } finally { fclose($handle); }
                        $entry = 'database/'.$table.'.json';
                        $this->addFile($zip, $entry, $file, $manifest);
                        $manifest['tables'][$table] = $count;
                    }
                    foreach (DB::table('media_assets')->get() as $media) {
                        foreach (['original.'.$media->extension, 'display.webp', 'thumb.webp'] as $variant) {
                            $entry = $media->directory.'/'.$variant;
                            $this->assertMediaPath($entry);
                            $path = Storage::disk('local')->path($entry);
                            $root = realpath(Storage::disk('local')->path('media'));
                            if (! $root || ! $this->inside($path, $root) || ! is_file($path) || filesize($path) > config('backup.max_entry_bytes')) {
                                throw new RuntimeException('Falta una fotografía o excede el límite del respaldo.');
                            }
                            $this->addFile($zip, $entry, $path, $manifest);
                        }
                    }

                    return $manifest;
                });
                $this->add($zip, 'manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR), $manifest);
                if (! $zip->close()) {
                    throw new RuntimeException('No se pudo finalizar el respaldo.');
                }
                chmod($temporary, 0600);
                $this->inspect($temporary);
                if (! rename($temporary, $final)) {
                    throw new RuntimeException('No se pudo guardar el respaldo verificado.');
                }
                $run->update(['status' => 'complete', 'filename' => $name, 'bytes' => filesize($final), 'sha256' => hash_file('sha256', $final), 'verified_at' => now()]);

                return $run;
            } catch (Throwable $error) {
                try { $zip->close(); } catch (Throwable) { /* Already closed. */ }
                File::delete($temporary);
                $run->update(['status' => 'failed']);
                throw new RuntimeException('El respaldo no pudo completarse. Comprueba espacio, extensiones y archivos privados.', previous: $error);
            } finally { File::delete($staging); }
        });
    }

    public function inspect(string $path): array
    {
        $zip = $this->open($path);
        try {
            $manifest = json_decode($this->read($zip, 'manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            if (($manifest['format'] ?? null) !== 1 || array_keys($manifest['tables'] ?? []) !== self::TABLES || ! is_array($manifest['files'] ?? null)) {
                throw new RuntimeException('Formato de respaldo no compatible.');
            }
            $expected = ['manifest.json', ...array_keys($manifest['files'])];
            if ($zip->numFiles !== count($expected)) {
                throw new RuntimeException('El archivo contiene entradas inesperadas.');
            }
            $total = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (! in_array($zip->getNameIndex($i), $expected, true)) {
                    throw new RuntimeException('Entrada no permitida.');
                }
            }
            foreach ($manifest['files'] as $entry => $details) {
                if (! in_array($entry, array_map(fn ($table) => 'database/'.$table.'.json', self::TABLES), true)) {
                    $this->assertMediaPath($entry);
                }
                $data = $this->read($zip, $entry);
                $total += strlen($data);
                if ($total > config('backup.max_total_bytes') || strlen($data) !== $details['bytes'] || ! hash_equals($details['sha256'], hash('sha256', $data))) {
                    throw new RuntimeException('La integridad del respaldo no es válida.');
                }
            }
            foreach (self::TABLES as $table) {
                $rows = json_decode($this->read($zip, 'database/'.$table.'.json'), true, 512, JSON_THROW_ON_ERROR);
                if (! is_array($rows) || ! array_is_list($rows) || count($rows) !== $manifest['tables'][$table]) {
                    throw new RuntimeException('Los registros no coinciden con el manifiesto.');
                }
            }

            return $manifest;
        } finally { $zip->close(); }
    }

    public function restore(string $path, string $connection, string $mediaDirectory): array
    {
        if ($connection === config('database.default') || ! config('database.connections.'.$connection)) {
            throw new RuntimeException('Usa una conexión distinta, configurada para una base vacía.');
        }
        $manifest = $this->inspect($path);
        $target = DB::connection($connection);
        if ($this->identity($target) === $this->identity(DB::connection()) || $this->identity($target) === $manifest['source']) {
            throw new RuntimeException('No se permite restaurar sobre la base de origen.');
        }
        if ($this->migrations($target) !== $manifest['migrations']) {
            throw new RuntimeException('Aplica las mismas migraciones en la base de destino antes de restaurar.');
        }
        foreach ([...self::TABLES, 'sessions', 'password_reset_tokens', 'backup_runs'] as $table) {
            if ($target->table($table)->exists()) {
                throw new RuntimeException('La base de destino debe estar vacía.');
            }
        }
        if (! is_dir($mediaDirectory) || File::allFiles($mediaDirectory) || File::directories($mediaDirectory)
            || $this->inside($mediaDirectory, public_path()) || $this->inside($mediaDirectory, Storage::disk('local')->path('media'))) {
            throw new RuntimeException('Usa una carpeta vacía y separada de los medios activos y de la raíz pública.');
        }
        $zip = $this->open($path);
        $created = [];
        $schema = $target->getSchemaBuilder();
        $schema->disableForeignKeyConstraints();
        try {
            $target->transaction(function () use ($target, $schema, $zip, $mediaDirectory, $manifest, &$created) {
                foreach (self::TABLES as $table) {
                    $columns = $schema->getColumnListing($table);
                    $rows = json_decode($this->read($zip, 'database/'.$table.'.json'), true, 512, JSON_THROW_ON_ERROR);
                    foreach ($rows as $row) {
                        if (array_diff(array_keys($row), $columns) || array_diff($columns, array_keys($row))) {
                            throw new RuntimeException('Las columnas del respaldo no coinciden.');
                        }
                    }
                    foreach (array_chunk($rows, 100) as $chunk) { $target->table($table)->insert($chunk); }
                }
                $this->checkReferences($target);
                // Never revive old authenticated sessions or remember tokens.
                $target->table('users')->increment('auth_version', 1, ['remember_token' => null]);
                $expectedMedia = [];
                foreach ($target->table('media_assets')->get() as $media) {
                    foreach (['original.'.$media->extension, 'display.webp', 'thumb.webp'] as $variant) {
                        $expectedMedia[] = $media->directory.'/'.$variant;
                    }
                }
                $includedMedia = array_values(array_filter(array_keys($manifest['files']), fn ($entry) => str_starts_with($entry, 'media/')));
                sort($expectedMedia);
                sort($includedMedia);
                if ($expectedMedia !== $includedMedia) { throw new RuntimeException('Las fotografías no coinciden con los datos.'); }
                foreach ($expectedMedia as $entry) {
                    $this->assertMediaPath($entry);
                    $file = rtrim($mediaDirectory, '/\\').'/'.substr($entry, 6);
                    File::ensureDirectoryExists(dirname($file), 0700);
                    $created[] = $file;
                    if (File::put($file, $this->read($zip, $entry)) === false) { throw new RuntimeException('No se pudo restaurar una fotografía.'); }
                    chmod($file, 0600);
                }
            });
        } catch (Throwable $error) {
            foreach ($created as $file) {
                if ($this->inside($file, $mediaDirectory)) { File::delete($file); }
            }
            foreach (array_unique(array_map('dirname', $created)) as $directory) {
                if ($this->inside($directory, $mediaDirectory) && is_dir($directory) && ! File::allFiles($directory)) { @rmdir($directory); }
            }
            throw $error;
        } finally {
            $schema->enableForeignKeyConstraints();
            $zip->close();
        }
        BackupRun::where('sha256', hash_file('sha256', $path))->update(['restore_checked_at' => now()]);

        return $manifest['tables'];
    }

    public function prune(): int
    {
        $keep = BackupRun::where('status', 'complete')->latest('id')->limit(2)->pluck('id');
        $expired = BackupRun::where('status', 'complete')->whereNotIn('id', $keep)->where('created_at', '<', now()->subDays(max(1, config('backup.retention_days'))))->get();
        foreach ($expired as $run) {
            if (! preg_match('/^backup-[0-9]{8}-[0-9]{6}-[a-f0-9-]{36}\.zip$/', $run->filename)) { continue; }
            $path = config('backup.directory').'/'.$run->filename;
            if (is_file($path) && ! File::delete($path)) { continue; }
            $run->update(['status' => 'pruned']);
        }

        return $expired->count();
    }

    private function add(ZipArchive $zip, string $entry, string $data, array &$manifest): void
    {
        if (strlen($data) > config('backup.max_entry_bytes') || array_sum(array_column($manifest['files'], 'bytes')) + strlen($data) > config('backup.max_total_bytes')) {
            throw new RuntimeException('El respaldo excede el límite configurado.');
        }
        if (! $zip->addFromString($entry, $data) || ! $zip->setEncryptionName($entry, ZipArchive::EM_AES_256, $this->password())) {
            throw new RuntimeException('No se pudo cifrar una entrada.');
        }
        $manifest['files'][$entry] = ['bytes' => strlen($data), 'sha256' => hash('sha256', $data)];
    }

    private function addFile(ZipArchive $zip, string $entry, string $path, array &$manifest): void
    {
        $bytes = filesize($path);
        if ($bytes > config('backup.max_entry_bytes') || array_sum(array_column($manifest['files'], 'bytes')) + $bytes > config('backup.max_total_bytes')) {
            throw new RuntimeException('El respaldo excede el límite configurado.');
        }
        if (! $zip->addFile($path, $entry) || ! $zip->setEncryptionName($entry, ZipArchive::EM_AES_256, $this->password())) {
            throw new RuntimeException('No se pudo cifrar una entrada.');
        }
        $manifest['files'][$entry] = ['bytes' => $bytes, 'sha256' => hash_file('sha256', $path)];
    }

    private function open(string $path): ZipArchive
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) { throw new RuntimeException('No se pudo abrir el respaldo.'); }
        $zip->setPassword($this->password());

        return $zip;
    }

    private function read(ZipArchive $zip, string $entry): string
    {
        $stat = $zip->statName($entry);
        $max = str_starts_with($entry, 'database/') ? config('backup.max_database_bytes') : config('backup.max_entry_bytes');
        if (! $stat || $stat['size'] > $max || ($stat['encryption_method'] ?? null) !== ZipArchive::EM_AES_256) {
            throw new RuntimeException('Entrada ausente, demasiado grande o sin cifrar.');
        }
        $data = $zip->getFromName($entry);
        if ($data === false) { throw new RuntimeException('No se pudo descifrar el respaldo con la clave de esta instalación.'); }

        return $data;
    }

    private function password(): string
    {
        $key = config('app.key');
        if (! is_string($key) || strlen($key) < 32) { throw new RuntimeException('La clave de la aplicación no está configurada.'); }

        return hash_hmac('sha256', 'medicina-respiratoria-backup-v1', $key);
    }

    private function migrations(Connection $connection): array
    {
        return $connection->table('migrations')->orderBy('migration')->pluck('migration')->all();
    }

    private function identity(Connection $connection): string
    {
        if ($connection->getDriverName() === 'sqlite') {
            $database = $connection->getDatabaseName();
            $identity = $database === ':memory:' ? 'memory:'.$connection->getName() : strtolower(realpath($database) ?: $database);
        } elseif (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $row = $connection->selectOne('SELECT DATABASE() AS db, @@hostname AS host, @@port AS port');
            $identity = $row->host.':'.$row->port.'/'.$row->db;
        } else { throw new RuntimeException('Solo se admiten MySQL, MariaDB y SQLite.'); }

        return hash('sha256', $identity);
    }

    private function assertMediaPath(string $entry): void
    {
        if (! preg_match('#^media/[a-f0-9-]{36}/(?:original\.(?:jpg|png|webp)|display\.webp|thumb\.webp)$#D', $entry)) {
            throw new RuntimeException('Ruta de medios no permitida.');
        }
    }

    private function inside(string $path, string $root): bool
    {
        $real = realpath($path);
        $base = realpath($root);
        if (! $real || ! $base) { return false; }
        $real = strtolower(str_replace('\\', '/', $real));
        $base = rtrim(strtolower(str_replace('\\', '/', $base)), '/');

        return $real === $base || str_starts_with($real, $base.'/');
    }

    private function checkReferences(Connection $connection): void
    {
        foreach (self::TABLES as $table) {
            foreach ($connection->getSchemaBuilder()->getForeignKeys($table) as $foreign) {
                $query = $connection->table($table.' as child')->leftJoin($foreign['foreign_table'].' as parent', function ($join) use ($foreign) {
                    foreach ($foreign['columns'] as $i => $column) { $join->on('child.'.$column, '=', 'parent.'.$foreign['foreign_columns'][$i]); }
                });
                foreach ($foreign['columns'] as $column) { $query->whereNotNull('child.'.$column); }
                if ($query->whereNull('parent.'.$foreign['foreign_columns'][0])->exists()) { throw new RuntimeException('Se detectó una referencia incompleta en el respaldo.'); }
            }
        }
    }
}
