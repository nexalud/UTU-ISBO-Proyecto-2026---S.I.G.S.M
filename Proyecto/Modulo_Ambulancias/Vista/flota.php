<?php
// Vista: flota de vehiculos. Variables: $vehiculos, $busqueda, $aviso, $token
require __DIR__ . '/_funciones.php';
$raiz = '../../';
$seccionActiva = 'flota';
$enServicio = count(array_filter($vehiculos, fn($v) => (int) $v['activo'] === 1));
$ocupados = count(array_filter($vehiculos, fn($v) => $v['en_traslado']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flota · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../Servicios_Comunes/Vista_General/assets/css/panel.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/sidebar.php'; ?>

  <main class="contenido">
    <div class="encabezado">
      <div>
        <h1>Flota</h1>
        <p>Ambulancias y autos del hospital. Los choferes pueden manejar cualquiera.</p>
      </div>
      <a class="btn btn-primario" href="vehiculo_form.php">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
        Nuevo vehículo
      </a>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>

    <div class="etiquetas">
      <span class="etiqueta-info"><?= count($vehiculos) ?> vehículos</span>
      <span class="etiqueta-info verde"><?= $enServicio ?> en servicio</span>
      <span class="etiqueta-info"><?= $ocupados ?> en un traslado</span>
    </div>

    <section class="tarjeta lista-docs">
      <div class="lista-cabecera">
        <h2>Vehículos</h2>
        <form class="buscador" method="get" action="flota.php" role="search">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
          <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Matrícula, marca o tipo" aria-label="Buscar vehículo">
        </form>
      </div>

      <?php if (!$vehiculos): ?>
        <p class="vacio">No hay vehículos que coincidan con "<?= e($busqueda) ?>".</p>
      <?php else: ?>
      <div class="tabla-scroll">
        <table class="tabla" data-ordenable>
          <thead>
            <tr>
              <th scope="col" data-orden="texto">Vehículo</th>
              <th scope="col" data-orden="texto">Tipo</th>
              <th scope="col" data-orden="numero">Año</th>
              <th scope="col" data-orden="texto">Estado</th>
              <th scope="col" class="col-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($vehiculos as $v): $id = (int) $v['id_vehiculo']; $activo = (int) $v['activo'] === 1; ?>
              <tr>
                <td>
                  <div class="doc-celda">
                    <span class="icono-tipo documental" aria-hidden="true">
                      <svg class="icono" viewBox="0 0 24 24"><path d="M3 16V7h11v9"></path><path d="M14 10h4l3 3v3h-7"></path><circle cx="7" cy="17.5" r="2"></circle><circle cx="17" cy="17.5" r="2"></circle></svg>
                    </span>
                    <div>
                      <strong><?= e($v['matricula']) ?></strong>
                      <span><?= e($v['marca'] . ' ' . $v['modelo']) ?></span>
                    </div>
                  </div>
                </td>
                <td><?= e($v['tipo']) ?></td>
                <td><?= (int) $v['anho'] ?></td>
                <td>
                  <?php if (!$activo): ?>
                    <span class="estado inactivo">Fuera de servicio</span>
                  <?php elseif ($v['en_traslado']): ?>
                    <a class="estado en-viaje" href="traslado.php?id=<?= (int) $v['en_traslado'] ?>">En <?= e(codigoTraslado((int) $v['en_traslado'])) ?></a>
                  <?php else: ?>
                    <span class="estado activo">Libre</span>
                  <?php endif; ?>
                </td>
                <td class="col-acciones">
                  <div class="acciones-fila">
                    <a class="btn-icono" href="vehiculo_form.php?id=<?= $id ?>" aria-label="Editar <?= e($v['matricula']) ?>" title="Editar">
                      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path><path d="M13.5 6.5l4 4"></path></svg>
                    </a>
                    <?php if (!$v['en_traslado']): ?>
                      <form method="post" action="flota.php"<?= $activo ? ' data-confirmar="¿Sacar de servicio ' . e($v['matricula']) . '? No va a aparecer para nuevos traslados."' : '' ?>>
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn-icono<?= $activo ? ' peligro' : '' ?>" aria-label="<?= $activo ? 'Sacar de servicio' : 'Poner en servicio' ?> <?= e($v['matricula']) ?>" title="<?= $activo ? 'Sacar de servicio' : 'Poner en servicio' ?>">
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
      <?php endif; ?>
      <p class="nota">Los vehículos no se borran: se sacan de servicio, así queda su historial de traslados. Qué puede llevar cada tipo se define en la base (compatibilidad).</p>
    </section>
  </main>
</div>
<script src="../../Servicios_Comunes/Vista_General/assets/js/panel.js"></script>
</body>
</html>
