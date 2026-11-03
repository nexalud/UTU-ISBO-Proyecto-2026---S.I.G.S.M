<?php
// Pagina publica a la que apunta el QR de cada categoria: paciente.php?qr=QR-CAT-0001
// Muestra los documentos activos del area y, si el area tiene una encuesta abierta, una tarjeta para responderla.
// Asi el paciente escanea UN solo QR para todo.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';
require_once __DIR__ . '/../../Modulo_Encuestas/Modelo/EncuestaModelo.php';

$codigoQr   = trim($_GET['qr'] ?? '');
$categoria  = null;
$documentos = [];
$encuesta   = null;
$yaRespondio = false;

// Un QR valido solo tiene letras, numeros y guiones: cualquier otra cosa ni se busca en la base
if (preg_match('/^[A-Za-z0-9-]{1,50}$/', $codigoQr)) {
    $modelo    = new DocumentoModelo();
    $categoria = $modelo->buscarCategoriaPorQr($codigoQr);

    if ($categoria) {
        // Solo se muestran los documentos cuyo PDF existe en el servidor
        foreach ($modelo->documentosActivosDeCategoria((int) $categoria['id_categoria']) as $d) {
            $ruta = DocumentoModelo::CARPETA_ARCHIVOS . basename($d['archivo_url']);
            if (is_file($ruta)) {
                $d['tamano'] = tamanoLegible(filesize($ruta));
                $documentos[] = $d;
            }
        }

        // La encuesta abierta de esta area (si no hay ninguna, no se muestra la tarjeta)
        $encuesta = (new EncuestaModelo())->buscarPorQr($codigoQr);

        // Si desde este celular ya la respondio, en vez del boton se le agradece
        $yaRespondio = $encuesta && !empty($_SESSION['encuestas_respondidas'][(int) $encuesta['id_encuesta']]);
    }
}

if (!$categoria) {
    http_response_code(404);
}

require __DIR__ . '/../Vista/paciente.php';
