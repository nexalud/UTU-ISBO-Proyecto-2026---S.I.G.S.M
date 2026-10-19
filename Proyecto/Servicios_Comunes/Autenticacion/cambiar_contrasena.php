<?php
// Herramienta para el administrador: define la contraseña de un usuario.
// Solo se puede usar desde la terminal, nunca desde el navegador.
//
//   En XAMPP:   C:\xampp\php\php.exe Proyecto\Servicios_Comunes\Autenticacion\cambiar_contrasena.php tLeites
//   En Docker:  sudo docker exec -it sigsm_app php /var/www/html/Proyecto/Servicios_Comunes/Autenticacion/cambiar_contrasena.php tLeites

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/UsuarioModelo.php';

$nombreUsuario = $argv[1] ?? '';
if ($nombreUsuario === '') {
    fwrite(STDERR, "Uso: php cambiar_contrasena.php <usuario>\n");
    exit(1);
}

$modelo  = new UsuarioModelo();
$usuario = $modelo->buscarPorNombre($nombreUsuario);
if (!$usuario) {
    fwrite(STDERR, "No existe el usuario $nombreUsuario\n");
    exit(1);
}

echo "Nueva contraseña para $nombreUsuario (mínimo 8 caracteres): ";
$contrasena = trim((string) fgets(STDIN));

if (mb_strlen($contrasena) < 8) {
    fwrite(STDERR, "La contraseña tiene que tener al menos 8 caracteres.\n");
    exit(1);
}

$modelo->cambiarContrasena((int) $usuario['id_usuario'], password_hash($contrasena, PASSWORD_DEFAULT));
echo "Listo: contraseña actualizada para $nombreUsuario\n";
