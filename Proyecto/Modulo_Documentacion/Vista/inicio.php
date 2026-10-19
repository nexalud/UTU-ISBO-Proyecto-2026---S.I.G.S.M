<?php
// Vista: inicio del panel.
// Variables: $resumen, $calificacion, $documentos, $encuestas, $diferencia, $saludo, $usuarioActual, $aviso

$raiz = '../../';
$seccionActiva = 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio · S.I.G.S.M.</title>
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
        <h1><?= e($saludo) ?>, <?= e($usuarioActual['nombre']) ?></h1>
        <p>Así está hoy la documentación y las encuestas para pacientes.</p>
      </div>
      <div class="botones-encabezado">
        <a class="btn btn-secundario" href="../../Modulo_Encuestas/Controlador/encuesta_form.php">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
          Nueva encuesta
        </a>
        <a class="btn btn-primario" href="nuevo_documento.php">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
          Nuevo documento
        </a>
      </div>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>

    <div class="indicadores">
      <a class="indicador indicador-enlace" href="documentos.php">
        <span class="indicador-titulo">Documentos activos</span>
        <span class="indicador-valor"><?= $resumen['documentos_activos'] ?> <small>/ <?= $resumen['documentos'] ?></small></span>
        <span class="indicador-detalle">visibles en los QR</span>
      </a>
      <a class="indicador indicador-enlace" href="categorias.php">
        <span class="indicador-titulo">Categorías</span>
        <span class="indicador-valor"><?= $resumen['categorias_docs'] + $resumen['categorias_encuesta'] ?></span>
        <span class="indicador-detalle"><?= $resumen['categorias_docs'] ?> de documentos · <?= $resumen['categorias_encuesta'] ?> de encuesta</span>
      </a>
      <a class="indicador indicador-enlace" href="../../Modulo_Encuestas/Controlador/encuestas.php">
        <span class="indicador-titulo">Encuestas abiertas</span>
        <span class="indicador-valor"><?= $resumen['encuestas_abiertas'] ?> <small>/ <?= $resumen['encuestas'] ?></small></span>
        <span class="indicador-detalle">se pueden responder hoy</span>
      </a>
      <a class="indicador indicador-enlace" href="../../Modulo_Encuestas/Controlador/resultados.php?periodo=mes">
        <span class="indicador-titulo">Respuestas este mes</span>
        <span class="indicador-valor"><?= $resumen['respuestas_mes'] ?></span>
        <span class="indicador-detalle">
          <?php if ($diferencia > 0): ?><span class="tendencia sube">▲ <?= $diferencia ?></span> más que el mes pasado
          <?php elseif ($diferencia < 0): ?><span class="tendencia baja">▼ <?= -$diferencia ?></span> menos que el mes pasado
          <?php else: ?>igual que el mes pasado<?php endif; ?>
        </span>
      </a>
      <div class="indicador">
        <span class="indicador-titulo">Calificación general</span>
        <?php if ($calificacion !== null): ?>
          <span class="indicador-valor"><?= number_format($calificacion, 1, ',', '.') ?> <small>/ 5</small></span>
          <span class="estrellas" role="img" aria-label="<?= number_format($calificacion, 1, ',', '.') ?> de 5 estrellas">
            <span style="width: <?= round($calificacion * 20) ?>%"></span>
          </span>
        <?php else: ?>
          <span class="indicador-valor">–</span>
          <span class="indicador-detalle">todavía no hay respuestas</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="fila-columnas">
      <section class="tarjeta lista-docs">
        <div class="lista-cabecera">
          <h2>Últimos documentos</h2>
          <a href="documentos.php" class="enlace-chico">Ver todos</a>
        </div>
        <?php if (!$documentos): ?>
          <p class="vacio">Todavía no se cargó ningún documento.</p>
        <?php else: ?>
          <ul class="lista-simple">
            <?php foreach ($documentos as $d): ?>
              <li>
                <span class="icono-pdf" aria-hidden="true">PDF</span>
                <div>
                  <a href="editar_documento.php?id=<?= (int) $d['id_documento'] ?>"><strong><?= e($d['titulo']) ?></strong></a>
                  <span><?= e($d['categoria']) ?> · <?= e(fechaCorta($d['fecha_carga'])) ?></span>
                </div>
                <span class="estado <?= $d['activo'] ? 'activo' : 'inactivo' ?>"><?= $d['activo'] ? 'Activo' : 'Inactivo' ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="tarjeta tarjeta-qr">
        <div class="lista-cabecera">
          <h2>Encuestas abiertas</h2>
          <a href="../../Modulo_Encuestas/Controlador/encuestas.php" class="enlace-chico">Gestionar</a>
        </div>
        <?php if (!$encuestas): ?>
          <p class="vacio">No hay encuestas abiertas hoy.</p>
        <?php else: ?>
          <ul class="lista-simple">
            <?php foreach ($encuestas as $en): ?>
              <li>
                <div>
                  <a href="../../Modulo_Encuestas/Controlador/resultados.php?enc=<?= (int) $en['id_encuesta'] ?>"><strong><?= e($en['titulo']) ?></strong></a>
                  <span><?= e($en['segmento'] ?: 'Sin segmento') ?> · <?= (int) $en['semana'] ?> en los últimos 7 días</span>
                </div>
                <span class="numero-grande"><?= (int) $en['respuestas'] ?><small>resp.</small></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>
  </main>
</div>
</body>
</html>
