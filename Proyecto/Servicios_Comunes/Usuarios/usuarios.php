<?php
// Administracion de usuarios (solo administradores): listado, activar/desactivar y reenviar el enlace de contraseña.

require_once __DIR__ . '/../Autenticacion/sesion.php';
require_once __DIR__ . '/../Autenticacion/correos.php';
$usuarioActual = requerirAdministrador();

$modelo = new UsuarioModelo();

// Texto del aviso segun como salio el correo
function avisoCorreo($resultado, string $ok): array
{
    if ($resultado === Correo::ENVIADO) {
        return ['ok', $ok];
    }
    if ($resultado === Correo::GUARDADO) {
        return ['ok', $ok . ' (Modo prueba: el correo quedó guardado en Servicios_Comunes/Correo/salida.)'];
    }
    return ['error', 'No se pudo enviar el correo. Revisá la configuración del correo del servidor.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $modelo->buscarPorId((int) ($_POST['id'] ?? 0));
    $accion  = $_POST['accion'] ?? '';
    $esYo    = $usuario && (int) $usuario['id_usuario'] === (int) $usuarioActual['id_usuario'];

    if (!validarCsrf($_POST['token'] ?? null)) {
        guardarAviso('error', 'La página estuvo abierta mucho tiempo. Probá de nuevo.');
    } elseif (!$usuario) {
        guardarAviso('error', 'No se encontró el usuario.');
    } elseif ($accion === 'estado') {
        $activo = (int) $usuario['activo'] === 1;
        if ($activo && $esYo) {
            guardarAviso('error', 'No te podés desactivar a vos mismo.');
        } elseif ($activo && esAdministrador($usuario) && $modelo->contarAdministradoresActivos((int) $usuario['id_usuario']) === 0) {
            guardarAviso('error', 'No se puede desactivar: es el único administrador activo.');
        } else {
            $modelo->cambiarEstado((int) $usuario['id_usuario']);
            if ($activo) {
                (new RecuperacionModelo())->anularPendientes((int) $usuario['id_usuario']);
            }
            guardarAviso('ok', ($activo ? 'Se desactivó a ' : 'Se activó a ') . $usuario['nombre'] . ' ' . $usuario['apellido'] . '.');
        }
    } elseif ($accion === 'enlace') {
        if ((int) $usuario['activo'] !== 1) {
            guardarAviso('error', 'Primero activá al usuario.');
        } else {
            [$tipo, $texto] = avisoCorreo(
                enviarEnlaceContrasena($usuario, 'restablecer'),
                'Le mandamos a ' . $usuario['email'] . ' un enlace para elegir una contraseña nueva.'
            );
            guardarAviso($tipo, $texto);
        }
    }

    header('Location: usuarios.php', true, 303);
    exit;
}

$usuarios = $modelo->listar();
$activos  = count(array_filter($usuarios, fn($u) => (int) $u['activo'] === 1));

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista_General/usuarios.php';
