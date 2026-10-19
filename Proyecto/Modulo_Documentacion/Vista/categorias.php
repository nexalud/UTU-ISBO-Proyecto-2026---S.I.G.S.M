<?php
// Vista: listado de categorias y QR de la categoria elegida.
// Variables: $categorias, $categoriaElegida, $cantidadPorTipo, $urlQrBase, $aviso, $token

$raiz = '../../';
$seccionActiva = 'categorias';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categorías · S.I.G.S.M.</title>
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
        <h1>Categorías</h1>
        <p>Cada categoría tiene un QR fijo. Puede mostrar documentos o llevar a una encuesta.</p>
      </div>
      <a class="btn btn-primario" href="categoria_form.php">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
        Nueva categoría
      </a>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>

    <div class="etiquetas">
      <span class="etiqueta-info"><?= count($categorias) ?> categorías</span>
      <span class="etiqueta-info"><?= (int) ($cantidadPorTipo['documental'] ?? 0) ?> de documentos</span>
      <span class="etiqueta-info"><?= (int) ($cantidadPorTipo['encuesta'] ?? 0) ?> de encuesta</span>
    </div>

    <div class="fila-columnas">
      <section class="tarjeta lista-docs">
        <div class="lista-cabecera"><h2>Todas las categorías</h2></div>

        <?php if (!$categorias): ?>
          <p class="vacio">Todavía no hay categorías. Creá la primera con el botón de arriba.</p>
        <?php else: ?>
          <div class="tabla-scroll">
            <table class="tabla">
              <thead>
                <tr>
                  <th scope="col">Categoría</th>
                  <th scope="col">Tipo</th>
                  <th scope="col">Contenido</th>
                  <th scope="col" class="col-acciones">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categorias as $c):
                  $id = (int) $c['id_categoria'];
                  $esEncuesta = $c['tipo_categoria'] === 'encuesta';
                  $vacia = (int) $c['documentos'] === 0 && (int) $c['encuestas'] === 0;
                  $elegida = $categoriaElegida && (int) $categoriaElegida['id_categoria'] === $id;
                ?>
                  <tr<?= $elegida ? ' class="fila-elegida"' : '' ?>>
                    <td>
                      <div class="doc-celda">
                        <span class="icono-tipo <?= $esEncuesta ? 'encuesta' : 'documental' ?>" aria-hidden="true">
                          <?php if ($esEncuesta): ?>
                            <svg class="icono" viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2"></rect><path d="M9 8h6"></path><path d="M9 12h6"></path><path d="M9 16h3"></path></svg>
                          <?php else: ?>
                            <svg class="icono" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                          <?php endif; ?>
                        </span>
                        <div>
                          <strong><?= e($c['nombre']) ?></strong>
                          <span><?= e($c['codigo_qr']) ?><?= $c['descripcion'] ? ' · ' . e($c['descripcion']) : '' ?></span>
                        </div>
                      </div>
                    </td>
                    <td><span class="estado <?= $esEncuesta ? 'encuesta' : 'documental' ?>"><?= e(CategoriaModelo::TIPOS[$c['tipo_categoria']]) ?></span></td>
                    <td>
                      <?php if ($esEncuesta): ?>
                        <?= (int) $c['encuestas'] ?> <?= (int) $c['encuestas'] === 1 ? 'encuesta' : 'encuestas' ?>
                      <?php else: ?>
                        <?= (int) $c['documentos'] ?> <?= (int) $c['documentos'] === 1 ? 'documento' : 'documentos' ?>
                        <?php if ((int) $c['documentos']): ?><small class="gris">(<?= (int) $c['documentos_activos'] ?> activos)</small><?php endif; ?>
                      <?php endif; ?>
                    </td>
                    <td class="col-acciones">
                      <div class="acciones-fila">
                        <a class="btn-icono" href="categorias.php?ver=<?= $id ?>" aria-label="Ver QR de <?= e($c['nombre']) ?>" title="Ver QR">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><path d="M14 14h3v3h-3z"></path><path d="M20 14v7h-6"></path></svg>
                        </a>
                        <a class="btn-icono" href="categoria_form.php?id=<?= $id ?>" aria-label="Editar <?= e($c['nombre']) ?>" title="Editar">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path><path d="M13.5 6.5l4 4"></path></svg>
                        </a>
                        <?php if ($vacia): ?>
                          <form method="post" action="categorias.php" data-confirmar="¿Eliminar la categoría &quot;<?= e($c['nombre']) ?>&quot;? Su QR deja de funcionar.">
                            <input type="hidden" name="token" value="<?= e($token) ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" class="btn-icono peligro" aria-label="Eliminar <?= e($c['nombre']) ?>" title="Eliminar">
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
        <p class="nota">Solo se pueden eliminar las categorías vacías. El código del QR no cambia nunca, así los QR impresos siguen funcionando.</p>
      </section>

      <section class="tarjeta tarjeta-qr">
        <h2>QR de la categoría</h2>
        <?php if ($categoriaElegida):
          $urlQr = $urlQrBase . urlencode($categoriaElegida['codigo_qr']);
          $esEncuesta = $categoriaElegida['tipo_categoria'] === 'encuesta';
        ?>
          <div class="qr-contenido">
            <div class="qr-imagen" id="qr" data-url="<?= e($urlQr) ?>" role="img"
                 aria-label="Código QR de la categoría <?= e($categoriaElegida['nombre']) ?>"></div>
            <div>
              <div class="qr-nombre"><?= e($categoriaElegida['nombre']) ?></div>
              <div class="qr-codigo">
                <?= e($categoriaElegida['codigo_qr']) ?> ·
                <?= $esEncuesta ? 'lleva a la encuesta abierta' : (int) $categoriaElegida['documentos_activos'] . ' documentos activos' ?>
              </div>
            </div>
            <div class="qr-botones">
              <button type="button" class="btn btn-primario" id="btn-imprimir">
                <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6"></path><rect x="3" y="9" width="18" height="8" rx="2"></rect><path d="M7 14h10v7H7z"></path></svg>
                Imprimir
              </button>
              <button type="button" class="btn btn-secundario" id="btn-descargar"
                      data-nombre="<?= e($categoriaElegida['codigo_qr']) ?>" data-titulo="<?= e($categoriaElegida['nombre']) ?>">
                <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"></path><path d="M7 10l5 5 5-5"></path><path d="M4 21h16"></path></svg>
                Descargar
              </button>
            </div>
            <a href="<?= e($urlQr) ?>" target="_blank" rel="noopener">Ver lo que ve el paciente</a>
            <?php if ($esEncuesta): ?>
              <a href="../../Modulo_Encuestas/Controlador/encuestas.php">Ir a las encuestas</a>
            <?php else: ?>
              <a href="documentos.php?cat=<?= (int) $categoriaElegida['id_categoria'] ?>">Ir a los documentos</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="qr-vacio">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true" style="width:40px;height:40px"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><path d="M14 14h3v3h-3z"></path><path d="M20 14v7h-6"></path></svg>
            <strong>Elegí una categoría</strong>
            <span>Tocá el ícono de QR de una categoría para verlo, imprimirlo o descargarlo.</span>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </main>
</div>

<?php if ($categoriaElegida): ?>
  <div class="qr-impresion" aria-hidden="true">
    <h1><?= e($categoriaElegida['nombre']) ?></h1>
    <div id="qr-impresion"></div>
    <p><?= $categoriaElegida['tipo_categoria'] === 'encuesta'
          ? 'Escaneá el código con la cámara del celular para responder la encuesta. Es anónima.'
          : 'Escaneá el código con la cámara del celular para ver los documentos.' ?></p>
    <p>Hospital de Clínicas · S.I.G.S.M.</p>
  </div>
  <script src="../../Servicios_Comunes/Vista_General/assets/js/qrcode.js"></script>
  <script src="../../Servicios_Comunes/Vista_General/assets/js/qr_categoria.js"></script>
<?php endif; ?>
<script src="../../Servicios_Comunes/Vista_General/assets/js/panel.js"></script>
</body>
</html>
