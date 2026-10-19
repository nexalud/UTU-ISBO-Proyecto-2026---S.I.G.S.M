<?php
// Crear una encuesta, editarla (?id=ID) o crear una copia de otra (?duplicar=ID).
// Si la encuesta ya tiene respuestas, las preguntas quedan bloqueadas (para no mezclar resultados).

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/GestionEncuestaModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

const MAXIMO_PREGUNTAS = 20;
const MAXIMO_OPCIONES  = 10;

$modelo     = new GestionEncuestaModelo();
$categorias = $modelo->listarCategorias();

// Pasa las preguntas de la base al formato del formulario
function preguntasParaFormulario(array $preguntasBase): array
{
    $lista = [];
    foreach ($preguntasBase as $p) {
        $lista[] = [
            'texto'       => $p['texto_pregunta'],
            'tipo'        => array_key_exists($p['tipo_pregunta'], GestionEncuestaModelo::TIPOS_PREGUNTA) ? $p['tipo_pregunta'] : 'opcion_unica',
            'obligatoria' => $p['obligatoria'],
            'opciones'    => $p['tipo_pregunta'] === 'escala' ? [] : array_column($p['opciones'], 'texto'),
        ];
    }
    return $lista;
}

function irAlListado(string $tipo, string $texto): void
{
    guardarAviso($tipo, $texto);
    header('Location: encuestas.php', true, 303);
    exit;
}

$idEncuesta = (int) ($_GET['id'] ?? 0);
$encuesta   = null;

$datos = [
    'titulo'       => '',
    'descripcion'  => '',
    'segmento'     => '',
    'categoria'    => 0,
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin'    => '',
];
// Una encuesta nueva arranca con una pregunta de escala y una abierta, que son las mas comunes
$preguntas = [
    ['texto' => '¿Cómo calificarías la atención recibida?', 'tipo' => 'escala', 'obligatoria' => true, 'opciones' => []],
    ['texto' => '¿Querés dejarnos algún comentario?', 'tipo' => 'abierta', 'obligatoria' => false, 'opciones' => []],
];

if ($idEncuesta) {
    $encuesta = $modelo->obtener($idEncuesta);
    if (!$encuesta) {
        irAlListado('error', 'No se encontró la encuesta.');
    }
    $datos = [
        'titulo'       => $encuesta['titulo'],
        'descripcion'  => $encuesta['descripcion'] ?? '',
        'segmento'     => $encuesta['segmento'] ?? '',
        'categoria'    => (int) $encuesta['id_categoria'],
        'fecha_inicio' => $encuesta['fecha_inicio'] ?? '',
        'fecha_fin'    => $encuesta['fecha_fin'] ?? '',
    ];
    $preguntas = preguntasParaFormulario($modelo->obtenerPreguntas($idEncuesta));
} elseif (isset($_GET['duplicar'])) {
    $original = $modelo->obtener((int) $_GET['duplicar']);
    if ($original) {
        $datos['titulo']      = mb_substr($original['titulo'] . ' (copia)', 0, 150);
        $datos['descripcion'] = $original['descripcion'] ?? '';
        $datos['segmento']    = $original['segmento'] ?? '';
        $datos['categoria']   = (int) $original['id_categoria'];
        $preguntas = preguntasParaFormulario($modelo->obtenerPreguntas((int) $original['id_encuesta']));
    }
}

$preguntasBloqueadas = $encuesta && (int) $encuesta['respuestas'] > 0;

