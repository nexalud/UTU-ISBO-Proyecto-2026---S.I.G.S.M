<?php
// Formulario para crear una categoria nueva o editar una existente (?id=ID).

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/CategoriaModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirModulo('documentacion');

$modelo = new CategoriaModelo();

$idCategoria = (int) ($_GET['id'] ?? 0);
$categoria   = null;

if ($idCategoria) {
    $categoria = $modelo->obtener($idCategoria);
    if (!$categoria) {
        guardarAviso('error', 'No se encontró la categoría.');
        header('Location: categorias.php', true, 303);
        exit;
    }
}

$errores = [];
$datos = [
    'nombre'      => $categoria['nombre'] ?? '',
    'descripcion' => $categoria['descripcion'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } else {
        $datos['nombre']      = trim($_POST['nombre'] ?? '');
        $datos['descripcion'] = trim($_POST['descripcion'] ?? '');

        $largo = mb_strlen($datos['nombre']);
        if ($largo < 3 || $largo > 100) {
            $errores['nombre'] = 'Escribí un nombre de entre 3 y 100 caracteres.';
        } elseif ($modelo->existeNombre($datos['nombre'], $idCategoria)) {
            $errores['nombre'] = 'Ya hay una categoría con ese nombre.';
        }

        if (mb_strlen($datos['descripcion']) > 255) {
            $errores['descripcion'] = 'La descripción puede tener hasta 255 caracteres.';
        }

        if (!$errores) {
            $descripcion = $datos['descripcion'] !== '' ? $datos['descripcion'] : null;

            try {
                if ($categoria) {
                    $modelo->actualizar($idCategoria, $datos['nombre'], $descripcion);
                    guardarAviso('ok', 'Se guardaron los cambios de "' . $datos['nombre'] . '".');
                } else {
                    $idCategoria = $modelo->crear($datos['nombre'], $descripcion);
                    guardarAviso('ok', 'Se creó la categoría "' . $datos['nombre'] . '". Ya podés imprimir su QR.');
                }

                header('Location: categorias.php?ver=' . $idCategoria, true, 303);
                exit;
            } catch (mysqli_sql_exception $ex) {
                error_log('Error al guardar categoria: ' . $ex->getMessage());
                $errores['general'] = 'No se pudo guardar la categoría. Probá de nuevo.';
            }
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista/categoria_form.php';
