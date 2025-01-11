<?php

namespace App\Enums;

enum RoleEnum: string
{
    case GERENTE_PROPIETARIO = 'Gerente propietario';
    case SECRETARIO = 'Secretario';
    case ASISTENTE = 'Asistente';

    /**
     * Obtener una lista de valores del enum.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
