<?php
// Flota: vehiculos del hospital, poner o sacar de servicio.

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/FlotaModelo.php';
$usuarioActual = requerirModulo('ambulancias');

$modelo = new FlotaModelo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo = $modelo->obtenerVehiculo((int) ($_POST['id'] ?? 0));

    if (!validarCsrf($_POST['token'] ?? null)) {
        guardarAviso('error', 'La página estuvo abierta mucho tiempo. Probá de nuevo.');
    } elseif (!$vehiculo) {
        guardarAviso('error', 'No se encontró el vehículo.');
    } elseif ($vehiculo['activo'] && array_filter($modelo->listarVehiculos(), fn($v) => (int) $v['id_vehiculo'] === (int) $vehiculo['id_vehiculo'] && $v['en_traslado'])) {
        guardarAviso('error', 'No se puede sacar de servicio: ' . $vehiculo['matricula'] . ' está en un traslado en curso.');
    } else {
        $modelo->cambiarEstadoVehiculo((int) $vehiculo['id_vehiculo']);
        guardarAviso('ok', $vehiculo['matricula'] . ($vehiculo['activo'] ? ' quedó fuera de servicio.' : ' volvió a estar en servicio.'));
    }
    header('Location: flota.php', true, 303);
    exit;
}

$busqueda  = mb_substr(trim($_GET['q'] ?? ''), 0, 50);
$vehiculos = $modelo->listarVehiculos($busqueda);

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista/flota.php';
