<?php
// Alta de traslado: se registra la solicitud y se le asignan vehiculo, chofer y enfermero
// (o se deriva a un proveedor externo). Queda en estado "Programado".
// Con ?id=N se edita un traslado que todavia no salio.

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/TrasladoModelo.php';
require_once __DIR__ . '/../Modelo/PacienteApi.php';
$usuarioActual = requerirModulo('ambulancias');

$modelo = new TrasladoModelo();

// Edicion: solo mientras esta Programado (despues de salir ya no se cambia, queda el historial)
$idTraslado = (int) ($_GET['id'] ?? 0);
$traslado   = null;
if ($idTraslado) {
    $traslado = $modelo->obtener($idTraslado);
    if (!$traslado || (int) $traslado['id_estado'] !== TrasladoModelo::PROGRAMADO) {
        guardarAviso('error', 'Solo se puede editar un traslado que todavía no salió.');
        header('Location: ' . ($traslado ? 'traslado.php?id=' . $idTraslado : 'traslados.php'), true, 303);
        exit;
    }
}

$tipos       = $modelo->tiposElemento();
$ubicaciones = $modelo->ubicaciones();
$canales     = $modelo->canales();
$proveedores = $modelo->proveedores();
$vehiculos   = $modelo->vehiculosLibres($idTraslado);
$choferes    = $modelo->personalLibre('chofer', $idTraslado);
$enfermeros  = $modelo->personalLibre('enfermero', $idTraslado);
$rutas       = $modelo->rutas();

// Para validar que lo que llega del formulario sea una opcion real
function ids(array $filas, string $campo): array
{
    return array_map('intval', array_column($filas, $campo));
}
function fechaHora(string $texto): ?string
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $texto);
    return ($d && $d->format('Y-m-d\TH:i') === $texto) ? $d->format('Y-m-d H:i:s') : null;
}

$errores  = [];
$paciente = null;
$datos = [
    'tipo_elemento' => (int) ($tipos[0]['id_tipo_elemento'] ?? 0),
    'ci'            => '',
    'motivo'        => '',
    'origen'        => 1,       // las ambulancias salen del Hospital de Clinicas
    'destino'       => 0,
    'canal'         => 1,
    'modo'          => 'propio',
    'vehiculo'      => 0,
    'chofer'        => 0,
    'enfermero'     => 0,
    'proveedor'     => 0,
    'salida'        => date('Y-m-d\TH:i', strtotime('+30 minutes')),
    'llegada'       => '',
    'nombre'        => '',      // nombre y apellido: solo si el paciente se carga a mano
    'apellido'      => '',
];

