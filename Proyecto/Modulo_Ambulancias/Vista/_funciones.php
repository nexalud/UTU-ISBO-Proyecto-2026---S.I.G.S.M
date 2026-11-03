<?php
// Pequeñas ayudas de las vistas de ambulancias

// T-0007
function codigoTraslado(int $id): string
{
    return 'T-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
}

// "14:30" si es hoy, "02/11 14:30" si es otro dia, "–" si no hay
function hora(?string $fecha): string
{
    if (!$fecha) {
        return '–';
    }
    $t = strtotime($fecha);
    return date('Y-m-d', $t) === date('Y-m-d') ? date('H:i', $t) : date('d/m H:i', $t);
}

// Clase de color para el estado
function claseEstado(int $idEstado): string
{
    return [1 => 'programada', 2 => 'en-viaje', 3 => 'en-viaje', 4 => 'en-viaje', 5 => 'activo', 6 => 'inactivo'][$idEstado] ?? 'inactivo';
}

// Qué se lleva: el paciente o el tipo de elemento
function queSeLleva(array $t): string
{
    return $t['requiere_paciente'] && $t['paciente'] ? $t['paciente'] : $t['tipo_elemento'];
}
