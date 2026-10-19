<?php
// Pagina del enlace que llega por correo: elegir la contraseña nueva (o la primera, si el usuario es nuevo).

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/RecuperacionModelo.php';

$recuperacion = new RecuperacionModelo();

$codigo   = (string) ($_POST['codigo'] ?? $_GET['codigo'] ?? '');
$pedido   = $recuperacion->buscarValido($codigo);
$error    = '';

if ($pedido && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrasena = (string) ($_POST['contrasena'] ?? '');
    $repetida   = (string) ($_POST['repetir'] ?? '');

    if (!validarCsrf($_POST['token'] ?? null)) {
        $error = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } elseif ($problema = errorContrasenaNueva($contrasena, $repetida, $pedido['nombre_usuario'])) {
        $error = $problema;
    } elseif (!$recuperacion->marcarUsado((int) $pedido['id_recuperacion'])) {
        // Alguien ya lo uso en otra pestaña
        $pedido = null;
    } else {
        (new UsuarioModelo())->cambiarContrasena((int) $pedido['id_usuario'], password_hash($contrasena, PASSWORD_DEFAULT));
        $recuperacion->anularPendientes((int) $pedido['id_usuario']);

        // Por si habia una sesion abierta en este navegador
        cerrarSesion();
        guardarAviso('ok', 'Listo, ya podés entrar con tu contraseña nueva.');
        header('Location: login.php', true, 303);
        exit;
    }
}

if (!$pedido) {
    http_response_code(410);
}

$token = tokenCsrf();

require __DIR__ . '/../Vista_General/restablecer.php';
