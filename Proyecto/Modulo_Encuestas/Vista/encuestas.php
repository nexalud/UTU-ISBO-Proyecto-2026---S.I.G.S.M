<?php
// Vista: listado de encuestas.
// Variables: $encuestas, $enElQr, $categoriasRepetidas, $hayCategorias, $aviso, $token

$raiz = '../../';
$seccionActiva = 'encuestas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas · S.I.G.S.M.</title>
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
        <h1>Encuestas</h1>
        <p>Creá encuestas, elegí en qué QR aparecen y cuándo están abiertas.</p>
      </div>
      <a class="btn btn-primario" href="encuesta_form.php">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
        Nueva encuesta
      </a>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>

    <?php if (!$hayCategorias): ?>
      <div class="aviso error" role="alert">
        No hay categorías de encuesta. <a href="../../Modulo_Documentacion/Controlador/categoria_form.php?tipo=encuesta">Creá una</a> para tener el QR.
      </div>
    <?php endif; ?>

    <?php foreach ($categoriasRepetidas as $nombre): ?>
      <div class="aviso info" role="status">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5"></path><path d="M12 7.5h.01"></path></svg>
        “<?= e($nombre) ?>” tiene más de una encuesta abierta. El QR muestra la más nueva (marcada “En el QR”).
      </div>
    <?php endforeach; ?>

    <section class="tarjeta lista-docs">
      <?php if (!$encuestas): ?>
        <p class="vacio">Todavía no hay encuestas. Creá la primera con el botón de arriba.</p>
      <?php else: ?>
        <div class="tabla-scroll">
          <table class="tabla">
            <thead>
              <tr>
                <th scope="col">Encuesta</th>
                <th scope="col">Segmento</th>
                <th scope="col">Vigencia</th>
                <th scope="col">Estado</th>
                <th scope="col">Respuestas</th>
                <th scope="col" class="col-acciones">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($encuestas as $e):
                $id = (int) $e['id_encuesta'];
                $mostrada = $e['id_categoria'] && ($enElQr[(int) $e['id_categoria']] ?? 0) === $id;
              ?>
                <tr>
                  <td>
                    <div class="doc-celda">
                      <span class="icono-tipo encuesta" aria-hidden="true">
                        <svg class="icono" viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2"></rect><path d="M9 8h6"></path><path d="M9 12h6"></path><path d="M9 16h3"></path></svg>
                      </span>
                      <div>
                        <strong><?= e($e['titulo']) ?></strong>
                        <span><?= $e['categoria'] ? e($e['categoria']) . ' · ' . e($e['codigo_qr']) : 'Sin categoría' ?> · <?= (int) $e['preguntas'] ?> preguntas</span>
                      </div>
                    </div>
                  </td>
                  <td><?= e($e['segmento'] ?: '–') ?></td>
                  <td class="sin-corte">
                    <?= $e['fecha_inicio'] ? e(fechaCorta($e['fecha_inicio'])) : 'Siempre' ?>
                    → <?= $e['fecha_fin'] ? e(fechaCorta($e['fecha_fin'])) : 'sin cierre' ?>
                  </td>
                  <td class="sin-corte">
                    <span class="estado <?= $e['estado'] === 'Abierta' ? 'activo' : ($e['estado'] === 'Programada' ? 'programada' : 'inactivo') ?>"><?= e($e['estado']) ?></span>
                    <?php if ($mostrada): ?><span class="estado encuesta">En el QR</span><?php endif; ?>
                  </td>
                  <td><strong><?= (int) $e['respuestas'] ?></strong></td>
                  <td class="col-acciones">
                    <div class="acciones-fila">
                      <a class="btn-icono" href="resultados.php?enc=<?= $id ?>" aria-label="Resultados de <?= e($e['titulo']) ?>" title="Resultados">
                        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10"></path><path d="M10 20V4"></path><path d="M16 20v-7"></path><path d="M22 20H2"></path></svg>
                      </a>
                      <a class="btn-icono" href="encuesta_form.php?id=<?= $id ?>" aria-label="Editar <?= e($e['titulo']) ?>" title="Editar">
                        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path><path d="M13.5 6.5l4 4"></path></svg>
                      </a>
                      <a class="btn-icono" href="encuesta_form.php?duplicar=<?= $id ?>" aria-label="Duplicar <?= e($e['titulo']) ?>" title="Duplicar">
                        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg>
                      </a>
                      <?php if ($e['estado'] !== 'Cerrada'): ?>
                        <form method="post" action="encuestas.php" data-confirmar="¿Cerrar &quot;<?= e($e['titulo']) ?>&quot;? Los pacientes ya no van a poder responderla.">
                          <input type="hidden" name="token" value="<?= e($token) ?>">
                          <input type="hidden" name="accion" value="cerrar">
                          <input type="hidden" name="id" value="<?= $id ?>">
                          <button type="submit" class="btn-icono" aria-label="Cerrar <?= e($e['titulo']) ?>" title="Cerrar encuesta">
                            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
                          </button>
                        </form>
                      <?php endif; ?>
                      <?php if ((int) $e['respuestas'] === 0): ?>
                        <form method="post" action="encuestas.php" data-confirmar="¿Eliminar &quot;<?= e($e['titulo']) ?>&quot;? No se puede deshacer.">
                          <input type="hidden" name="token" value="<?= e($token) ?>">
                          <input type="hidden" name="accion" value="eliminar">
                          <input type="hidden" name="id" value="<?= $id ?>">
                          <button type="submit" class="btn-icono peligro" aria-label="Eliminar <?= e($e['titulo']) ?>" title="Eliminar">
                            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg>
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
      <p class="nota">Las encuestas con respuestas no se borran: se cierran, y sus resultados quedan en “Resultados”. El QR de cada categoría está en <a href="../../Modulo_Documentacion/Controlador/categorias.php">Categorías</a>.</p>
    </section>
  </main>
</div>
<script src="../../Servicios_Comunes/Vista_General/assets/js/panel.js"></script>
</body>
</html>
