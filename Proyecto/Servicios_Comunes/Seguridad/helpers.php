<?php
// Funciones comunes para las pantallas del sistema.

if (session_status() === PHP_SESSION_NONE) {
    // Cookie de sesion: no accesible desde JavaScript y no se manda desde otros sitios
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// Escapa texto antes de mostrarlo en el HTML (evita inyeccion de codigo)
if (!function_exists('e')) {
    function e(?string $texto): string
    {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Token para que los formularios solo se puedan enviar desde nuestras paginas
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function validarCsrf(?string $token): bool
{
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

// Mensaje que se muestra una sola vez despues de redirigir (ej: "Documento guardado")
function guardarAviso(string $tipo, string $texto): void
{
    $_SESSION['aviso'] = ['tipo' => $tipo, 'texto' => $texto];
}

function tomarAviso(): ?array
{
    $aviso = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $aviso;
}

// Direccion del servidor tal como la usa el navegador (ej: http://192.168.1.19)
function urlServidor(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

// Tamaño de archivo legible: 412 KB, 1,2 MB
function tamanoLegible(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }
    return max(1, (int) round($bytes / 1024)) . ' KB';
}

// ---------- Ayudas para los formularios del panel ----------

// Mensaje de error debajo de un campo (vacio si el campo esta bien)
function errorCampo(array $errores, string $campo): string
{
    if (!isset($errores[$campo])) {
        return '';
    }
    return '<p class="mensaje-error" id="error-' . e($campo) . '" role="alert">'
        . '<svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>'
        . e($errores[$campo]) . '</p>';
}

function claseCampo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? 'campo con-error' : 'campo';
}

// Fecha de la base (2026-10-19) en formato uruguayo (19/10/2026)
function fechaCorta(?string $fecha): string
{
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}

// Valida una fecha escrita como AAAA-MM-DD (lo que manda un input type="date")
function fechaValida(string $fecha): bool
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    return $d !== false && $d->format('Y-m-d') === $fecha;
}
