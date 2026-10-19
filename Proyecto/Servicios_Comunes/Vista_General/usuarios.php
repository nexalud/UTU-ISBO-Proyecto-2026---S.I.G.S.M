<?php
// Vista: listado de usuarios (solo administradores). Variables: $usuarios, $activos, $aviso, $token, $usuarioActual
$raiz = '../../';
$seccionActiva = 'usuarios';

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios · S.I.G.S.M.</title>
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
        <h1>Usuarios</h1>
        <p>Quién puede entrar al sistema y con qué rol. Cada uno elige su contraseña con el enlace que le llega por correo.</p>
      </div>
      <a class="btn btn-primario" href="usuario_form.php">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
        Nuevo usuario
      </a>
    </div>

    <?php require __DIR__ . '/aviso.php'; ?>

    <div class="etiquetas">
      <span class="etiqueta-info"><?= count($usuarios) ?> usuarios</span>
      <span class="etiqueta-info verde"><?= $activos ?> activos</span>
      <?php if (count($usuarios) - $activos): ?><span class="etiqueta-info gris"><?= count($usuarios) - $activos ?> desactivados</span><?php endif; ?>
    </div>

    <section class="tarjeta lista-docs">
      <div class="tabla-scroll">
        <table class="tabla">
          <thead>
            <tr>
              <th scope="col">Persona</th>
              <th scope="col">Usuario</th>
              <th scope="col">Roles</th>
              <th scope="col">Estado</th>
              <th scope="col" class="col-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u):
              $id = (int) $u['id_usuario'];
              $activo = (int) $u['activo'] === 1;
              $esYo = $id === (int) $usuarioActual['id_usuario'];
              $nombreCompleto = $u['nombre'] . ' ' . $u['apellido'];
            ?>
              <tr>
                <td>
                  <div class="doc-celda">
                    <span class="avatar<?= $activo ? '' : ' apagado' ?>" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1))) ?></span>
                    <div>
                      <strong><?= e($nombreCompleto) ?><?= $esYo ? ' <small class="gris">(vos)</small>' : '' ?></strong>
                      <span><?= e($u['email']) ?></span>
                    </div>
                  </div>
                </td>
                <td><?= e($u['nombre_usuario']) ?></td>
                <td>
                  <div class="chips-roles">
                    <?php foreach ($u['roles'] as $rol): ?>
                      <span class="estado <?= in_array($rol, ROLES_PANEL, true) ? 'documental' : 'inactivo' ?>"><?= e(nombreRol($rol)) ?></span>
                    <?php endforeach; ?>
                    <?php if (!$u['roles']): ?><span class="gris">Sin roles</span><?php endif; ?>
                  </div>
                </td>
                <td><span class="estado <?= $activo ? 'activo' : 'inactivo' ?>"><?= $activo ? 'Activo' : 'Desactivado' ?></span></td>
                <td class="col-acciones">
                  <div class="acciones-fila">
                    <a class="btn-icono" href="usuario_form.php?id=<?= $id ?>" aria-label="Editar a <?= e($nombreCompleto) ?>" title="Editar">
                      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path><path d="M13.5 6.5l4 4"></path></svg>
                    </a>
                    <?php if ($activo): ?>
                      <form method="post" action="usuarios.php" data-confirmar="¿Mandarle a <?= e($nombreCompleto) ?> un correo para restablecer su contraseña?">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="accion" value="enlace">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn-icono" aria-label="Enviar enlace de contraseña a <?= e($nombreCompleto) ?>" title="Enviar enlace para restablecer la contraseña">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if (!$esYo): ?>
                      <form method="post" action="usuarios.php"<?= $activo ? ' data-confirmar="¿Desactivar a ' . e($nombreCompleto) . '? No va a poder entrar al sistema."' : '' ?>>
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn-icono<?= $activo ? ' peligro' : '' ?>" aria-label="<?= $activo ? 'Desactivar' : 'Activar' ?> a <?= e($nombreCompleto) ?>" title="<?= $activo ? 'Desactivar' : 'Activar' ?>">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v9"></path><path d="M6.3 7.3a8 8 0 1 0 11.4 0"></path></svg>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="nota">Los usuarios no se borran: se desactivan, así queda registro de los documentos que cargaron. Al panel entran los roles Administrador y Funcionario.</p>
    </section>
  </main>
</div>
<script src="../Vista_General/assets/js/panel.js"></script>
</body>
</html>
