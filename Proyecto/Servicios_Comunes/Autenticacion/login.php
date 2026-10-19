<?php
// Inicio de sesion de los funcionarios.

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/UsuarioModelo.php';

const INTENTOS_MAXIMOS = 5;
const SEGUNDOS_BLOQUEO = 60;

$destino = '../../Modulo_Documentacion/Controlador/inicio.php';

// Si ya inicio sesion, va directo al panel
$actual = usuarioActual();
if ($actual && puedeEntrarAlPanel($actual)) {
    header('Location: ' . $destino);
    exit;
}

$error   = '';
$usuario = '';
$aviso   = tomarAviso();
$intentos = $_SESSION['intentos_login'] ?? ['cantidad' => 0, 'hasta' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario    = trim($_POST['usuario'] ?? '');
    $contrasena = (string) ($_POST['contrasena'] ?? '');

    if ($intentos['hasta'] > time()) {
        $error = 'Demasiados intentos. Esperá ' . ($intentos['hasta'] - time()) . ' segundos y probá de nuevo.';
    } elseif (!validarCsrf($_POST['token'] ?? null)) {
        $error = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } elseif ($usuario === '' || $contrasena === '') {
        $error = 'Completá el usuario y la contraseña.';
    } else {
        $modelo = new UsuarioModelo();
        $datos  = $modelo->buscarPorNombre($usuario);

        // Si el usuario no existe se verifica igual contra un hash cualquiera,
        // asi la respuesta tarda lo mismo y no se puede adivinar que usuarios existen
        $hash   = $datos['contrasenha_hash'] ?? '$2y$10$9A6LmqdRbuh/PcVIqT8Z3uetTS49RFM8bF0eNO.JFi204exEyDmjW';
        $valida = password_verify($contrasena, $hash) && $datos && (int) $datos['activo'] === 1;

        if (!$valida) {
            $intentos['cantidad']++;
            if ($intentos['cantidad'] >= INTENTOS_MAXIMOS) {
                $intentos = ['cantidad' => 0, 'hasta' => time() + SEGUNDOS_BLOQUEO];
            }
            $_SESSION['intentos_login'] = $intentos;
            $error = 'Usuario o contraseña incorrectos.';
        } elseif (!puedeEntrarAlPanel($datos)) {
            $error = 'Tu usuario no tiene permiso para entrar al panel. Consultá con el Departamento de Informática (DTI).';
        } else {
            // Si el hash se hizo con un costo viejo, se actualiza sin que el usuario note nada
            if (password_needs_rehash($datos['contrasenha_hash'], PASSWORD_DEFAULT)) {
                $modelo->cambiarContrasena((int) $datos['id_usuario'], password_hash($contrasena, PASSWORD_DEFAULT));
            }

            unset($_SESSION['intentos_login']);
            iniciarSesion($datos);
            header('Location: ' . $destino, true, 303);
            exit;
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista_General/login.php';
