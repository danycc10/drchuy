<?php

return [
    'enabled' => (bool) env('BACKUP_ENABLED', false),
    'time' => env('BACKUP_TIME', '03:15'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
    'directory' => storage_path('app/private/backups'),
    'max_entry_bytes' => 32 * 1024 * 1024,
    'max_database_bytes' => 8 * 1024 * 1024,
    'max_total_bytes' => 512 * 1024 * 1024,
];
