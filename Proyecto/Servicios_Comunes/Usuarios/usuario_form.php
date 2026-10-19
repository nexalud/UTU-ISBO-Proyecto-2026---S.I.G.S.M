<?php
// Crear un usuario (le llega un correo para elegir su contraseña) o editar uno existente (?id=ID).

require_once __DIR__ . '/../Autenticacion/sesion.php';
require_once __DIR__ . '/../Autenticacion/correos.php';
$usuarioActual = requerirAdministrador();

$modelo = new UsuarioModelo();
$roles  = $modelo->listarRoles();
$idsValidos = array_map('intval', array_column($roles, 'id_rol'));
$idAdministrador = (int) (array_column($roles, 'id_rol', 'nombre_rol')['administrador'] ?? 0);

$idUsuario = (int) ($_GET['id'] ?? 0);
$usuario   = null;

if ($idUsuario) {
    $usuario = $modelo->buscarPorId($idUsuario);
    if (!$usuario) {
        guardarAviso('error', 'No se encontró el usuario.');
        header('Location: usuarios.php', true, 303);
        exit;
    }
}

$esYo = $usuario && (int) $usuario['id_usuario'] === (int) $usuarioActual['id_usuario'];

// Roles marcados: nombres -> ids
$idsPorNombre = array_map('intval', array_column($roles, 'id_rol', 'nombre_rol'));
$datos = [
    'nombre'         => $usuario['nombre'] ?? '',
    'apellido'       => $usuario['apellido'] ?? '',
    'nombre_usuario' => $usuario['nombre_usuario'] ?? '',
    'email'          => $usuario['email'] ?? '',
    'roles'          => $usuario
        ? array_values(array_intersect_key($idsPorNombre, array_flip($usuario['roles'])))
        : array_filter([$idsPorNombre['funcionario'] ?? 0]),
];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf($_POST['token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho tiempo. Probá de nuevo.';
    } else {
        $datos['nombre']         = trim($_POST['nombre'] ?? '');
        $datos['apellido']       = trim($_POST['apellido'] ?? '');
        $datos['nombre_usuario'] = trim($_POST['nombre_usuario'] ?? '');
        $datos['email']          = mb_strtolower(trim($_POST['email'] ?? ''));
        $datos['roles']          = array_values(array_intersect(array_map('intval', (array) ($_POST['roles'] ?? [])), $idsValidos));

        foreach (['nombre' => 'el nombre', 'apellido' => 'el apellido'] as $campo => $texto) {
            $largo = mb_strlen($datos[$campo]);
            if ($largo < 2 || $largo > 100) {
                $errores[$campo] = 'Escribí ' . $texto . ' (entre 2 y 100 caracteres).';
            }
        }

        if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $datos['nombre_usuario'])) {
            $errores['nombre_usuario'] = 'Entre 4 y 50 caracteres: letras sin tilde, números, punto, guion o guion bajo.';
        } elseif ($modelo->existeNombre($datos['nombre_usuario'], $idUsuario)) {
            $errores['nombre_usuario'] = 'Ese usuario ya existe.';
        }

        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['email']) > 150) {
            $errores['email'] = 'Escribí un correo válido. Ahí le llega el enlace para la contraseña.';
        } elseif ($modelo->existeEmail($datos['email'], $idUsuario)) {
            $errores['email'] = 'Ya hay un usuario con ese correo.';
        }

        $quedaAdmin = in_array($idAdministrador, $datos['roles'], true);
        if (!$datos['roles']) {
            $errores['roles'] = 'Elegí al menos un rol.';
        } elseif ($esYo && esAdministrador($usuario) && !$quedaAdmin) {
            $errores['roles'] = 'No te podés sacar el rol de administrador a vos mismo.';
        } elseif ($usuario && esAdministrador($usuario) && !$quedaAdmin && (int) $usuario['activo'] === 1
            && $modelo->contarAdministradoresActivos((int) $usuario['id_usuario']) === 0) {
            $errores['roles'] = 'Es el único administrador activo: no se le puede sacar ese rol.';
        }

        if (!$errores) {
            try {
                if ($usuario) {
                    $modelo->actualizar($idUsuario, $datos, $datos['roles']);
                    guardarAviso('ok', 'Se guardaron los cambios de ' . $datos['nombre'] . ' ' . $datos['apellido'] . '.');
                } else {
                    $idNuevo = $modelo->crear($datos, $datos['roles']);
                    $resultado = enviarEnlaceContrasena($modelo->buscarPorId($idNuevo), 'bienvenida');

                    if ($resultado === Correo::ENVIADO) {
                        guardarAviso('ok', 'Se creó el usuario ' . $datos['nombre_usuario'] . '. Le llegó un correo a ' . $datos['email'] . ' para crear su contraseña.');
                    } elseif ($resultado === Correo::GUARDADO) {
                        guardarAviso('ok', 'Se creó el usuario ' . $datos['nombre_usuario'] . '. (Modo prueba: el correo quedó guardado en Servicios_Comunes/Correo/salida.)');
                    } else {
                        guardarAviso('error', 'Se creó el usuario, pero no se pudo enviar el correo. Usá "Enviar enlace" cuando el correo esté configurado.');
                    }
                }
                header('Location: usuarios.php', true, 303);
                exit;
            } catch (mysqli_sql_exception $ex) {
                error_log('Error al guardar usuario: ' . $ex->getMessage());
                $errores['general'] = 'No se pudo guardar el usuario. Probá de nuevo.';
            }
        }
    }
}

$token = tokenCsrf();

require __DIR__ . '/../Vista_General/usuario_form.php';
