<?php
// Panel del funcionario: listado de documentos, filtro por categoria y QR de cada categoria.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo = new DocumentoModelo();

$idCategoria = max(0, (int) ($_GET['cat'] ?? 0));
$busqueda    = mb_substr(trim($_GET['q'] ?? ''), 0, 100);

// Activar / desactivar un documento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf($_POST['token'] ?? null)) {
        guardarAviso('error', 'La página estuvo abierta mucho tiempo. Probá de nuevo.');
    } elseif (($_POST['accion'] ?? '') === 'estado') {
        $ok = $modelo->cambiarEstado((int) ($_POST['id'] ?? 0));
        guardarAviso($ok ? 'ok' : 'error', $ok ? 'Se actualizó el estado del documento.' : 'No se encontró el documento.');
    }

    // Post/Redirect/Get: vuelve a la misma vista sin reenviar el formulario al recargar
    $parametros = http_build_query(array_filter(['cat' => $idCategoria ?: null, 'q' => $busqueda ?: null]));
    header('Location: documentos.php' . ($parametros ? '?' . $parametros : ''), true, 303);
    exit;
}

$categorias = $modelo->listarCategorias();
$documentos = $modelo->listarDocumentos($idCategoria, $busqueda);

$categoriaElegida = null;
foreach ($categorias as $c) {
    if ((int) $c['id_categoria'] === $idCategoria) {
        $categoriaElegida = $c;
    }
}

// Si piden una categoria que no existe, se muestran todas
if ($idCategoria && !$categoriaElegida) {
    $idCategoria = 0;
    $documentos  = $modelo->listarDocumentos(0, $busqueda);
}

$totalDocumentos = array_sum(array_column($categorias, 'total'));

// El QR lleva la direccion completa de la pagina del paciente
$urlQrBase = urlServidor() . dirname($_SERVER['SCRIPT_NAME']) . '/paciente.php?qr=';

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista/documentos.php';
