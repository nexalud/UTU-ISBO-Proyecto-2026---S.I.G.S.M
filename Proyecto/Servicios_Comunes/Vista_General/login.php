<?php
// Vista del login. Variables: $error, $usuario, $aviso, $token
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista_General/assets/css/panel.css">
</head>
<body>
<div class="login">
  <?php require __DIR__ . '/marca_login.php'; ?>

  <section class="login-lado">
    <form class="login-form" method="post" action="login.php" novalidate>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div>
        <h2>Iniciar sesión</h2>
        <p>Ingresá con tu usuario del sistema.</p>
      </div>

      <?php if ($aviso): ?>
        <div class="aviso <?= $aviso['tipo'] === 'ok' ? 'ok' : 'error' ?>" role="status"><?= e($aviso['texto']) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="aviso error" role="alert">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <div class="campo">
        <label for="usuario">Usuario</label>
        <input id="usuario" name="usuario" type="text" autocomplete="username" autocapitalize="none" spellcheck="false"
               value="<?= e($usuario) ?>" placeholder="Ej: tLeites" required <?= $usuario === '' ? 'autofocus' : '' ?>>
      </div>

      <div class="campo">
        <label for="contrasena">Contraseña</label>
        <div class="campo-clave">
          <input id="contrasena" name="contrasena" type="password" autocomplete="current-password"
                 placeholder="Tu contraseña" required <?= $usuario !== '' ? 'autofocus' : '' ?>>
          <button type="button" class="btn-icono" id="ver-clave" aria-label="Mostrar contraseña" aria-pressed="false">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primario">Ingresar</button>

      <p class="login-ayuda"><a href="olvide.php">¿Olvidaste tu contraseña?</a></p>
    </form>
  </section>
</div>
<script>
  var boton = document.getElementById('ver-clave');
  var clave = document.getElementById('contrasena');
  boton.addEventListener('click', function () {
    var mostrar = clave.type === 'password';
    clave.type = mostrar ? 'text' : 'password';
    boton.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
    boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
  });
</script>
</body>
</html>
