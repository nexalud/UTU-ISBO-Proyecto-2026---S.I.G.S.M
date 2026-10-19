<?php
// Entrega el PDF de un documento activo. ?id=5 lo muestra, ?id=5&descargar=1 lo descarga.
// Los pacientes solo ven documentos activos; un funcionario con sesion tambien puede ver los inactivos.

require_once __DIR__ . '/../Modelo/DocumentoModelo.php';
require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';

$id = (int) ($_GET['id'] ?? 0);

$modelo    = new DocumentoModelo();
$documento = $id > 0 ? $modelo->obtenerActivo($id) : null;

if (!$documento && $id > 0) {
    $funcionario = usuarioActual();
    if ($funcionario && puedeEntrarAlPanel($funcionario)) {
        $documento = $modelo->obtener($id);
    }
}

// basename evita que alguien use "../" para leer otros archivos del servidor
$ruta = $documento ? DocumentoModelo::CARPETA_ARCHIVOS . basename($documento['archivo_url']) : null;

if (!$ruta || !is_file($ruta)) {
    http_response_code(404);
    exit('El documento no está disponible.');
}

// Nombre amigable para el archivo descargado, a partir del titulo
$nombre = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $documento['titulo']);
$nombre = trim(preg_replace('/[^A-Za-z0-9]+/', '_', $nombre), '_') ?: 'documento';

$modo = isset($_GET['descargar']) ? 'attachment' : 'inline';

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . $modo . '; filename="' . $nombre . '.pdf"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');

readfile($ruta);
