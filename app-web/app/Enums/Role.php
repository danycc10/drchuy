<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Propietario',
            self::Administrator => 'Administrador',
            self::Editor => 'Editor',
        };
    }
}
