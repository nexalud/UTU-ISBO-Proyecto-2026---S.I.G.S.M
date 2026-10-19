<?php
// Cierra la sesion. Solo acepta POST con token, para que un link de otro sitio no te saque.

require_once __DIR__ . '/sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validarCsrf($_POST['token'] ?? null)) {
    cerrarSesion();
    guardarAviso('ok', 'Cerraste sesión.');
}

header('Location: login.php', true, 303);
exit;
