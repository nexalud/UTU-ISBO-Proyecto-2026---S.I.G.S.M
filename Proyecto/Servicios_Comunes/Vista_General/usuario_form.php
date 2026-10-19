<?php
// Vista: formulario de usuario. Variables: $usuario (null si es nuevo), $datos, $roles, $errores, $token, $esYo
$raiz = '../../';
$seccionActiva = 'usuarios';
$titulo = $usuario ? 'Editar usuario' : 'Nuevo usuario';

// Los roles del modulo 1 primero y despues los de ambulancias
$rolesModulo1 = ['administrador', 'funcionario', 'usuario_basico'];
$gruposRoles = [
    'Documentación y encuestas' => array_filter($roles, fn($r) => in_array($r['nombre_rol'], $rolesModulo1, true)),
    'Ambulancias (módulo 2)'    => array_filter($roles, fn($r) => !in_array($r['nombre_rol'], $rolesModulo1, true)),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo) ?> · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista_General/assets/css/panel.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/sidebar.php'; ?>

  <main class="contenido">
    <div>
      <nav class="ruta" aria-label="Ruta">
        <a href="usuarios.php">Usuarios</a>
        <span aria-hidden="true">/</span>
        <span><?= e($usuario ? $usuario['nombre'] . ' ' . $usuario['apellido'] : 'Nuevo usuario') ?></span>
      </nav>
      <div class="encabezado"><h1><?= e($titulo) ?></h1></div>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>

    <div class="fila-columnas">
      <form class="tarjeta formulario" method="post" action="usuario_form.php<?= $usuario ? '?id=' . (int) $usuario['id_usuario'] : '' ?>" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="campos-fila">
          <div class="<?= claseCampo($errores, 'nombre') ?>">
            <label for="nombre">Nombre <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="nombre" name="nombre" type="text" maxlength="100" value="<?= e($datos['nombre']) ?>" autocomplete="off">
            <?= errorCampo($errores, 'nombre') ?>
          </div>
          <div class="<?= claseCampo($errores, 'apellido') ?>">
            <label for="apellido">Apellido <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="apellido" name="apellido" type="text" maxlength="100" value="<?= e($datos['apellido']) ?>" autocomplete="off">
            <?= errorCampo($errores, 'apellido') ?>
          </div>
        </div>

        <div class="campos-fila">
          <div class="<?= claseCampo($errores, 'nombre_usuario') ?>">
            <label for="nombre_usuario">Usuario <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="nombre_usuario" name="nombre_usuario" type="text" maxlength="50" value="<?= e($datos['nombre_usuario']) ?>"
                   placeholder="Ej: jPerez" autocapitalize="none" spellcheck="false" autocomplete="off">
            <span class="ayuda">Con este nombre entra al sistema.</span>
            <?= errorCampo($errores, 'nombre_usuario') ?>
          </div>
          <div class="<?= claseCampo($errores, 'email') ?>">
            <label for="email">Correo <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="email" name="email" type="email" maxlength="150" value="<?= e($datos['email']) ?>"
                   placeholder="Ej: jperez@hc.edu.uy" autocapitalize="none" spellcheck="false" autocomplete="off">
            <span class="ayuda">Acá le llegan los enlaces para la contraseña.</span>
            <?= errorCampo($errores, 'email') ?>
          </div>
        </div>

        <fieldset class="<?= claseCampo($errores, 'roles') ?> grupo-opciones">
          <legend class="etiqueta">Roles <span class="obligatorio" aria-hidden="true">*</span></legend>
          <?php foreach ($gruposRoles as $tituloGrupo => $lista): if (!$lista) { continue; } ?>
            <span class="etiqueta-chica"><?= e($tituloGrupo) ?></span>
            <div class="opciones-tarjeta">
              <?php foreach ($lista as $r): $idRol = (int) $r['id_rol']; ?>
                <label class="opcion-tarjeta">
                  <input type="checkbox" name="roles[]" value="<?= $idRol ?>" <?= in_array($idRol, $datos['roles'], true) ? 'checked' : '' ?>>
                  <span>
                    <strong><?= e(nombreRol($r['nombre_rol'])) ?></strong>
                    <small><?= e($r['descripcion']) ?></small>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
          <?= errorCampo($errores, 'roles') ?>
        </fieldset>

        <div class="botones-form">
          <a class="btn btn-secundario" href="usuarios.php">Cancelar</a>
          <button type="submit" class="btn btn-primario">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
            <?= $usuario ? 'Guardar cambios' : 'Crear y enviar correo' ?>
          </button>
        </div>
      </form>

      <aside class="tarjeta tarjeta-ayuda">
        <h2><?= $usuario ? 'Contraseña' : 'Cómo entra' ?></h2>
        <?php if ($usuario): ?>
          <p>Vos nunca ves ni elegís la contraseña de otra persona. Si se la olvidó, desde la lista de usuarios tocá el ícono del sobre y le llega un enlace para elegir una nueva.</p>
          <p>También la puede pedir sola con “¿Olvidaste tu contraseña?” en la pantalla de inicio de sesión.</p>
        <?php else: ?>
          <p>Al crear el usuario le llega un correo con un enlace para que <strong>elija su propia contraseña</strong>. El enlace vence en 48 horas.</p>
          <p>Si no le llega o se vence, desde la lista de usuarios podés mandarle uno nuevo.</p>
        <?php endif; ?>
        <p>Al panel entran los roles <strong>Administrador</strong> y <strong>Funcionario</strong>. Solo el administrador ve esta sección.</p>
      </aside>
    </div>
  </main>
</div>
</body>
</html>
