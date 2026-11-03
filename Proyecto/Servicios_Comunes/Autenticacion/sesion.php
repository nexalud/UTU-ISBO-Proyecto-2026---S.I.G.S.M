<?php
// Manejo de la sesion de los funcionarios.
// En cada pagina del panel: require este archivo y llamar a requerirFuncionario().

require_once __DIR__ . '/../Seguridad/helpers.php';
require_once __DIR__ . '/UsuarioModelo.php';

// Roles que pueden entrar al panel (choferes y enfermeros no entran: solo se asignan a los traslados)
const ROLES_PANEL = ['administrador', 'funcionario', 'operador_ue'];

// Que roles pueden usar cada modulo. El administrador entra a todo.
const ROLES_MODULO = [
    'documentacion' => ['administrador', 'funcionario'],   // documentos, categorias y encuestas
    'ambulancias'   => ['administrador', 'operador_ue'],   // traslados, flota y rutas
];

// Si no hay actividad en 30 minutos, la sesion se cierra sola
const MINUTOS_INACTIVIDAD = 30;

function iniciarSesion(array $usuario): void
{
    // Nuevo id de sesion al entrar: evita que alguien reutilice uno viejo
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id_usuario'     => (int) $usuario['id_usuario'],
        'nombre_usuario' => $usuario['nombre_usuario'],
        'nombre'         => $usuario['nombre'],
        'apellido'       => $usuario['apellido'],
        'roles'          => $usuario['roles'],
    ];
    $_SESSION['ultimo_acceso'] = time();
}

function cerrarSesion(): void
{
    // Se vacian los datos y se cambia el id: la sesion vieja queda inutilizable
    $_SESSION = [];
    session_regenerate_id(true);
}

function usuarioActual(): ?array
{
    if (empty($_SESSION['usuario'])) {
        return null;
    }

    if (time() - ($_SESSION['ultimo_acceso'] ?? 0) > MINUTOS_INACTIVIDAD * 60) {
        cerrarSesion();
        guardarAviso('error', 'Tu sesión se cerró por inactividad. Ingresá de nuevo.');
        return null;
    }

    // Se vuelve a leer de la base en cada pagina: si el administrador lo desactiva
    // o le saca un rol, el cambio vale al instante (no cuando vuelva a entrar)
    $datos = (new UsuarioModelo())->buscarPorId((int) $_SESSION['usuario']['id_usuario']);
    if (!$datos || (int) $datos['activo'] !== 1) {
        cerrarSesion();
        guardarAviso('error', 'Tu usuario fue desactivado. Consultá con el administrador del sistema.');
        return null;
    }
    $_SESSION['usuario']['nombre_usuario'] = $datos['nombre_usuario'];
    $_SESSION['usuario']['nombre']         = $datos['nombre'];
    $_SESSION['usuario']['apellido']       = $datos['apellido'];
    $_SESSION['usuario']['roles']          = $datos['roles'];

    $_SESSION['ultimo_acceso'] = time();
    return $_SESSION['usuario'];
}

function puedeEntrarAlPanel(array $usuario): bool
{
    return (bool) array_intersect($usuario['roles'], ROLES_PANEL);
}

// Corta la pagina y manda al login si no hay un funcionario con sesion iniciada
function requerirFuncionario(): array
{
    $usuario = usuarioActual();

    if (!$usuario || !puedeEntrarAlPanel($usuario)) {
        $raiz = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        header('Location: ' . rtrim($raiz, '/') . '/Servicios_Comunes/Autenticacion/login.php');
        exit;
    }

    return $usuario;
}

function esAdministrador(array $usuario): bool
{
    return in_array('administrador', $usuario['roles'], true);
}

function puedeUsarModulo(array $usuario, string $modulo): bool
{
    return (bool) array_intersect($usuario['roles'], ROLES_MODULO[$modulo]);
}

// Pagina de inicio segun el rol: el de ambulancias arranca en Traslados
function urlInicio(array $usuario, string $raiz): string
{
    return puedeUsarModulo($usuario, 'documentacion')
        ? $raiz . 'Modulo_Documentacion/Controlador/inicio.php'
        : $raiz . 'Modulo_Ambulancias/Controlador/traslados.php';
}

// Como requerirFuncionario(), pero ademas tiene que tener permiso para ese modulo
function requerirModulo(string $modulo): array
{
    $usuario = requerirFuncionario();

    if (!puedeUsarModulo($usuario, $modulo)) {
        guardarAviso('error', 'No tenés permiso para entrar a esa sección.');
        $raiz = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/') . '/';
        header('Location: ' . urlInicio($usuario, $raiz), true, 303);
        exit;
    }

    return $usuario;
}

// Como requerirFuncionario(), pero ademas tiene que ser administrador (si no, vuelve al inicio)
function requerirAdministrador(): array
{
    $usuario = requerirFuncionario();

    if (!esAdministrador($usuario)) {
        guardarAviso('error', 'Esa sección es solo para administradores.');
        $raiz = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/') . '/';
        header('Location: ' . urlInicio($usuario, $raiz), true, 303);
        exit;
    }

    return $usuario;
}

// Reglas para una contraseña nueva. Devuelve el error o null si esta bien.
function errorContrasenaNueva(string $contrasena, string $repetida, string $nombreUsuario): ?string
{
    $largo = mb_strlen($contrasena);
    if ($largo < 8) {
        return 'La contraseña tiene que tener al menos 8 caracteres.';
    }
    if (strlen($contrasena) > 72) {
        return 'La contraseña es demasiado larga (máximo 72 caracteres).';
    }
    if (mb_strtolower($contrasena) === mb_strtolower($nombreUsuario)) {
        return 'La contraseña no puede ser igual a tu usuario.';
    }
    if (!preg_match('/[A-Za-zÁÉÍÓÚáéíóúÑñ]/u', $contrasena) || !preg_match('/\d/', $contrasena)) {
        return 'Usá al menos una letra y un número.';
    }
    if ($contrasena !== $repetida) {
        return 'Las dos contraseñas no coinciden.';
    }
    return null;
}

// Nombre para mostrar de cada rol de la base
function nombreRol(string $rol): string
{
    $nombres = [
        'administrador' => 'Administrador',
        'funcionario'   => 'Administrativo de documentación',
        'operador_ue'   => 'Administrativo de ambulancias',
        'enfermero'     => 'Enfermero',
        'chofer'        => 'Chofer',
    ];
    return $nombres[$rol] ?? ucfirst(str_replace('_', ' ', $rol));
}

// Texto para mostrar el rol en el menu (el de mas permisos)
function rolPrincipal(array $usuario): string
{
    foreach (ROLES_PANEL as $rol) {
        if (in_array($rol, $usuario['roles'], true)) {
            return nombreRol($rol);
        }
    }
    return '';
}
