<?php
// Seguimiento de un traslado (?id=ID): estado actual, horas, historial y boton para pasar al siguiente estado.

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/TrasladoModelo.php';
$usuarioActual = requerirModulo('ambulancias');

$modelo     = new TrasladoModelo();
$idTraslado = (int) ($_GET['id'] ?? 0);
$traslado   = $modelo->obtener($idTraslado);

if (!$traslado) {
    guardarAviso('error', 'No se encontró el traslado.');
    header('Location: traslados.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $observaciones = mb_substr(trim($_POST['observaciones'] ?? ''), 0, 255);
    $accion = $_POST['accion'] ?? '';

    if (!validarCsrf($_POST['token'] ?? null)) {
        guardarAviso('error', 'La página estuvo abierta mucho tiempo. Probá de nuevo.');
    } elseif ($accion === 'avanzar') {
        $ok = $modelo->avanzar($idTraslado, (int) ($_POST['estado'] ?? 0), (int) $usuarioActual['id_usuario'], $observaciones ?: null);
        guardarAviso($ok ? 'ok' : 'error', $ok ? 'Se registró el cambio de estado.' : 'El traslado ya había cambiado de estado. Revisá cómo quedó.');
    } elseif ($accion === 'cancelar') {
        if (mb_strlen($observaciones) < 3) {
            guardarAviso('error', 'Para cancelar, escribí el motivo.');
        } else {
            $ok = $modelo->cancelar($idTraslado, (int) $usuarioActual['id_usuario'], $observaciones);
            guardarAviso($ok ? 'ok' : 'error', $ok ? 'Se canceló el traslado.' : 'Solo se puede cancelar antes de que salga.');
        }
    }

    header('Location: traslado.php?id=' . $idTraslado, true, 303);
    exit;
}

$historial = $modelo->historial($idTraslado);

// Momento en que se llego a cada estado (para la linea de tiempo)
$momentos = [];
foreach ($historial as $h) {
    $momentos[(int) $h['id_estado']] = $h['fecha_hora'];
}

$pasos = [
    TrasladoModelo::PROGRAMADO => 'Programado',
    TrasladoModelo::EN_VIAJE   => 'En viaje',
    TrasladoModelo::EN_DESTINO => 'En destino',
    TrasladoModelo::REGRESANDO => 'Regresando',
    TrasladoModelo::FINALIZADO => 'Finalizado',
];

$estado    = (int) $traslado['id_estado'];
$siguiente = TrasladoModelo::ACCION[$estado] ?? null;

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista/traslado.php';