if ($traslado) {
    $datos = [
        'tipo_elemento' => (int) $traslado['id_tipo_elemento'],
        'ci'            => (string) $traslado['ci_paciente'],
        'motivo'        => (string) $traslado['motivo'],
        'origen'        => (int) $traslado['id_origen'],
        'destino'       => (int) $traslado['id_destino'],
        'canal'         => (int) $traslado['id_canal'],
        'modo'          => $traslado['id_proveedor'] ? 'externo' : 'propio',
        'vehiculo'      => (int) $traslado['id_vehiculo'],
        'chofer'        => (int) $traslado['id_chofer'],
        'enfermero'     => (int) $traslado['id_enfermero'],
        'proveedor'     => (int) $traslado['id_proveedor'],
        'salida'        => date('Y-m-d\TH:i', strtotime($traslado['hora_salida_prev'])),
        'llegada'       => $traslado['hora_llegada_prev'] ? date('Y-m-d\TH:i', strtotime($traslado['hora_llegada_prev'])) : '',
        'nombre'        => (string) $traslado['paciente_nombre'],
        'apellido'      => (string) $traslado['paciente_apellido'],
    ];
    if ($traslado['ci_paciente']) {
        $paciente = ['nombre' => $traslado['paciente_nombre'], 'apellido' => $traslado['paciente_apellido']];
    }
}
$cargaManual = false;   // true cuando el paciente no esta en la API y hay que escribir nombre y apellido

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['tipo_elemento', 'origen', 'destino', 'canal', 'vehiculo', 'chofer', 'enfermero', 'proveedor'] as $campo) {
        $datos[$campo] = (int) ($_POST[$campo] ?? 0);
    }
    $datos['ci']      = PacienteApi::limpiar((string) ($_POST['ci'] ?? ''));
    $datos['motivo']  = trim($_POST['motivo'] ?? '');
    $datos['modo']    = ($_POST['modo'] ?? '') === 'externo' ? 'externo' : 'propio';
    $datos['salida']  = trim($_POST['salida'] ?? '');
    $datos['llegada'] = trim($_POST['llegada'] ?? '');
    $datos['nombre']   = trim($_POST['nombre'] ?? '');
    $datos['apellido'] = trim($_POST['apellido'] ?? '');

    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } else {
        // ---- Qué se traslada ----
        $tipo = null;
        foreach ($tipos as $t) {
            if ((int) $t['id_tipo_elemento'] === $datos['tipo_elemento']) {
                $tipo = $t;
            }
        }
        if (!$tipo) {
            $errores['tipo_elemento'] = 'Elegí qué se traslada.';
        } elseif ($tipo['requiere_paciente']) {
            // Los datos del paciente se toman de la API del hospital por la cédula.
            // Si no esta (o la API no responde), se cargan a mano: nombre y apellido.
            if (!PacienteApi::cedulaValida($datos['ci'])) {
                $errores['ci'] = 'Escribí una cédula válida (con el dígito verificador).';
            } elseif (!($paciente = (new PacienteApi())->buscarEnTodo($datos['ci']))) {
                $cargaManual = true;
                $soloLetras = "/^[\\p{L}][\\p{L} '\\-]{1,99}$/u";
                if (!preg_match($soloLetras, $datos['nombre'])) {
                    $errores['nombre'] = 'Escribí el nombre (solo letras, de 2 a 100).';
                }
                if (!preg_match($soloLetras, $datos['apellido'])) {
                    $errores['apellido'] = 'Escribí el apellido (solo letras, de 2 a 100).';
                }
            }
        } else {
            $datos['ci'] = '';
        }

        $largo = mb_strlen($datos['motivo']);
        if ($largo < 3 || $largo > 255) {
            $errores['motivo'] = 'Contá brevemente el motivo o qué se lleva (entre 3 y 255 caracteres).';
        }

        // ---- De dónde a dónde ----
        if (!in_array($datos['origen'], ids($ubicaciones, 'id_ubicacion'), true)) {
            $errores['origen'] = 'Elegí el origen.';
        }
        if (!in_array($datos['destino'], ids($ubicaciones, 'id_ubicacion'), true)) {
            $errores['destino'] = 'Elegí el destino.';
        } elseif ($datos['destino'] === $datos['origen']) {
            $errores['destino'] = 'El destino tiene que ser distinto del origen.';
        }
        if (!in_array($datos['canal'], ids($canales, 'id_canal'), true)) {
            $errores['canal'] = 'Elegí por dónde llegó la solicitud.';
        }

        // ---- Horarios ----
        $salida = fechaHora($datos['salida']);
        if (!$salida) {
            $errores['salida'] = 'Elegí el día y la hora de salida.';
        }
        $llegada = $datos['llegada'] !== '' ? fechaHora($datos['llegada']) : null;
        if ($datos['llegada'] !== '' && !$llegada) {
            $errores['llegada'] = 'La hora no es válida.';
        } elseif ($llegada && $salida && $llegada <= $salida) {
            $errores['llegada'] = 'La llegada tiene que ser después de la salida.';
        }

        // ---- Quién lo hace ----
        if ($datos['modo'] === 'externo') {
            if (!in_array($datos['proveedor'], ids($proveedores, 'id_proveedor'), true)) {
                $errores['proveedor'] = 'Elegí la empresa externa.';
            }
            $datos['vehiculo'] = $datos['chofer'] = $datos['enfermero'] = 0;
        } else {
            $vehiculo = null;
            foreach ($vehiculos as $v) {
                if ((int) $v['id_vehiculo'] === $datos['vehiculo']) {
                    $vehiculo = $v;
                }
            }
            if (!$vehiculo) {
                $errores['vehiculo'] = 'Elegí un vehículo disponible.';
            } elseif ($tipo && !in_array($tipo['id_tipo_elemento'], explode(',', (string) $vehiculo['admite']), false)) {
                // "No todos los traslados se pueden hacer en cualquier vehiculo"
                $errores['vehiculo'] = 'Ese vehículo no puede llevar: ' . $tipo['nombre'] . '.';
            }
            if (!in_array($datos['chofer'], ids($choferes, 'id_usuario'), true)) {
                $errores['chofer'] = 'Elegí un chofer disponible.';
            }
            if (!in_array($datos['enfermero'], ids($enfermeros, 'id_usuario'), true)) {
                $errores['enfermero'] = 'Elegí un enfermero disponible.';
            } elseif ($datos['enfermero'] === $datos['chofer']) {
                $errores['enfermero'] = 'El enfermero no puede ser el mismo que el chofer.';
            }
            $datos['proveedor'] = 0;
        }

        // ---- Guardar ----
        if (!$errores) {
            // Si hay una ruta conocida entre origen y destino, se usa para la llegada estimada
            $ruta = $modelo->rutaEntre($datos['origen'], $datos['destino']);
            if (!$llegada && $ruta) {
                $llegada = date('Y-m-d H:i:s', strtotime($salida . ' +' . (int) $ruta['tiempo_min'] . ' minutes'));
            }

            try {
                // Paciente cargado a mano: se guarda recien ahora, cuando todo lo demas esta bien
                if ($cargaManual) {
                    $paciente = (new PacienteApi())->guardarManual($datos['ci'], $datos['nombre'], $datos['apellido']);
                }

                $guardar = [
                    'ci_paciente'   => $datos['ci'] !== '' ? $datos['ci'] : null,
                    'tipo_elemento' => $datos['tipo_elemento'],
                    'origen'        => $datos['origen'],
                    'destino'       => $datos['destino'],
                    'canal'         => $datos['canal'],
                    'motivo'        => $datos['motivo'],
                    'vehiculo'      => $datos['vehiculo'] ?: null,
                    'chofer'        => $datos['chofer'] ?: null,
                    'enfermero'     => $datos['enfermero'] ?: null,
                    'proveedor'     => $datos['proveedor'] ?: null,
                    'ruta'          => $ruta ? (int) $ruta['id_ruta'] : null,
                    'salida'        => $salida,
                    'llegada'       => $llegada,
                ];

                if ($traslado) {
                    if (!$modelo->actualizar($idTraslado, $guardar)) {
                        guardarAviso('error', 'El traslado ya salió: no se puede editar.');
                        header('Location: traslado.php?id=' . $idTraslado, true, 303);
                        exit;
                    }
                    guardarAviso('ok', 'Se guardaron los cambios del traslado T-' . str_pad((string) $idTraslado, 4, '0', STR_PAD_LEFT) . '.');
                } else {
                    $idTraslado = $modelo->crear($guardar, (int) $usuarioActual['id_usuario']);
                    guardarAviso('ok', 'Se programó el traslado T-' . str_pad((string) $idTraslado, 4, '0', STR_PAD_LEFT) . '.');
                }
                header('Location: traslado.php?id=' . $idTraslado, true, 303);
                exit;
            } catch (mysqli_sql_exception $ex) {
                error_log('Error al crear traslado: ' . $ex->getMessage());
                $errores['general'] = 'No se pudo guardar el traslado. Probá de nuevo.';
            }
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista/traslado_form.php';
