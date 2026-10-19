<?php
// Formulario para cargar un documento PDF nuevo.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo     = new DocumentoModelo();
$categorias = $modelo->listarCategorias();
$documento  = null;   // la vista usa esto para saber si es nuevo o edicion

$errores = [];
$datos = [
    'titulo'      => '',
    'descripcion' => '',
    'categoria'   => (int) ($_GET['cat'] ?? 0),
    'activo'      => true,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si el archivo supera el limite de PHP, el formulario llega vacio
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errores['archivo'] = 'El archivo es demasiado grande. El máximo es 10 MB.';
    } elseif (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } else {
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

        $archivo = $_FILES['archivo'] ?? null;
        $errorArchivo = DocumentoModelo::validarPdf($archivo, true);
        if ($errorArchivo) {
            $errores['archivo'] = $errorArchivo;
        }

        if (!$errores) {
            $nombreArchivo = DocumentoModelo::guardarPdf($archivo['tmp_name']);

            if (!$nombreArchivo) {
                $errores['general'] = 'No se pudo guardar el archivo en el servidor.';
            } else {
                try {
                    $modelo->crear(
                        $datos['titulo'],
                        $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                        $nombreArchivo,
                        $datos['categoria'],
                        $usuarioActual['id_usuario'],
                        $datos['activo']
                    );

                    guardarAviso('ok', 'Se guardó "' . $datos['titulo'] . '".');
                    header('Location: documentos.php?cat=' . $datos['categoria'], true, 303);
                    exit;
                } catch (mysqli_sql_exception $ex) {
                    // Si falla la base, no dejamos el archivo huerfano
                    DocumentoModelo::borrarPdf($nombreArchivo);
                    error_log('Error al guardar documento: ' . $ex->getMessage());
                    $errores['general'] = 'No se pudo guardar el documento. Probá de nuevo.';
                }
            }
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista/documento_form.php';
