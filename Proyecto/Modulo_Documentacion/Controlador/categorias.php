<?php
// Panel del funcionario: listado de categorias, su QR y borrado de las que estan vacias.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/CategoriaModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo = new CategoriaModelo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf($_POST['token'] ?? null)) {
        guardarAviso('error', 'La página estuvo abierta mucho tiempo. Probá de nuevo.');
    } elseif (($_POST['accion'] ?? '') === 'eliminar') {
        $categoria = $modelo->obtener((int) ($_POST['id'] ?? 0));

        if (!$categoria) {
            guardarAviso('error', 'No se encontró la categoría.');
        } elseif ($modelo->eliminar((int) $categoria['id_categoria'])) {
            guardarAviso('ok', 'Se eliminó la categoría "' . $categoria['nombre'] . '".');
        } else {
            guardarAviso('error', 'No se puede eliminar "' . $categoria['nombre'] . '" porque tiene documentos o encuestas.');
        }
    }

    header('Location: categorias.php', true, 303);
    exit;
}

$categorias = $modelo->listar();

// Categoria elegida para mostrar su QR (?ver=ID)
$idVer = (int) ($_GET['ver'] ?? 0);
$categoriaElegida = null;
foreach ($categorias as $c) {
    if ((int) $c['id_categoria'] === $idVer) {
        $categoriaElegida = $c;
    }
}

$cantidadPorTipo = array_count_values(array_column($categorias, 'tipo_categoria'));

// Todos los QR apuntan a la pagina del paciente; si la categoria es de encuesta, esa pagina redirige
$urlQrBase = urlServidor() . dirname($_SERVER['SCRIPT_NAME']) . '/paciente.php?qr=';

$aviso = tomarAviso();
$token = tokenCsrf();

require __DIR__ . '/../Vista/categorias.php';
