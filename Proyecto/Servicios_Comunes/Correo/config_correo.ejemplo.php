<?php
// Copiá este archivo como config_correo.php (en esta misma carpeta) y completá los datos.
// config_correo.php NO se sube a GitHub porque tiene la contraseña.
//
// Con Gmail: la "clave" es una contraseña de aplicación de 16 letras
// (Cuenta de Google > Seguridad > Verificación en 2 pasos > Contraseñas de aplicaciones),
// no la contraseña normal de la cuenta.
//
// En el servidor (Docker) no hace falta este archivo: se usan las variables SMTP_* del .env.

return [
    'host'      => 'smtp.gmail.com',
    'puerto'    => 587,
    'seguridad' => 'tls',
    'usuario'   => 'tu_cuenta@gmail.com',
    'clave'     => 'xxxxxxxxxxxxxxxx',
    'remitente' => 'tu_cuenta@gmail.com',
];
