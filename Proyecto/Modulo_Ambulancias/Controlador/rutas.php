<?php
// Rutas del circuito nacional (rutina base de cada recorrido) y lugares a donde se va.

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/FlotaModelo.php';
$usuarioActual = requerirModulo('ambulancias');

$modelo = new FlotaModelo();
$ubicaciones = $modelo->listarUbicaciones();
$idsUbicacion = array_map('intval', array_column($ubicaciones, 'id_ubicacion'));
$tiposUbicacion = ['hospital' => 'Hospital', 'centro_salud' => 'Centro de salud', 'domicilio' => 'Domicilio', 'otro' => 'Otro'];

$errores = [];
$ruta = ['origen' => 1, 'destino' => 0, 'descripcion' => '', 'km' => '', 'minutos' => ''];
$lugar = ['nombre' => '', 'direccion' => '', 'tipo' => 'hospital'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } elseif ($accion === 'ruta') {
        $ruta = [
            'origen'      => (int) ($_POST['origen'] ?? 0),
            'destino'     => (int) ($_POST['destino'] ?? 0),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'km'          => str_replace(',', '.', trim($_POST['km'] ?? '')),
            'minutos'     => trim($_POST['minutos'] ?? ''),
        ];
        if (!in_array($ruta['origen'], $idsUbicacion, true) || !in_array($ruta['destino'], $idsUbicacion, true)) {
            $errores['ruta'] = 'Elegí el origen y el destino.';
        } elseif ($ruta['origen'] === $ruta['destino']) {
            $errores['ruta'] = 'El origen y el destino tienen que ser distintos.';
        } elseif ($modelo->existeRuta($ruta['origen'], $ruta['destino'])) {
            $errores['ruta'] = 'Ya hay una ruta para ese recorrido.';
        } elseif (!is_numeric($ruta['km']) || (float) $ruta['km'] <= 0 || (float) $ruta['km'] > 9999) {
            $errores['ruta'] = 'Escribí la distancia en kilómetros.';
        } elseif (!ctype_digit($ruta['minutos']) || (int) $ruta['minutos'] < 1 || (int) $ruta['minutos'] > 2000) {
            $errores['ruta'] = 'Escribí el tiempo estimado en minutos.';
        } elseif (mb_strlen($ruta['descripcion']) > 255) {
            $errores['ruta'] = 'La descripción puede tener hasta 255 caracteres.';
        } else {
            $modelo->crearRuta($ruta['origen'], $ruta['destino'], $ruta['descripcion'] ?: null, (float) $ruta['km'], (int) $ruta['minutos']);
            guardarAviso('ok', 'Se agregó la ruta.');
            header('Location: rutas.php', true, 303);
            exit;
        }
    } elseif ($accion === 'lugar') {
        $lugar = [
            'nombre'    => trim($_POST['nombre'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'tipo'      => (string) ($_POST['tipo'] ?? ''),
        ];
        if (mb_strlen($lugar['nombre']) < 3 || mb_strlen($lugar['nombre']) > 100) {
            $errores['lugar'] = 'Escribí el nombre del lugar.';
        } elseif ($modelo->existeUbicacion($lugar['nombre'])) {
            $errores['lugar'] = 'Ya existe un lugar con ese nombre.';
        } elseif (mb_strlen($lugar['direccion']) < 3 || mb_strlen($lugar['direccion']) > 255) {
            $errores['lugar'] = 'Escribí la dirección.';
        } elseif (!array_key_exists($lugar['tipo'], $tiposUbicacion)) {
            $errores['lugar'] = 'Elegí el tipo de lugar.';
        } else {
            $modelo->crearUbicacion($lugar['nombre'], $lugar['direccion'], $lugar['tipo']);
            guardarAviso('ok', 'Se agregó "' . $lugar['nombre'] . '". Ahora podés crearle una ruta.');
            header('Location: rutas.php', true, 303);
            exit;
        }
    } elseif ($accion === 'eliminar') {
        $ok = $modelo->eliminarRuta((int) ($_POST['id'] ?? 0));
        guardarAviso($ok ? 'ok' : 'error', $ok ? 'Se eliminó la ruta.' : 'No se puede eliminar: ya se usó en traslados.');
        header('Location: rutas.php', true, 303);
        exit;
    }
}

$rutas = $modelo->listarRutas();

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista/rutas.php';
