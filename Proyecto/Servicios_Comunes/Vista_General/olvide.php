<?php
// Vista: olvide mi contraseña. Variables: $error, $texto, $enviado, $modoPrueba, $token
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contraseña · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista_General/assets/css/panel.css">
</head>
<body>
<div class="login">
  <?php require __DIR__ . '/marca_login.php'; ?>

  <section class="login-lado">
    <?php if ($enviado): ?>
      <div class="login-form">
        <div class="icono-grande" aria-hidden="true">
          <svg class="icono" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
        </div>
        <div>
          <h2>Revisá tu correo</h2>
          <p>Si el usuario existe, te mandamos un enlace para elegir una contraseña nueva. Vence en <?= RecuperacionModelo::MINUTOS_RESTABLECER ?> minutos.</p>
        </div>
        <p class="nota" style="font-size:14px">¿No llegó? Revisá la carpeta de spam. Si sigue sin llegar, pedile al administrador del sistema que te lo reenvíe.</p>
        <?php if ($modoPrueba): ?>
          <div class="aviso info">Modo prueba: el correo no sale, se guarda en la carpeta Servicios_Comunes/Correo/salida.</div>
        <?php endif; ?>
        <a class="btn btn-primario" href="login.php">Volver a iniciar sesión</a>
      </div>
    <?php else: ?>
      <form class="login-form" method="post" action="olvide.php" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div>
          <h2>¿Olvidaste tu contraseña?</h2>
          <p>Escribí tu usuario o tu correo y te mandamos un enlace para elegir una nueva.</p>
        </div>

        <?php if ($error): ?>
          <div class="aviso error" role="alert">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
            <?= e($error) ?>
          </div>
        <?php endif; ?>

        <div class="campo">
          <label for="usuario">Usuario o correo</label>
          <input id="usuario" name="usuario" type="text" autocomplete="username" autocapitalize="none" spellcheck="false"
                 value="<?= e($texto) ?>" placeholder="Ej: tLeites" required autofocus>
        </div>

        <button type="submit" class="btn btn-primario">Enviar enlace</button>
        <p class="login-ayuda"><a href="login.php">Volver a iniciar sesión</a></p>
      </form>
    <?php endif; ?>
  </section>
</div>
</body>
</html>