$errores          = [];   // errores de los datos generales
$erroresPreguntas = [];   // numero de pregunta => mensaje

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } else {
        // ---------- Datos generales ----------
        $datos['titulo']       = trim($_POST['titulo'] ?? '');
        $datos['descripcion']  = trim($_POST['descripcion'] ?? '');
        $datos['segmento']     = trim($_POST['segmento'] ?? '');
        $datos['categoria']    = (int) ($_POST['categoria'] ?? 0);
        $datos['fecha_inicio'] = trim($_POST['fecha_inicio'] ?? '');
        $datos['fecha_fin']    = trim($_POST['fecha_fin'] ?? '');

        $largo = mb_strlen($datos['titulo']);
        if ($largo < 3 || $largo > 150) {
            $errores['titulo'] = 'Escribí un título de entre 3 y 150 caracteres.';
        }
        if (mb_strlen($datos['descripcion']) > 255) {
            $errores['descripcion'] = 'La descripción puede tener hasta 255 caracteres.';
        }
        $largo = mb_strlen($datos['segmento']);
        if ($largo < 3 || $largo > 100) {
            $errores['segmento'] = 'Escribí a quién está dirigida (entre 3 y 100 caracteres).';
        }
        if (!$modelo->existeCategoria($datos['categoria'])) {
            $errores['categoria'] = 'Elegí la categoría (el QR) donde va a aparecer.';
        }
        if (!fechaValida($datos['fecha_inicio'])) {
            $errores['fecha_inicio'] = 'Elegí desde qué día se puede responder.';
        }
        if ($datos['fecha_fin'] !== '') {
            if (!fechaValida($datos['fecha_fin'])) {
                $errores['fecha_fin'] = 'La fecha no es válida.';
            } elseif (!isset($errores['fecha_inicio']) && $datos['fecha_fin'] < $datos['fecha_inicio']) {
                $errores['fecha_fin'] = 'Tiene que ser igual o posterior a la fecha de inicio.';
            }
        }

        // ---------- Preguntas ----------
        if (!$preguntasBloqueadas) {
            $preguntas = [];
            $recibidas = $_POST['preguntas'] ?? [];

            foreach (is_array($recibidas) ? $recibidas : [] as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $opciones = [];
                foreach ((array) ($p['opciones'] ?? []) as $o) {
                    $o = trim((string) $o);
                    if ($o !== '') {
                        $opciones[] = $o;
                    }
                }
                $preguntas[] = [
                    'texto'       => trim((string) ($p['texto'] ?? '')),
                    'tipo'        => (string) ($p['tipo'] ?? ''),
                    'obligatoria' => !empty($p['obligatoria']),
                    'opciones'    => $opciones,
                ];
            }

            if (!$preguntas) {
                $errores['preguntas'] = 'Agregá al menos una pregunta.';
            } elseif (count($preguntas) > MAXIMO_PREGUNTAS) {
                $errores['preguntas'] = 'La encuesta puede tener hasta ' . MAXIMO_PREGUNTAS . ' preguntas.';
            }

            foreach ($preguntas as $n => $p) {
                $largo = mb_strlen($p['texto']);
                if ($largo < 3 || $largo > 255) {
                    $erroresPreguntas[$n] = 'Escribí la pregunta (entre 3 y 255 caracteres).';
                } elseif (!array_key_exists($p['tipo'], GestionEncuestaModelo::TIPOS_PREGUNTA)) {
                    $erroresPreguntas[$n] = 'Elegí el tipo de respuesta.';
                } elseif ($p['tipo'] === 'opcion_unica') {
                    $sinRepetir = array_unique(array_map('mb_strtolower', $p['opciones']));
                    if (count($p['opciones']) < 2) {
                        $erroresPreguntas[$n] = 'Escribí al menos 2 opciones.';
                    } elseif (count($p['opciones']) > MAXIMO_OPCIONES) {
                        $erroresPreguntas[$n] = 'Puede tener hasta ' . MAXIMO_OPCIONES . ' opciones.';
                    } elseif (count($sinRepetir) !== count($p['opciones'])) {
                        $erroresPreguntas[$n] = 'Hay opciones repetidas.';
                    } elseif (max(array_map('mb_strlen', $p['opciones'])) > 150) {
                        $erroresPreguntas[$n] = 'Cada opción puede tener hasta 150 caracteres.';
                    }
                }
            }

            if ($erroresPreguntas && !isset($errores['preguntas'])) {
                $errores['preguntas'] = 'Revisá las preguntas marcadas en rojo.';
            }
        }

        // ---------- Guardar ----------
        if (!$errores) {
            $paraGuardar = [
                'titulo'       => $datos['titulo'],
                'descripcion'  => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                'segmento'     => $datos['segmento'],
                'categoria'    => $datos['categoria'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin'    => $datos['fecha_fin'] !== '' ? $datos['fecha_fin'] : null,
            ];

            try {
                if ($encuesta) {
                    $modelo->actualizar($idEncuesta, $paraGuardar, $preguntasBloqueadas ? null : $preguntas);
                    irAlListado('ok', 'Se guardaron los cambios de "' . $datos['titulo'] . '".');
                }
                $modelo->crear($paraGuardar, $preguntas);
                irAlListado('ok', 'Se creó la encuesta "' . $datos['titulo'] . '".');
            } catch (mysqli_sql_exception $ex) {
                error_log('Error al guardar encuesta: ' . $ex->getMessage());
                $errores['general'] = 'No se pudo guardar la encuesta. Probá de nuevo.';
            }
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista/encuesta_form.php';
