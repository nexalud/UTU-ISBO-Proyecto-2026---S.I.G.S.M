<?php
// Conexion unica a la base de datos del sistema.
// En el servidor los datos vienen de las variables del contenedor (docker-compose.yml).
// En XAMPP no existen esas variables, entonces usa los valores por defecto.

function conectarBD(): mysqli
{
    $host     = getenv('DB_HOST') ?: 'localhost';
    $usuario  = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $base     = getenv('DB_NAME') ?: 'sigsm';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion = new mysqli($host, $usuario, $password, $base);
        $conexion->set_charset('utf8mb4');
        return $conexion;
    } catch (mysqli_sql_exception $e) {
        // El detalle queda en el log del servidor, nunca se le muestra al usuario
        error_log('Error de conexion a la base: ' . $e->getMessage());
        http_response_code(500);
        exit('No se pudo conectar con el sistema. Intentá de nuevo más tarde.');
    }
}
