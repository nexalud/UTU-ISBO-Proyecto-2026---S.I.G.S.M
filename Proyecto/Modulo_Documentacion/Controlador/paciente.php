<?php
// Pagina publica a la que apunta el QR de cada categoria: paciente.php?qr=QR-CAT-0001
// Si la categoria es de encuesta, lo manda a la encuesta.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

$codigoQr   = trim($_GET['qr'] ?? '');
$categoria  = null;
$documentos = [];

if (preg_match('/^[A-Za-z0-9-]{1,50}$/', $codigoQr)) {
    $modelo    = new DocumentoModelo();
    $categoria = $modelo->buscarCategoriaPorQr($codigoQr);

    if ($categoria && $categoria['tipo_categoria'] === 'encuesta') {
        header('Location: ../../Modulo_Encuestas/Controlador/encuesta.php?qr=' . urlencode($codigoQr), true, 302);
        exit;
    }

    if ($categoria) {
        // Solo se muestran los documentos cuyo PDF existe en el servidor
        foreach ($modelo->documentosActivosDeCategoria((int) $categoria['id_categoria']) as $d) {
            $ruta = DocumentoModelo::CARPETA_ARCHIVOS . basename($d['archivo_url']);
            if (is_file($ruta)) {
                $d['tamano'] = tamanoLegible(filesize($ruta));
                $documentos[] = $d;
            }
        }
    }
}

if (!$categoria) {
    http_response_code(404);
}

require __DIR__ . '/../Vista/paciente.php';
