<?php

namespace App\Support;

class MediaSlots
{
    public static function definitions(): array
    {
        return [
            'doctor' => ['label' => 'Retrato del médico', 'file' => 'doctor.webp', 'width' => 709, 'height' => 1536, 'alt' => 'doctor_alt'],
            'consultorio' => ['label' => 'Interior del consultorio', 'file' => 'consultorio.webp', 'width' => 800, 'height' => 600, 'alt' => 'photos.0'],
            'fachada' => ['label' => 'Fachada', 'file' => 'fachada.webp', 'width' => 800, 'height' => 600, 'alt' => 'photos.1'],
            'entrada' => ['label' => 'Entrada al consultorio', 'file' => 'entrada.webp', 'width' => 800, 'height' => 600, 'alt' => 'photos.2'],
        ];
    }
}
