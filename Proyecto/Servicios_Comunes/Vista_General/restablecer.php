<?php
// Vista: elegir contraseña nueva desde el enlace del correo. Variables: $pedido (null si el enlace no sirve), $codigo, $error, $token
$esBienvenida = $pedido && $pedido['tipo'] === 'bienvenida';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- El codigo va en la direccion: que no se mande a otros sitios -->
  <meta name="referrer" content="no-referrer">
  <title><?= $esBienvenida ? 'Crear contraseña' : 'Restablecer contraseña' ?> · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista_General/assets/css/panel.css">
</head>
<body>
<div class="login">
  <?php require __DIR__ . '/marca_login.php'; ?>

  <section class="login-lado">
    <?php if (!$pedido): ?>
      <div class="login-form">
        <div class="icono-grande error" aria-hidden="true">
          <svg class="icono" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
        </div>
        <div>
          <h2>El enlace no sirve</h2>
          <p>Puede que haya vencido o que ya se haya usado. Pedí uno nuevo y usalo apenas te llegue.</p>
        </div>
        <a class="btn btn-primario" href="olvide.php">Pedir un enlace nuevo</a>
        <p class="login-ayuda"><a href="login.php">Volver a iniciar sesión</a></p>
      </div>
    <?php else: ?>
      <form class="login-form" method="post" action="restablecer.php" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <input type="hidden" name="codigo" value="<?= e($codigo) ?>">
        <div>
          <h2><?= $esBienvenida ? 'Creá tu contraseña' : 'Contraseña nueva' ?></h2>
          <p>Hola, <?= e($pedido['nombre']) ?>. Usuario: <strong><?= e($pedido['nombre_usuario']) ?></strong></p>
        </div>

        <?php if ($error): ?>
          <div class="aviso error" role="alert">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
            <?= e($error) ?>
          </div>
        <?php endif; ?>

        <div class="campo">
          <label for="contrasena">Contraseña nueva</label>
          <div class="campo-clave">
            <input id="contrasena" name="contrasena" type="password" autocomplete="new-password" required autofocus
                   aria-describedby="reglas-clave">
            <button type="button" class="btn-icono" data-ver-clave="contrasena" aria-label="Mostrar contraseña" aria-pressed="false">
              <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
          <span class="ayuda" id="reglas-clave">Mínimo 8 caracteres, con al menos una letra y un número.</span>
        </div>

        <div class="campo">
          <label for="repetir">Repetí la contraseña</label>
          <div class="campo-clave">
            <input id="repetir" name="repetir" type="password" autocomplete="new-password" required>
            <button type="button" class="btn-icono" data-ver-clave="repetir" aria-label="Mostrar contraseña" aria-pressed="false">
              <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primario">Guardar contraseña</button>
      </form>
    <?php endif; ?>
  </section>
</div>
<script src="../Vista_General/assets/js/panel.js"></script>
</body>
</html>
