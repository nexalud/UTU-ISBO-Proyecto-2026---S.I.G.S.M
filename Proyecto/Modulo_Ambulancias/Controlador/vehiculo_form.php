<?php
// Alta o edicion de un vehiculo (?id=ID para editar).

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/FlotaModelo.php';
$usuarioActual = requerirModulo('ambulancias');

$modelo = new FlotaModelo();
$tipos  = $modelo->tiposVehiculo();

$idVehiculo = (int) ($_GET['id'] ?? 0);
$vehiculo   = $idVehiculo ? $modelo->obtenerVehiculo($idVehiculo) : null;
if ($idVehiculo && !$vehiculo) {
    guardarAviso('error', 'No se encontró el vehículo.');
    header('Location: flota.php', true, 303);
    exit;
}

$errores = [];
$datos = [
    'matricula' => $vehiculo['matricula'] ?? '',
    'marca'     => $vehiculo['marca'] ?? '',
    'modelo'    => $vehiculo['modelo'] ?? '',
    'anho'      => (string) ($vehiculo['anho'] ?? date('Y')),
    'tipo'      => (int) ($vehiculo['id_tipo_vehiculo'] ?? 1),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['matricula'] = mb_strtoupper(trim($_POST['matricula'] ?? ''));
    $datos['marca']     = trim($_POST['marca'] ?? '');
    $datos['modelo']    = trim($_POST['modelo'] ?? '');
    $datos['anho']      = trim($_POST['anho'] ?? '');
    $datos['tipo']      = (int) ($_POST['tipo'] ?? 0);

    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } else {
        // Matricula uruguaya: 3 letras y 4 numeros (ej: SCH 1001)
        if (!preg_match('/^[A-Z]{3} ?\d{4}$/', $datos['matricula'])) {
            $errores['matricula'] = 'Escribí la matrícula como en la libreta: 3 letras y 4 números (ej: SCH 1001).';
        } else {
            $datos['matricula'] = substr($datos['matricula'], 0, 3) . ' ' . substr($datos['matricula'], -4);
            if ($modelo->existeMatricula($datos['matricula'], $idVehiculo)) {
                $errores['matricula'] = 'Ya hay un vehículo con esa matrícula.';
            }
        }
        foreach (['marca' => 'la marca', 'modelo' => 'el modelo'] as $campo => $texto) {
            $largo = mb_strlen($datos[$campo]);
            if ($largo < 2 || $largo > 50) {
                $errores[$campo] = 'Escribí ' . $texto . '.';
            }
        }
        if (!ctype_digit($datos['anho']) || (int) $datos['anho'] < 1990 || (int) $datos['anho'] > (int) date('Y') + 1) {
            $errores['anho'] = 'El año tiene que estar entre 1990 y ' . ((int) date('Y') + 1) . '.';
        }
        if (!in_array($datos['tipo'], array_map('intval', array_column($tipos, 'id_tipo_vehiculo')), true)) {
            $errores['tipo'] = 'Elegí el tipo.';
        }

        if (!$errores) {
            $modelo->guardarVehiculo($vehiculo ? $idVehiculo : null, ['anho' => (int) $datos['anho']] + $datos);
            guardarAviso('ok', 'Se guardó el vehículo ' . $datos['matricula'] . '.');
            header('Location: flota.php', true, 303);
            exit;
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista/vehiculo_form.php';
