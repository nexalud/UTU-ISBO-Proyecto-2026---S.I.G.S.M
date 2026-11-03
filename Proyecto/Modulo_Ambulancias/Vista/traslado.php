<?php
// Vista: seguimiento de un traslado.
// Variables: $traslado, $historial, $momentos, $pasos, $estado, $siguiente, $aviso, $token
require __DIR__ . '/_funciones.php';
$raiz = '../../';
$seccionActiva = 'traslados';
$t = $traslado;
$cancelado = $estado === TrasladoModelo::CANCELADO;
$enCurso = in_array($estado, TrasladoModelo::ACTIVOS, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(codigoTraslado((int) $t['id_traslado'])) ?> · S.I.G.S.M.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../Servicios_Comunes/Vista_General/assets/css/panel.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/sidebar.php'; ?>

  <main class="contenido">
    <div>
      <nav class="ruta" aria-label="Ruta">
        <a href="traslados.php">Traslados</a>
        <span aria-hidden="true">/</span>
        <span><?= e(codigoTraslado((int) $t['id_traslado'])) ?></span>
      </nav>
      <div class="encabezado">
        <div>
          <h1><?= e(codigoTraslado((int) $t['id_traslado'])) ?> · <?= e(queSeLleva($t)) ?></h1>
          <p><?= e($t['origen']) ?> → <?= e($t['destino']) ?></p>
        </div>
        <span class="estado estado-grande <?= claseEstado($estado) ?>"><?= e($t['estado']) ?></span>
      </div>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>

    <!-- Linea de tiempo del ciclo del traslado -->
    <?php if (!$cancelado): ?>
      <ol class="pasos-traslado" aria-label="Estado del traslado">
        <?php foreach ($pasos as $id => $nombre):
          $hecho = isset($momentos[$id]);
          $actual = $id === $estado;
        ?>
          <li class="<?= $hecho ? 'hecho' : '' ?><?= $actual ? ' actual' : '' ?>" <?= $actual ? 'aria-current="step"' : '' ?>>
            <span class="paso-punto" aria-hidden="true"></span>
            <strong><?= e($nombre) ?></strong>
            <span><?= $hecho ? e(hora($momentos[$id])) : '' ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>

    <div class="fila-columnas">
      <section class="tarjeta formulario">
        <h2>Datos del traslado</h2>
        <dl class="datos-lista datos-traslado">
          <dt>Qué se lleva</dt><dd><?= e($t['tipo_elemento']) ?><?= $t['paciente'] ? ' · ' . e($t['paciente']) . ' (CI ' . e($t['ci_paciente']) . ')' : '' ?></dd>
          <dt>Motivo</dt><dd><?= e($t['motivo'] ?: '–') ?></dd>
          <dt>Recorrido</dt><dd><?= e($t['origen']) ?> → <?= e($t['destino']) ?><?= $t['distancia_km'] ? ' · ' . e(rtrim(rtrim($t['distancia_km'], '0'), '.')) . ' km' : '' ?></dd>
          <?php if ($t['id_proveedor']): ?>
            <dt>Lo hace</dt><dd><?= e($t['proveedor']) ?> (empresa externa)</dd>
          <?php else: ?>
            <dt>Vehículo</dt><dd><?= e($t['tipo_vehiculo'] . ' · ' . $t['matricula'] . ' · ' . $t['vehiculo']) ?></dd>
            <dt>Chofer</dt><dd><?= e($t['chofer']) ?></dd>
            <dt>Enfermero</dt><dd><?= e($t['enfermero']) ?></dd>
          <?php endif; ?>
          <dt>Solicitud</dt><dd><?= e($t['canal']) ?> · <?= e(hora($t['fecha_solicitud'])) ?> · <?= e($t['registrado_por']) ?></dd>
        </dl>

        <div class="horas">
          <div><span>Salida</span><strong><?= e(hora($t['hora_salida_real'])) ?></strong><small>prevista <?= e(hora($t['hora_salida_prev'])) ?></small></div>
          <div><span>Llegada</span><strong><?= e(hora($t['hora_llegada_real'])) ?></strong><small>estimada <?= e(hora($t['hora_llegada_prev'])) ?></small></div>
          <div><span>Regreso</span><strong><?= e(hora($t['hora_retorno'])) ?></strong><small>al hospital</small></div>
        </div>
      </section>

      <div class="columna-lateral">
        <?php if ($siguiente): ?>
          <form class="tarjeta formulario" method="post" action="traslado.php?id=<?= (int) $t['id_traslado'] ?>">
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <input type="hidden" name="accion" value="avanzar">
            <input type="hidden" name="estado" value="<?= $estado ?>">
            <h2>Actualizar estado</h2>
            <div class="campo">
              <div class="campo-cabecera">
                <label for="obs-avanzar">Observación</label>
                <span class="chip-opcional">Opcional</span>
              </div>
              <textarea id="obs-avanzar" name="observaciones" maxlength="255" placeholder="Ej: Demora por tránsito en Av. Italia"></textarea>
            </div>
            <button type="submit" class="btn btn-primario">
              <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"></path><path d="M13 6l6 6-6 6"></path></svg>
              <?= e($siguiente) ?>
            </button>
            <p class="nota">Se guarda la hora de este momento.</p>
          </form>
        <?php endif; ?>

        <?php if ($estado === TrasladoModelo::PROGRAMADO): ?>
          <a class="btn btn-secundario" href="traslado_form.php?id=<?= (int) $t['id_traslado'] ?>">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path><path d="M13.5 6.5l4 4"></path></svg>
            Editar traslado
          </a>
          <form class="tarjeta zona-peligro" method="post" action="traslado.php?id=<?= (int) $t['id_traslado'] ?>"
                data-confirmar="¿Cancelar este traslado? Se liberan el vehículo y el personal.">
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <input type="hidden" name="accion" value="cancelar">
            <h2>Cancelar traslado</h2>
            <div class="campo">
              <label for="obs-cancelar">Motivo</label>
              <textarea id="obs-cancelar" name="observaciones" maxlength="255" required placeholder="Ej: El paciente fue dado de alta"></textarea>
            </div>
            <button type="submit" class="btn btn-peligro">Cancelar traslado</button>
          </form>
        <?php endif; ?>

        <section class="tarjeta tarjeta-ayuda">
          <h2>Historial</h2>
          <ol class="historial">
            <?php foreach (array_reverse($historial) as $h): ?>
              <li>
                <div><strong><?= e($h['estado']) ?></strong><span><?= e(hora($h['fecha_hora'])) ?></span></div>
                <span class="gris"><?= e($h['usuario']) ?></span>
                <?php if ($h['observaciones']): ?><p><?= e($h['observaciones']) ?></p><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ol>
        </section>
      </div>
    </div>
  </main>
</div>
<script src="../../Servicios_Comunes/Vista_General/assets/js/panel.js"></script>
<?php if ($enCurso): ?>
  <script>
    // Si otra persona actualiza el traslado, esta pantalla se entera sola (menos mientras se escribe)
    setTimeout(function () {
      if (!document.activeElement || document.activeElement.tagName !== 'TEXTAREA') { window.location.reload(); }
    }, 30000);
  </script>
<?php endif; ?>
</body>
</html>
