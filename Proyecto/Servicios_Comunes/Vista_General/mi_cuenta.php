<?php
// Vista: mi cuenta. Variables: $datos, $errores, $aviso, $token, $usuarioActual
$raiz = '../../';
$seccionActiva = 'mi_cuenta';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi cuenta · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista_General/assets/css/panel.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/sidebar.php'; ?>

  <main class="contenido">
    <div class="encabezado">
      <div>
        <h1>Mi cuenta</h1>
        <p>Tus datos y tu contraseña.</p>
      </div>
    </div>

    <?php require __DIR__ . '/aviso.php'; ?>
    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>

    <div class="fila-columnas">
      <form class="tarjeta formulario" method="post" action="mi_cuenta.php" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <h2>Cambiar contraseña</h2>

        <div class="<?= claseCampo($errores, 'actual') ?>">
          <label for="actual">Contraseña actual</label>
          <div class="campo-clave">
            <input id="actual" name="actual" type="password" autocomplete="current-password" required>
            <button type="button" class="btn-icono" data-ver-clave="actual" aria-label="Mostrar contraseña" aria-pressed="false">
              <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
          <?= errorCampo($errores, 'actual') ?>
        </div>

        <div class="campos-fila">
          <div class="<?= claseCampo($errores, 'nueva') ?>">
            <label for="nueva">Contraseña nueva</label>
            <div class="campo-clave">
              <input id="nueva" name="nueva" type="password" autocomplete="new-password" required aria-describedby="reglas-clave">
              <button type="button" class="btn-icono" data-ver-clave="nueva" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
            <span class="ayuda" id="reglas-clave">Mínimo 8 caracteres, con al menos una letra y un número.</span>
            <?= errorCampo($errores, 'nueva') ?>
          </div>
          <div class="campo">
            <label for="repetir">Repetí la contraseña nueva</label>
            <div class="campo-clave">
              <input id="repetir" name="repetir" type="password" autocomplete="new-password" required>
              <button type="button" class="btn-icono" data-ver-clave="repetir" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>
        </div>

        <div class="botones-form">
          <button type="submit" class="btn btn-primario">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
            Cambiar contraseña
          </button>
        </div>
      </form>

      <aside class="tarjeta tarjeta-ayuda">
        <h2>Tus datos</h2>
        <dl class="datos-lista">
          <dt>Nombre</dt><dd><?= e($datos['nombre'] . ' ' . $datos['apellido']) ?></dd>
          <dt>Usuario</dt><dd><?= e($datos['nombre_usuario']) ?></dd>
          <dt>Correo</dt><dd><?= e($datos['email']) ?></dd>
          <dt>Roles</dt><dd><?= e(implode(', ', array_map('nombreRol', $datos['roles']))) ?></dd>
        </dl>
        <p>Para cambiar tu nombre, correo o roles, pedíselo al administrador del sistema.</p>
      </aside>
    </div>
  </main>
</div>
<script src="../Vista_General/assets/js/panel.js"></script>
</body>
</html>
