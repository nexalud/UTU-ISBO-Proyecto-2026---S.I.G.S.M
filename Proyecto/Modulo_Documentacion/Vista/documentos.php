<?php
// Vista: listado de documentos y QR por categoria.
// Variables: $categorias, $documentos, $idCategoria, $categoriaElegida, $busqueda, $totalDocumentos, $urlQrBase, $aviso, $token

$raiz = '../../';
$seccionActiva = 'documentos';
$tituloLista = $categoriaElegida ? $categoriaElegida['nombre'] : 'Todos los documentos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documentos y QR · S.I.G.S.M.</title>
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
        <h1>Documentos y QR</h1>
        <p>Cada categoría tiene un QR. El paciente lo escanea y ve los documentos activos de esa categoría.</p>
      </div>
      <a class="btn btn-primario" href="nuevo_documento.php<?= $idCategoria ? '?cat=' . $idCategoria : '' ?>">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
        Nuevo documento
      </a>
    </div>

    <?php if ($aviso): ?>
      <div class="aviso <?= $aviso['tipo'] === 'ok' ? 'ok' : 'error' ?>" role="status">
        <?php if ($aviso['tipo'] === 'ok'): ?>
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
        <?php else: ?>
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
        <?php endif; ?>
        <?= e($aviso['texto']) ?>
      </div>
    <?php endif; ?>

    <nav class="chips" aria-label="Categorías">
      <a class="chip<?= $idCategoria === 0 ? ' activo' : '' ?>" href="documentos.php" <?= $idCategoria === 0 ? 'aria-current="true"' : '' ?>>
        Todas <span class="chip-numero"><?= (int) $totalDocumentos ?></span>
      </a>
      <?php foreach ($categorias as $c): $activa = (int) $c['id_categoria'] === $idCategoria; ?>
        <a class="chip<?= $activa ? ' activo' : '' ?>" href="documentos.php?cat=<?= (int) $c['id_categoria'] ?>" <?= $activa ? 'aria-current="true"' : '' ?>>
          <?= e($c['nombre']) ?> <span class="chip-numero"><?= (int) $c['total'] ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="fila-columnas">
      <section class="tarjeta lista-docs">
        <div class="lista-cabecera">
          <h2><?= e($tituloLista) ?></h2>
          <form class="buscador" method="get" action="documentos.php" role="search">
            <?php if ($idCategoria): ?><input type="hidden" name="cat" value="<?= $idCategoria ?>"><?php endif; ?>
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
            <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar documento" aria-label="Buscar documento">
          </form>
        </div>

        <?php if (!$documentos): ?>
          <p class="vacio">
            <?= $busqueda !== '' ? 'No hay documentos que coincidan con "' . e($busqueda) . '".' : 'Todavía no hay documentos en esta categoría.' ?>
          </p>
        <?php else: ?>
          <div class="tabla-scroll">
            <table class="tabla">
              <thead>
                <tr>
                  <th scope="col">Documento</th>
                  <th scope="col">Cargado</th>
                  <th scope="col">Estado</th>
                  <th scope="col" class="col-acciones">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($documentos as $d): $activo = (bool) $d['activo']; ?>
                  <tr>
                    <td>
                      <div class="doc-celda">
                        <span class="icono-pdf" aria-hidden="true">PDF</span>
                        <div>
                          <strong><?= e($d['titulo']) ?></strong>
                          <span><?= e($d['categoria']) ?></span>
                        </div>
                      </div>
                    </td>
                    <td><?= e(date('d/m/Y', strtotime($d['fecha_carga']))) ?></td>
                    <td><span class="estado <?= $activo ? 'activo' : 'inactivo' ?>"><?= $activo ? 'Activo' : 'Inactivo' ?></span></td>
                    <td class="col-acciones">
                      <div class="acciones-fila">
                        <a class="btn-icono" href="archivo.php?id=<?= (int) $d['id_documento'] ?>" target="_blank" rel="noopener" aria-label="Ver <?= e($d['titulo']) ?>" title="Ver PDF">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                        <a class="btn-icono" href="editar_documento.php?id=<?= (int) $d['id_documento'] ?>" aria-label="Editar <?= e($d['titulo']) ?>" title="Editar">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path><path d="M13.5 6.5l4 4"></path></svg>
                        </a>
                        <form method="post" action="documentos.php?<?= e(http_build_query(array_filter(['cat' => $idCategoria ?: null, 'q' => $busqueda ?: null]))) ?>">
                          <input type="hidden" name="token" value="<?= e($token) ?>">
                          <input type="hidden" name="accion" value="estado">
                          <input type="hidden" name="id" value="<?= (int) $d['id_documento'] ?>">
                          <button type="submit" class="btn-icono" aria-label="<?= $activo ? 'Desactivar' : 'Activar' ?> <?= e($d['titulo']) ?>" title="<?= $activo ? 'Desactivar' : 'Activar' ?>">
                            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v9"></path><path d="M6.3 7.3a8 8 0 1 0 11.4 0"></path></svg>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
        <p class="nota">Los documentos inactivos no se muestran al paciente. Para borrar uno, entrá a editarlo.</p>
      </section>

      <section class="tarjeta tarjeta-qr">
        <h2>QR de la categoría</h2>
        <?php if ($categoriaElegida):
          $urlQr = $urlQrBase . urlencode($categoriaElegida['codigo_qr']);
        ?>
          <div class="qr-contenido">
            <div class="qr-imagen" id="qr" data-url="<?= e($urlQr) ?>" role="img"
                 aria-label="Código QR de la categoría <?= e($categoriaElegida['nombre']) ?>"></div>
            <div>
              <div class="qr-nombre"><?= e($categoriaElegida['nombre']) ?></div>
              <div class="qr-codigo"><?= e($categoriaElegida['codigo_qr']) ?> · <?= (int) $categoriaElegida['activos'] ?> documentos activos</div>
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
          </div>
        <?php else: ?>
          <div class="qr-vacio">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true" style="width:40px;height:40px"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><path d="M14 14h3v3h-3z"></path><path d="M20 14v7h-6"></path></svg>
            <strong>Elegí una categoría</strong>
            <span>Tocá una categoría de arriba para ver, imprimir o descargar su QR.</span>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </main>
</div>

<?php if ($categoriaElegida): ?>
  <!-- Hoja que se usa al imprimir el QR -->
  <div class="qr-impresion" aria-hidden="true">
    <h1><?= e($categoriaElegida['nombre']) ?></h1>
    <div id="qr-impresion"></div>
    <p>Escaneá el código con la cámara del celular para ver los documentos.</p>
    <p>Hospital de Clínicas · S.I.G.S.M.</p>
  </div>
  <script src="../../Servicios_Comunes/Vista_General/assets/js/qrcode.js"></script>
  <script src="../../Servicios_Comunes/Vista_General/assets/js/qr_categoria.js"></script>
<?php endif; ?>
</body>
</html>
