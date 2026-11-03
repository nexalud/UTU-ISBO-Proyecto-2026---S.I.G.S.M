<?php
// Vista: panel de gestion de traslados. Variables: $resumen, $traslados, $vistas, $vista, $busqueda, $aviso
require __DIR__ . '/_funciones.php';
$raiz = '../../';
$seccionActiva = 'traslados';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Traslados · S.I.G.S.M.</title>
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
        <h1>Traslados</h1>
        <p>Seguimiento de las ambulancias y vehículos, desde la salida hasta el regreso al hospital.</p>
      </div>
      <a class="btn btn-primario" href="traslado_form.php">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
        Nuevo traslado
      </a>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>

    <div class="indicadores">
      <div class="indicador">
        <span class="indicador-titulo">En curso</span>
        <span class="indicador-valor"><?= $resumen['en_curso'] ?></span>
        <span class="indicador-detalle">en viaje, en destino o regresando</span>
      </div>
      <div class="indicador">
        <span class="indicador-titulo">Programados</span>
        <span class="indicador-valor"><?= $resumen['programados'] ?></span>
        <span class="indicador-detalle">esperando la salida</span>
      </div>
      <a class="indicador indicador-enlace" href="flota.php">
        <span class="indicador-titulo">Vehículos libres</span>
        <span class="indicador-valor"><?= $resumen['libres'] ?> <small>/ <?= $resumen['activos'] ?></small></span>
        <span class="indicador-detalle">de los que están en servicio</span>
      </a>
      <div class="indicador">
        <span class="indicador-titulo">Finalizados hoy</span>
        <span class="indicador-valor"><?= $resumen['finalizados_hoy'] ?></span>
        <span class="indicador-detalle">volvieron al hospital</span>
      </div>
    </div>

    <nav class="chips" aria-label="Filtrar traslados">
      <?php foreach ($vistas as $clave => $texto): ?>
        <a class="chip<?= $clave === $vista ? ' activo' : '' ?>" href="traslados.php?vista=<?= e($clave) ?>" <?= $clave === $vista ? 'aria-current="true"' : '' ?>><?= e($texto) ?></a>
      <?php endforeach; ?>
    </nav>

    <section class="tarjeta lista-docs">
      <div class="lista-cabecera">
        <h2><?= e($vistas[$vista]) ?></h2>
        <form class="buscador" method="get" action="traslados.php" role="search">
          <input type="hidden" name="vista" value="<?= e($vista) ?>">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
          <input type="search" name="q" id="buscar-traslado" value="<?= e($busqueda) ?>" placeholder="Paciente (cédula o nombre) o matrícula" aria-label="Buscar por paciente o vehículo">
        </form>
      </div>
      <?php if (!$traslados): ?>
        <p class="vacio"><?= $busqueda !== '' ? 'No hay traslados que coincidan con "' . e($busqueda) . '".' : ($vista === 'activos' ? 'No hay traslados en curso ni programados.' : 'No hay traslados para mostrar.') ?></p>
      <?php else: ?>
        <div class="tabla-scroll">
          <table class="tabla tabla-traslados">
            <thead>
              <tr>
                <th scope="col">Traslado</th>
                <th scope="col">Recorrido</th>
                <th scope="col">Vehículo y personal</th>
                <th scope="col">Salida</th>
                <th scope="col">Llegada</th>
                <th scope="col">Estado</th>
                <th scope="col" class="col-acciones"><span class="sr-only">Ver</span></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($traslados as $t): $id = (int) $t['id_traslado']; ?>
                <tr>
                  <td>
                    <strong class="codigo"><?= e(codigoTraslado($id)) ?></strong>
                    <span class="sub"><?= e(queSeLleva($t)) ?></span>
                  </td>
                  <td>
                    <span class="recorrido"><?= e($t['origen']) ?> <span aria-hidden="true">→</span><span class="sr-only">a</span> <?= e($t['destino']) ?></span>
                    <?php if ($t['distancia_km']): ?><span class="sub"><?= e(rtrim(rtrim($t['distancia_km'], '0'), '.')) ?> km · <?= (int) $t['tiempo_min'] ?> min</span><?php endif; ?>
                  </td>
                  <td>
                    <?php if ($t['id_proveedor']): ?>
                      <span class="recorrido"><?= e($t['proveedor']) ?></span>
                      <span class="sub">Empresa externa</span>
                    <?php else: ?>
                      <span class="recorrido"><?= e($t['matricula']) ?> · <?= e($t['vehiculo']) ?></span>
                      <span class="sub"><?= e($t['chofer']) ?> · <?= e($t['enfermero']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="sin-corte">
                    <?= e(hora($t['hora_salida_real'] ?: $t['hora_salida_prev'])) ?>
                    <span class="sub"><?= $t['hora_salida_real'] ? 'real' : 'prevista' ?></span>
                  </td>
                  <td class="sin-corte">
                    <?= e(hora($t['hora_llegada_real'] ?: $t['hora_llegada_prev'])) ?>
                    <span class="sub"><?= $t['hora_llegada_real'] ? 'real' : 'estimada' ?></span>
                  </td>
                  <td><span class="estado <?= claseEstado((int) $t['id_estado']) ?>"><?= e($t['estado']) ?></span></td>
                  <td class="col-acciones">
                    <a class="btn btn-secundario btn-chico" href="traslado.php?id=<?= $id ?>">Seguimiento</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <?php if ($vista === 'activos'): ?>
        <p class="nota">Esta pantalla se actualiza sola cada 30 segundos.</p>
      <?php endif; ?>
    </section>
  </main>
</div>
<?php if ($vista === 'activos'): ?>
  <script>
    // Seguimiento "en vivo": recarga la lista cada 30 segundos
    // (no recarga si la persona esta escribiendo en el buscador)
    setInterval(function () {
      if (document.activeElement && document.activeElement.id === 'buscar-traslado') { return; }
      window.location.reload();
    }, 30000);
  </script>
<?php endif; ?>
</body>
</html>
