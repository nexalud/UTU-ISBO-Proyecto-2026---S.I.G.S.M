<?php
// Lector de PDF para el paciente: visor.php?id=5
// El documento se lee dentro de nuestra pagina (no se descarga) y al llegar al final
// aparece la encuesta del area, si hay una abierta.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';
require_once __DIR__ . '/../../Modulo_Encuestas/Modelo/EncuestaModelo.php';

$id = (int) ($_GET['id'] ?? 0);

$modelo    = new DocumentoModelo();
$documento = $id > 0 ? $modelo->obtenerParaVisor($id) : null;   // solo documentos activos

$encuesta    = null;
$yaRespondio = false;

if ($documento) {
    $encuesta    = (new EncuestaModelo())->buscarPorQr($documento['codigo_qr']);
    $yaRespondio = $encuesta && !empty($_SESSION['encuestas_respondidas'][(int) $encuesta['id_encuesta']]);
} else {
    http_response_code(404);
}

require __DIR__ . '/../Vista/visor.php';
