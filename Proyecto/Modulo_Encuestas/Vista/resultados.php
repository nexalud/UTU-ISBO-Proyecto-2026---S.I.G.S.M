<?php
// Vista: resultados de una encuesta.
// Variables: $encuestas, $encuesta, $idEncuesta, $periodos, $periodo, $estado, $totalRespuestas, $totalComentarios,
//            $preguntas, $comentarios, $promedio, $indicadorOpcion, $textoEscala, $urlQr

$raiz = '../../';
$seccionActiva = 'resultados';

function formatoNumero(float $n): string
{
    return number_format($n, 1, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados de encuestas · S.I.G.S.M.</title>
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
        <h1>Resultados de encuestas</h1>
        <p>Las respuestas son anónimas: no se guarda ningún dato del paciente.</p>
      </div>
      <?php if ($encuestas): ?>
        <form class="filtros" method="get" action="resultados.php" id="form-filtros">
          <div class="filtro">
            <label for="enc">Encuesta</label>
            <select id="enc" name="enc">
              <?php foreach ($encuestas as $e): ?>
                <option value="<?= (int) $e['id_encuesta'] ?>" <?= (int) $e['id_encuesta'] === $idEncuesta ? 'selected' : '' ?>><?= e($e['titulo']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filtro">
            <label for="periodo">Período</label>
            <select id="periodo" name="periodo">
              <?php foreach ($periodos as $clave => $texto): ?>
                <option value="<?= e($clave) ?>" <?= $clave === $periodo ? 'selected' : '' ?>><?= e($texto) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <noscript><button type="submit" class="btn btn-secundario btn-chico">Ver</button></noscript>
        </form>
      <?php endif; ?>
    </div>

    <?php if (!$encuesta): ?>
      <div class="tarjeta vacio">Todavía no hay encuestas cargadas.</div>
    <?php else: ?>

      <div class="etiquetas">
        <span class="etiqueta-info <?= $estado === 'Abierta' ? 'verde' : 'gris' ?>"><?= e($estado) ?></span>
        <?php if ($encuesta['segmento']): ?><span class="etiqueta-info">Segmento: <?= e($encuesta['segmento']) ?></span><?php endif; ?>
        <?php if ($encuesta['codigo_qr']): ?><span class="etiqueta-info"><?= e($encuesta['codigo_qr']) ?></span><?php endif; ?>
      </div>

      <div class="indicadores">
        <div class="indicador">
          <span class="indicador-titulo">Respuestas</span>
          <span class="indicador-valor"><?= $totalRespuestas ?></span>
          <span class="indicador-detalle"><?= $periodo === 'todo' ? 'en total' : e(mb_strtolower($periodos[$periodo])) ?></span>
        </div>
        <div class="indicador">
          <span class="indicador-titulo">Calificación promedio</span>
          <?php if ($promedio): ?>
            <span class="indicador-valor"><?= formatoNumero($promedio['valor']) ?> <small>/ <?= $promedio['maximo'] ?></small></span>
          <?php else: ?>
            <span class="indicador-valor">–</span>
          <?php endif; ?>
          <span class="indicador-detalle">de la primera pregunta de escala</span>
        </div>
        <?php if ($indicadorOpcion): ?>
          <div class="indicador">
            <span class="indicador-titulo"><?= e($indicadorOpcion['texto']) ?></span>
            <span class="indicador-valor"><?= (int) $indicadorOpcion['porcentaje'] ?> %</span>
            <span class="indicador-detalle"><?= e($indicadorOpcion['pregunta']) ?></span>
          </div>
        <?php endif; ?>
        <div class="indicador">
          <span class="indicador-titulo">Comentarios</span>
          <span class="indicador-valor"><?= $totalComentarios ?></span>
          <span class="indicador-detalle">dejaron un comentario</span>
        </div>
      </div>

      <div class="fila-columnas">
        <?php $num = 0; foreach ($preguntas as $p): $num++; if ($p['tipo_pregunta'] === 'abierta') { continue; } ?>
          <section class="tarjeta tarjeta-pregunta">
            <span class="pregunta-tipo">PREGUNTA <?= $num ?> · <?= $p['tipo_pregunta'] === 'escala' ? 'ESCALA' : 'OPCIÓN ÚNICA' ?></span>
            <h2><?= e($p['texto_pregunta']) ?></h2>
            <?php if (!$p['respondieron']): ?>
              <p class="nota" style="font-size:14px">Nadie respondió esta pregunta en el período.</p>
            <?php else: ?>
              <?php foreach ($p['opciones'] as $o): ?>
                <div class="barra-fila">
                  <div class="barra-texto">
                    <span><?= e($o['texto']) ?></span>
                    <span><?= (int) $o['cantidad'] ?> <small>(<?= (int) $o['porcentaje'] ?> %)</small></span>
                  </div>
                  <div class="barra" role="img" aria-label="<?= (int) $o['porcentaje'] ?> por ciento">
                    <span style="width: <?= (int) $o['porcentaje'] ?>%"></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>

        <section class="tarjeta tarjeta-qr">
          <h2>QR de la encuesta</h2>
          <?php if ($urlQr): ?>
            <div class="qr-contenido">
              <div class="qr-imagen" id="qr" data-url="<?= e($urlQr) ?>" role="img"
                   aria-label="Código QR de la encuesta <?= e($encuesta['titulo']) ?>"></div>
              <div>
                <div class="qr-nombre"><?= e($encuesta['titulo']) ?></div>
                <div class="qr-codigo"><?= e($encuesta['codigo_qr']) ?></div>
              </div>
              <div class="qr-botones">
                <button type="button" class="btn btn-primario" id="btn-imprimir">
                  <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6"></path><rect x="3" y="9" width="18" height="8" rx="2"></rect><path d="M7 14h10v7H7z"></path></svg>
                  Imprimir
                </button>
                <button type="button" class="btn btn-secundario" id="btn-descargar"
                        data-nombre="<?= e($encuesta['codigo_qr']) ?>" data-titulo="<?= e($encuesta['titulo']) ?>">
                  <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"></path><path d="M7 10l5 5 5-5"></path><path d="M4 21h16"></path></svg>
                  Descargar
                </button>
              </div>
              <a href="<?= e($urlQr) ?>" target="_blank" rel="noopener">Abrir la encuesta</a>
            </div>
          <?php else: ?>
            <div class="qr-vacio"><strong>Sin QR</strong><span>Esta encuesta no está asociada a una categoría.</span></div>
          <?php endif; ?>
        </section>
      </div>

      <?php foreach ($preguntas as $p): if ($p['tipo_pregunta'] !== 'abierta') { continue; } ?>
        <section class="tarjeta" style="display:flex;flex-direction:column;gap:14px">
          <div class="lista-cabecera">
            <div>
              <span class="pregunta-tipo">PREGUNTA ABIERTA</span>
              <h2 style="font-size:18px;margin-top:4px"><?= e($p['texto_pregunta']) ?></h2>
            </div>
            <span class="nota" style="font-size:14px">Más recientes primero</span>
          </div>
          <?php $deEsta = array_filter($comentarios, fn($c) => (int) $c['id_pregunta'] === $p['id_pregunta']); ?>
          <?php if (!$deEsta): ?>
            <p class="nota" style="font-size:14px">No hay comentarios en el período.</p>
          <?php else: ?>
            <div class="comentarios">
              <?php foreach ($deEsta as $c): ?>
                <article class="comentario">
                  <div class="comentario-cabecera">
                    <strong><?= e($textoEscala[(int) $c['id_opcion_escala']] ?? 'Sin calificación') ?></strong>
                    <span><?= e(date('d/m/Y H:i', strtotime($c['fecha_envio']))) ?></span>
                  </div>
                  <p><?= e($c['texto_libre']) ?></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>

    <?php endif; ?>
  </main>
</div>

<?php if ($urlQr): ?>
  <div class="qr-impresion" aria-hidden="true">
    <h1><?= e($encuesta['titulo']) ?></h1>
    <div id="qr-impresion"></div>
    <p>Escaneá el código con la cámara del celular para responder la encuesta.</p>
    <p>Es anónima y lleva menos de un minuto.</p>
  </div>
  <script src="../../Servicios_Comunes/Vista_General/assets/js/qrcode.js"></script>
  <script src="../../Servicios_Comunes/Vista_General/assets/js/qr_categoria.js"></script>
<?php endif; ?>
<script>
  // Al cambiar la encuesta o el periodo, se actualiza la pagina sola
  document.querySelectorAll('#form-filtros select').forEach(function (s) {
    s.addEventListener('change', function () { s.form.submit(); });
  });
</script>
</body>
</html>
