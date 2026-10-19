<?php
// Editar un documento (?id=ID): datos, categoria, visibilidad y, si se quiere, reemplazar el PDF.
// Tambien permite eliminarlo (borra el registro y el archivo).

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo    = new DocumentoModelo();
$documento = $modelo->obtener((int) ($_GET['id'] ?? 0));

if (!$documento) {
    guardarAviso('error', 'No se encontró el documento.');
    header('Location: documentos.php', true, 303);
    exit;
}

$idDocumento = (int) $documento['id_documento'];
$categorias  = $modelo->listarCategorias();

$errores = [];
$datos = [
    'titulo'      => $documento['titulo'],
    'descripcion' => $documento['descripcion'] ?? '',
    'categoria'   => (int) $documento['id_categoria'],
    'activo'      => (bool) $documento['activo'],
];

$rutaPdf = DocumentoModelo::CARPETA_ARCHIVOS . basename($documento['archivo_url']);
$tamanoActual = is_file($rutaPdf) ? tamanoLegible((int) filesize($rutaPdf)) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errores['archivo'] = 'El archivo es demasiado grande. El máximo es 10 MB.';
    } elseif (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } elseif (($_POST['accion'] ?? '') === 'eliminar') {
        // ---------- Eliminar ----------
        try {
            if ($modelo->eliminar($idDocumento)) {
                DocumentoModelo::borrarPdf($documento['archivo_url']);
                guardarAviso('ok', 'Se eliminó "' . $documento['titulo'] . '".');
            }
        } catch (mysqli_sql_exception $ex) {
            error_log('Error al eliminar documento: ' . $ex->getMessage());
            guardarAviso('error', 'No se pudo eliminar el documento.');
        }
        header('Location: documentos.php?cat=' . (int) $documento['id_categoria'], true, 303);
        exit;
    } else {
        // ---------- Guardar cambios ----------
        $datos['titulo']      = trim($_POST['titulo'] ?? '');
        $datos['descripcion'] = trim($_POST['descripcion'] ?? '');
        $datos['categoria']   = (int) ($_POST['categoria'] ?? 0);
        $datos['activo']      = isset($_POST['activo']);

        $largoTitulo = mb_strlen($datos['titulo']);
        if ($largoTitulo < 3 || $largoTitulo > 150) {
            $errores['titulo'] = 'Escribí un título de entre 3 y 150 caracteres.';
        }
        if (mb_strlen($datos['descripcion']) > 255) {
            $errores['descripcion'] = 'La descripción puede tener hasta 255 caracteres.';
        }
        if (!$modelo->existeCategoriaDocumental($datos['categoria'])) {
            $errores['categoria'] = 'Elegí una categoría.';
        }

        // El PDF es opcional: si no eligen uno, queda el que estaba
        $archivo = $_FILES['archivo'] ?? null;
        $errorArchivo = DocumentoModelo::validarPdf($archivo, false);
        if ($errorArchivo) {
            $errores['archivo'] = $errorArchivo;
        }
        $hayArchivoNuevo = $archivo && $archivo['error'] === UPLOAD_ERR_OK;

        if (!$errores) {
            $nombreNuevo = null;
            if ($hayArchivoNuevo) {
                $nombreNuevo = DocumentoModelo::guardarPdf($archivo['tmp_name']);
                if (!$nombreNuevo) {
                    $errores['general'] = 'No se pudo guardar el archivo en el servidor.';
                }
            }

            if (!$errores) {
                try {
                    $modelo->actualizar(
                        $idDocumento,
                        $datos['titulo'],
                        $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                        $datos['categoria'],
                        $datos['activo'],
                        $nombreNuevo
                    );

                    // El PDF viejo se borra recien cuando la base ya apunta al nuevo
                    if ($nombreNuevo) {
                        DocumentoModelo::borrarPdf($documento['archivo_url']);
                    }

                    guardarAviso('ok', 'Se guardaron los cambios de "' . $datos['titulo'] . '".');
                    header('Location: documentos.php?cat=' . $datos['categoria'], true, 303);
                    exit;
                } catch (mysqli_sql_exception $ex) {
                    if ($nombreNuevo) {
                        DocumentoModelo::borrarPdf($nombreNuevo);
                    }
                    error_log('Error al editar documento: ' . $ex->getMessage());
                    $errores['general'] = 'No se pudieron guardar los cambios. Probá de nuevo.';
                }
            }
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista/documento_form.php';
