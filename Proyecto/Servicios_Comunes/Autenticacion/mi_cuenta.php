<?php
// Mi cuenta: datos del usuario con sesion y cambio de su propia contraseña.

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/RecuperacionModelo.php';
$usuarioActual = requerirFuncionario();

$modelo = new UsuarioModelo();
$datos  = $modelo->buscarPorId((int) $usuarioActual['id_usuario']);

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual   = (string) ($_POST['actual'] ?? '');
    $nueva    = (string) ($_POST['nueva'] ?? '');
    $repetida = (string) ($_POST['repetir'] ?? '');

    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } elseif (!password_verify($actual, $datos['contrasenha_hash'])) {
        $errores['actual'] = 'La contraseña actual no es correcta.';
    } elseif ($problema = errorContrasenaNueva($nueva, $repetida, $datos['nombre_usuario'])) {
        $errores['nueva'] = $problema;
    } elseif (password_verify($nueva, $datos['contrasenha_hash'])) {
        $errores['nueva'] = 'La contraseña nueva tiene que ser distinta a la actual.';
    } else {
        $modelo->cambiarContrasena((int) $datos['id_usuario'], password_hash($nueva, PASSWORD_DEFAULT));
        // Si habia un enlace de "olvide mi contraseña" pendiente, ya no sirve
        (new RecuperacionModelo())->anularPendientes((int) $datos['id_usuario']);
        session_regenerate_id(true);

        guardarAviso('ok', 'Se cambió tu contraseña.');
        header('Location: mi_cuenta.php', true, 303);
        exit;
    }
}

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista_General/mi_cuenta.php';
