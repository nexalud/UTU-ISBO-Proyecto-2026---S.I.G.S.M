<?php
// Vista publica (celular): lector de un PDF y, al final, la encuesta del area.
// Variables: $documento, $encuesta, $yaRespondio
$volverLista = $documento ? 'paciente.php?qr=' . urlencode($documento['codigo_qr']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $documento ? e($documento['titulo']) : 'Documento no disponible' ?> · Hospital de Clínicas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista/assets/css/paciente.css">
</head>
<body>

<?php if ($documento): $id = (int) $documento['id_documento']; ?>

  <header class="barra-visor">
    <a class="btn-atras" href="<?= e($volverLista) ?>" aria-label="Volver a los documentos de <?= e($documento['categoria']) ?>">
      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5"></path><path d="M11 6l-6 6 6 6"></path></svg>
    </a>
    <div class="barra-visor-texto">
      <span><?= e($documento['categoria']) ?></span>
      <strong><?= e($documento['titulo']) ?></strong>
    </div>
    <a class="btn-descargar" href="archivo.php?id=<?= $id ?>&amp;descargar=1" aria-label="Descargar el PDF">
      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"></path><path d="M7 10l5 5 5-5"></path><path d="M4 21h16"></path></svg>
    </a>
  </header>

  <main class="visor">
    <!-- Aca el script dibuja cada pagina del PDF -->
    <div id="paginas" class="paginas" data-pdf="archivo.php?id=<?= $id ?>">
      <p class="visor-estado" id="visor-estado">Cargando documento…</p>
    </div>

    <noscript>
      <p class="visor-estado">Para leerlo acá hace falta JavaScript. <a href="archivo.php?id=<?= $id ?>">Abrir el PDF</a></p>
    </noscript>

    <?php if ($encuesta): ?>
      <!-- Final del documento: se muestra recien cuando terminan de cargar todas las paginas -->
      <section class="tarjeta encuesta-tarjeta final-documento<?= $yaRespondio ? ' respondida' : '' ?>" id="final-documento" hidden aria-labelledby="titulo-encuesta">
        <span class="final-etiqueta">LLEGASTE AL FINAL</span>
        <?php if ($yaRespondio): ?>
          <h2 id="titulo-encuesta">¡Gracias por tu opinión!</h2>
          <p>Ya respondiste la encuesta de esta área.</p>
        <?php else: ?>
          <h2 id="titulo-encuesta">¿Te sirvió esta información?</h2>
          <p>Contanos cómo te atendieron. Es <strong>anónima</strong> y lleva menos de un minuto.</p>
          <a class="btn-encuesta" href="../../Modulo_Encuestas/Controlador/encuesta.php?qr=<?= e(urlencode($documento['codigo_qr'])) ?>&amp;doc=<?= $id ?>">
            Responder encuesta
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"></path><path d="M13 6l6 6-6 6"></path></svg>
          </a>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <a class="volver-lista" href="<?= e($volverLista) ?>">Ver los otros documentos de <?= e($documento['categoria']) ?></a>
  </main>

  <script type="module" src="../Vista/assets/js/visor.js"></script>

<?php else: ?>

  <main class="pantalla-simple">
    <div class="marca">
      <div class="marca-logo borde"><img src="../../assets/hospitalLogo.png" alt="Hospital de Clínicas"></div>
      <span class="marca-nombre oscuro">Hospital de Clínicas</span>
    </div>
    <div class="mensaje">
      <div class="mensaje-icono">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><path d="M14 3v6h6"></path><path d="M9 13l6 6"></path><path d="M15 13l-6 6"></path></svg>
      </div>
      <h1>Este documento no está disponible</h1>
      <p>Puede que lo hayan retirado. Si necesitás ayuda, consultá al personal del hospital.</p>
    </div>
  </main>

<?php endif; ?>

  <footer class="pie">S.I.G.S.M. · Desarrollado por Nexalud</footer>
<script src="../../Servicios_Comunes/Vista_General/assets/js/idioma_en.js"></script>
<script src="../../Servicios_Comunes/Vista_General/assets/js/comun.js"></script>
</body>
</html>
