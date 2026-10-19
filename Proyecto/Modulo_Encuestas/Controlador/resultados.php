<?php
// Panel del funcionario: resultados e indicadores de satisfaccion de cada encuesta.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/ResultadosModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo    = new ResultadosModelo();
$encuestas = $modelo->listarEncuestas();

// Encuesta elegida (por defecto, la primera)
$idEncuesta = (int) ($_GET['enc'] ?? 0);
$encuesta   = null;
foreach ($encuestas as $e) {
    if ((int) $e['id_encuesta'] === $idEncuesta) {
        $encuesta = $e;
    }
}
if (!$encuesta && $encuestas) {
    $encuesta   = $encuestas[0];
    $idEncuesta = (int) $encuesta['id_encuesta'];
}

// Periodo: se filtra por fecha_envio >= desde y < hasta
$periodos = [
    'todo'         => 'Todo',
    'mes'          => 'Este mes',
    'mes_anterior' => 'Mes anterior',
    'anio'         => 'Este año',
];
$periodo = array_key_exists($_GET['periodo'] ?? '', $periodos) ? $_GET['periodo'] : 'todo';

$hoy = new DateTimeImmutable('today');
switch ($periodo) {
    case 'mes':
        $desde = $hoy->modify('first day of this month');
        $hasta = $desde->modify('+1 month');
        break;
    case 'mes_anterior':
        $hasta = $hoy->modify('first day of this month');
        $desde = $hasta->modify('-1 month');
        break;
    case 'anio':
        $desde = $hoy->setDate((int) $hoy->format('Y'), 1, 1);
        $hasta = $desde->modify('+1 year');
        break;
    default:
        $desde = new DateTimeImmutable('2000-01-01');
        $hasta = $hoy->modify('+1 day');
}
$desdeSql = $desde->format('Y-m-d H:i:s');
$hastaSql = $hasta->format('Y-m-d H:i:s');

$totalRespuestas   = 0;
$totalComentarios  = 0;
$preguntas         = [];
$comentarios       = [];
$promedio          = null;   // de la primera pregunta de escala
$indicadorOpcion   = null;   // primera opcion de la primera pregunta de opcion unica
$textoEscala       = [];     // id_opcion => "4 · Buena", para los comentarios

if ($encuesta) {
    $totalRespuestas  = $modelo->contarRespuestas($idEncuesta, $desdeSql, $hastaSql);
    $totalComentarios = $modelo->contarComentarios($idEncuesta, $desdeSql, $hastaSql);
    $conteo           = $modelo->contarOpciones($idEncuesta, $desdeSql, $hastaSql);

    foreach ($modelo->obtenerPreguntas($idEncuesta) as $p) {
        if ($p['tipo_pregunta'] === 'abierta') {
            $preguntas[] = $p;
            continue;
        }

        $respondieron = 0;
        foreach ($p['opciones'] as $o) {
            $respondieron += $conteo[$o['id_opcion']] ?? 0;
        }

        $suma = 0;
        foreach ($p['opciones'] as $n => &$o) {
            $o['cantidad']   = $conteo[$o['id_opcion']] ?? 0;
            $o['porcentaje'] = $respondieron ? round($o['cantidad'] * 100 / $respondieron) : 0;

            if ($p['tipo_pregunta'] === 'escala') {
                // En la escala, la primera opcion vale 1, la segunda 2, etc.
                $suma += ($n + 1) * $o['cantidad'];
                $textoEscala[$o['id_opcion']] = ($n + 1) . ' de ' . count($p['opciones']) . ' · '
                    . preg_replace('/^\s*\d+\s*-\s*/', '', $o['texto']);
            }
        }
        unset($o);

        $p['respondieron'] = $respondieron;

        if ($p['tipo_pregunta'] === 'escala') {
            // Para la escala se muestran las opciones de la mejor a la peor
            $p['opciones'] = array_reverse($p['opciones']);
            if ($promedio === null && $respondieron) {
                $promedio = ['valor' => $suma / $respondieron, 'maximo' => count($p['opciones'])];
            }
        } elseif ($indicadorOpcion === null && $p['opciones']) {
            $indicadorOpcion = $p['opciones'][0] + ['pregunta' => $p['texto_pregunta']];
        }

        $preguntas[] = $p;
    }

    $comentarios = $modelo->comentarios($idEncuesta, $desdeSql, $hastaSql);
}

// Estado de la encuesta segun sus fechas
$estado = 'Abierta';
if ($encuesta) {
    $hoyTexto = $hoy->format('Y-m-d');
    if ($encuesta['fecha_inicio'] && $encuesta['fecha_inicio'] > $hoyTexto) {
        $estado = 'Programada';
    } elseif ($encuesta['fecha_fin'] && $encuesta['fecha_fin'] < $hoyTexto) {
        $estado = 'Cerrada';
    }
}

// Todos los QR apuntan a la misma pagina; si la categoria es de encuesta, redirige a la encuesta
$raizProyecto = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$urlQr = ($encuesta && $encuesta['codigo_qr'])
    ? urlServidor() . rtrim($raizProyecto, '/') . '/Modulo_Documentacion/Controlador/paciente.php?qr=' . urlencode($encuesta['codigo_qr'])
    : null;

require __DIR__ . '/../Vista/resultados.php';
