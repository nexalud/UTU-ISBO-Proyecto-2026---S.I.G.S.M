<?php
// "Olvide mi contraseña": la persona escribe su usuario o correo y le llega un enlace para elegir una nueva.
// La respuesta es siempre la misma, exista o no el usuario, para que nadie pueda averiguar quien tiene cuenta.

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/correos.php';

const PEDIDOS_POR_SESION = 3;
const MINUTOS_ENTRE_TANDAS = 15;
const PEDIDOS_POR_USUARIO_HORA = 3;

$actual = usuarioActual();
if ($actual && puedeEntrarAlPanel($actual)) {
    header('Location: ../../Modulo_Documentacion/Controlador/inicio.php');
    exit;
}

$error    = '';
$texto    = '';
$enviado  = isset($_GET['enviado']) && !empty($_SESSION['olvide_enviado']);
$modoPrueba = (new Correo())->modoPrueba();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = mb_substr(trim($_POST['usuario'] ?? ''), 0, 150);

    // Freno por sesion: hasta 3 pedidos cada 15 minutos
    $pedidos = array_filter($_SESSION['olvide_pedidos'] ?? [], fn($t) => $t > time() - MINUTOS_ENTRE_TANDAS * 60);

    if (!validarCsrf($_POST['token'] ?? null)) {
        $error = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } elseif ($texto === '') {
        $error = 'Escribí tu usuario o tu correo.';
    } elseif (count($pedidos) >= PEDIDOS_POR_SESION) {
        $error = 'Hiciste varios pedidos seguidos. Esperá unos minutos y revisá tu correo (también la carpeta de spam).';
    } else {
        $pedidos[] = time();
        $_SESSION['olvide_pedidos'] = $pedidos;

        $usuario = (new UsuarioModelo())->buscarPorNombreOEmail($texto);

        if ($usuario && (int) $usuario['activo'] === 1
            && (new RecuperacionModelo())->pedidosRecientes((int) $usuario['id_usuario']) < PEDIDOS_POR_USUARIO_HORA) {
            if (enviarEnlaceContrasena($usuario, 'restablecer') === false) {
                error_log('Fallo el envio del correo de restablecer para el usuario ' . $usuario['id_usuario']);
            }
        }

        $_SESSION['olvide_enviado'] = true;
        header('Location: olvide.php?enviado=1', true, 303);
        exit;
    }
}

if ($enviado) {
    unset($_SESSION['olvide_enviado']);
}

$token = tokenCsrf();

require __DIR__ . '/../Vista_General/olvide.php';
