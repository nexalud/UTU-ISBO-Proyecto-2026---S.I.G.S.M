<?php
// Panel del funcionario: listado de encuestas, cerrar y eliminar.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/GestionEncuestaModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo = new GestionEncuestaModelo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $encuesta = $modelo->obtener((int) ($_POST['id'] ?? 0));
    $accion   = $_POST['accion'] ?? '';

    if (!validarCsrf($_POST['token'] ?? null)) {
        guardarAviso('error', 'La página estuvo abierta mucho tiempo. Probá de nuevo.');
    } elseif (!$encuesta) {
        guardarAviso('error', 'No se encontró la encuesta.');
    } elseif ($accion === 'cerrar') {
        $modelo->cerrar((int) $encuesta['id_encuesta']);
        guardarAviso('ok', 'Se cerró "' . $encuesta['titulo'] . '". Las respuestas quedan guardadas.');
    } elseif ($accion === 'eliminar') {
        if ($modelo->eliminar((int) $encuesta['id_encuesta'])) {
            guardarAviso('ok', 'Se eliminó "' . $encuesta['titulo'] . '".');
        } else {
            guardarAviso('error', 'No se puede eliminar "' . $encuesta['titulo'] . '" porque ya tiene respuestas. Podés cerrarla.');
        }
    }

    header('Location: encuestas.php', true, 303);
    exit;
}

$encuestas = $modelo->listar();

// Que encuesta muestra hoy cada QR: la abierta mas nueva de cada categoria (igual que la pagina del paciente)
$enElQr = [];
$abiertasPorCategoria = [];
foreach ($encuestas as &$e) {
    $e['estado'] = GestionEncuestaModelo::estado($e['fecha_inicio'], $e['fecha_fin']);
    if ($e['estado'] === 'Abierta' && $e['id_categoria']) {
        $idCat = (int) $e['id_categoria'];
        $abiertasPorCategoria[$idCat][] = $e['categoria'];
        $enElQr[$idCat] = max($enElQr[$idCat] ?? 0, (int) $e['id_encuesta']);
    }
}
unset($e);

// Aviso si una categoria tiene mas de una encuesta abierta (el QR solo muestra la mas nueva)
$categoriasRepetidas = [];
foreach ($abiertasPorCategoria as $nombres) {
    if (count($nombres) > 1) {
        $categoriasRepetidas[] = $nombres[0];
    }
}

$hayCategorias = (bool) $modelo->listarCategorias();

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista/encuestas.php';
