<?php
// Encuesta del paciente. Se llega desde la pagina del QR del area (boton "Responder encuesta"):
// encuesta.php?qr=QR-CAT-0003 muestra la encuesta abierta de esa area.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/EncuestaModelo.php';

$LARGO_MAXIMO = 500;

$codigoQr  = trim($_GET['qr'] ?? '');
$idDocumento = max(0, (int) ($_GET['doc'] ?? 0));   // si viene del lector de PDF, al terminar vuelve a ese documento
$estado    = 'form';   // form | gracias | error
$encuesta  = null;
$preguntas = [];
$errores   = [];       // id_pregunta => mensaje
$valores   = [];       // id_pregunta => valor elegido o escrito (para no perderlo si hay errores)
$errorGeneral = '';

// Un QR valido solo tiene letras, numeros y guiones
if (!preg_match('/^[A-Za-z0-9-]{1,50}$/', $codigoQr)) {
    $estado = 'error';
} else {
    $modelo   = new EncuestaModelo();
    $encuesta = $modelo->buscarPorQr($codigoQr);

    if (!$encuesta) {
        $estado = 'error';
    } else {
        $idEncuesta = (int) $encuesta['id_encuesta'];
        $preguntas  = $modelo->obtenerPreguntas($idEncuesta);

        if (!$preguntas) {
            $estado = 'error';
        } elseif (isset($_GET['ok']) && !empty($_SESSION['encuesta_enviada'][$idEncuesta])) {
            // Viene de enviar: se muestra el agradecimiento una sola vez
            unset($_SESSION['encuesta_enviada'][$idEncuesta]);
            $estado = 'gracias';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';

            if (!hash_equals($_SESSION['token_encuesta'] ?? '', $token)) {
                $errorGeneral = 'La página estuvo abierta mucho tiempo. Revisá tus respuestas y enviá de nuevo.';
            } else {
                $respuestas = [];

                foreach ($preguntas as $p) {
                    $id     = $p['id_pregunta'];
                    $campo  = 'p' . $id;

                    if ($p['tipo_pregunta'] === 'abierta') {
                        $texto = trim((string) ($_POST[$campo] ?? ''));
                        $texto = mb_substr($texto, 0, $LARGO_MAXIMO);
                        $valores[$id] = $texto;

                        if ($texto === '') {
                            if ($p['obligatoria']) {
                                $errores[$id] = 'Escribí una respuesta para continuar.';
                            }
                            continue;
                        }

                        $respuestas[] = ['id_pregunta' => $id, 'id_opcion' => null, 'texto_libre' => $texto];
                    } else {
                        $idOpcion = (int) ($_POST[$campo] ?? 0);
                        $validas  = array_column($p['opciones'], 'id_opcion');
                        $valores[$id] = $idOpcion;

                        if (!in_array($idOpcion, $validas, true)) {
                            if ($p['obligatoria']) {
                                $errores[$id] = $p['tipo_pregunta'] === 'escala'
                                    ? 'Elegí una calificación para continuar.'
                                    : 'Elegí una opción para continuar.';
                            }
                            continue;
                        }

                        $respuestas[] = ['id_pregunta' => $id, 'id_opcion' => $idOpcion, 'texto_libre' => null];
                    }
                }

                if ($errores) {
                    $errorGeneral = 'Faltan responder preguntas obligatorias.';
                } elseif (!$respuestas) {
                    $errorGeneral = 'Respondé al menos una pregunta antes de enviar.';
                } else {
                    try {
                        $modelo->guardarRespuesta($idEncuesta, $respuestas);
                    } catch (mysqli_sql_exception $ex) {
                        error_log('Error al guardar encuesta: ' . $ex->getMessage());
                        $errorGeneral = 'No pudimos guardar tu respuesta. Intentá de nuevo en un momento.';
                    }

                    if ($errorGeneral === '') {
                        // Post/Redirect/Get: si recargan la pagina no se guarda dos veces
                        unset($_SESSION['token_encuesta']);
                        $_SESSION['encuesta_enviada'][$idEncuesta] = true;
                        // La pagina del area usa esto para mostrar "Gracias" en vez del boton
                        $_SESSION['encuestas_respondidas'][$idEncuesta] = true;
                        header('Location: encuesta.php?qr=' . urlencode($codigoQr) . ($idDocumento ? '&doc=' . $idDocumento : '') . '&ok=1', true, 303);
                        exit;
                    }
                }
            }
        }
    }
}

if ($estado === 'form') {
    if (empty($_SESSION['token_encuesta'])) {
        $_SESSION['token_encuesta'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['token_encuesta'];
}

if ($estado === 'error') {
    http_response_code(404);
}

// A donde vuelve el paciente: al documento que estaba leyendo o a la lista del area
$urlVolver = $idDocumento
    ? '../../Modulo_Documentacion/Controlador/visor.php?id=' . $idDocumento
    : '../../Modulo_Documentacion/Controlador/paciente.php?qr=' . urlencode($codigoQr);

require __DIR__ . '/../Vista/encuesta.php';
