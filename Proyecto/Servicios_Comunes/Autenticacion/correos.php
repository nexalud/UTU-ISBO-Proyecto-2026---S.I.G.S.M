<?php
// Correos del sistema: enlace para crear la contraseña (usuario nuevo) o para restablecerla.

require_once __DIR__ . '/../Correo/Correo.php';
require_once __DIR__ . '/RecuperacionModelo.php';

// Direccion de la pagina para elegir la contraseña.
// En el servidor conviene definir APP_URL (ej: http://192.168.1.19) para no depender de lo que mande el navegador.
function urlRestablecer(string $codigo): string
{
    $base = rtrim((string) getenv('APP_URL'), '/');
    if ($base !== '') {
        $ruta = $base . '/Proyecto/Servicios_Comunes/Autenticacion/restablecer.php';
    } else {
        $raiz = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
        $ruta = urlServidor() . $raiz . '/Servicios_Comunes/Autenticacion/restablecer.php';
    }
    return $ruta . '?codigo=' . $codigo;
}

// Crea el codigo y manda el correo. $tipo: 'restablecer' o 'bienvenida'.
// Devuelve Correo::ENVIADO, Correo::GUARDADO (modo prueba) o false.
function enviarEnlaceContrasena(array $usuario, string $tipo)
{
    $codigo = (new RecuperacionModelo())->crear((int) $usuario['id_usuario'], $tipo);
    $enlace = urlRestablecer($codigo);

    $esBienvenida = $tipo === 'bienvenida';
    $vence = $esBienvenida ? '48 horas' : RecuperacionModelo::MINUTOS_RESTABLECER . ' minutos';

    $asunto = $esBienvenida ? 'Tu usuario en S.I.G.S.M. está listo' : 'Restablecer tu contraseña de S.I.G.S.M.';
    $titulo = $esBienvenida ? 'Bienvenido/a a S.I.G.S.M.' : 'Restablecer tu contraseña';
    $intro  = $esBienvenida
        ? 'Se creó tu usuario <strong>' . e($usuario['nombre_usuario']) . '</strong> en el panel de documentación y encuestas del Hospital de Clínicas. Para entrar, elegí tu contraseña:'
        : 'Recibimos un pedido para restablecer la contraseña del usuario <strong>' . e($usuario['nombre_usuario']) . '</strong>. Para elegir una nueva, tocá el botón:';
    $boton  = $esBienvenida ? 'Crear mi contraseña' : 'Elegir contraseña nueva';
    $pie    = $esBienvenida
        ? 'Si no esperabas este correo, avisale al administrador del sistema.'
        : 'Si no lo pediste vos, ignorá este correo: tu contraseña no cambia.';

    $html = '<!DOCTYPE html><html lang="es"><body style="margin:0;background:#f0f4f8;font-family:Arial,Helvetica,sans-serif;color:#1a2a3a">'
        . '<div style="max-width:520px;margin:0 auto;padding:32px 16px">'
        . '<div style="background:#ffffff;border:1px solid #e0e8f0;border-radius:16px;padding:32px">'
        . '<p style="margin:0 0 6px;font-size:13px;font-weight:bold;letter-spacing:1px;color:#1565a0">HOSPITAL DE CLÍNICAS · S.I.G.S.M.</p>'
        . '<h1 style="margin:0 0 16px;font-size:24px">' . $titulo . '</h1>'
        . '<p style="margin:0 0 8px;font-size:16px;line-height:1.5">Hola, ' . e($usuario['nombre']) . ':</p>'
        . '<p style="margin:0 0 24px;font-size:16px;line-height:1.5;color:#3d4f62">' . $intro . '</p>'
        . '<p style="margin:0 0 24px"><a href="' . e($enlace) . '" style="display:inline-block;background:#1565a0;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;padding:14px 24px;border-radius:12px">' . $boton . '</a></p>'
        . '<p style="margin:0 0 8px;font-size:14px;color:#5a6a7a">El enlace vence en ' . $vence . ' y se puede usar una sola vez.</p>'
        . '<p style="margin:0 0 8px;font-size:14px;color:#5a6a7a">' . $pie . '</p>'
        . '<p style="margin:16px 0 0;font-size:12px;color:#5a6a7a;word-break:break-all">Si el botón no funciona, copiá esta dirección en el navegador:<br>' . e($enlace) . '</p>'
        . '</div><p style="text-align:center;font-size:12px;color:#5a6a7a">Correo automático, no lo respondas. Desarrollado por Nexalud.</p>'
        . '</div></body></html>';

    $texto = "Hola, {$usuario['nombre']}:\n\n"
        . strip_tags($intro) . "\n\n" . $enlace . "\n\n"
        . "El enlace vence en $vence y se puede usar una sola vez.\n" . $pie . "\n";

    return (new Correo())->enviar(
        $usuario['email'],
        $usuario['nombre'] . ' ' . $usuario['apellido'],
        $asunto,
        $html,
        html_entity_decode($texto, ENT_QUOTES, 'UTF-8')
    );
}
