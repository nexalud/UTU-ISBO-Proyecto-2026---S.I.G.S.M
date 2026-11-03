<?php
// Vista publica (celular): documentos de una categoria y, si hay, la tarjeta de su encuesta.
// Variables: $categoria, $documentos, $encuesta, $yaRespondio, $codigoQr
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $categoria ? e($categoria['nombre']) : 'Documentos no disponibles' ?> · Hospital de Clínicas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista/assets/css/paciente.css">
</head>
<body>

<?php if ($categoria): ?>

  <header class="cabecera">
    <div class="marca">
      <div class="marca-logo"><img src="../../assets/hospitalLogo.png" alt="Hospital de Clínicas"></div>
      <div class="marca-texto">
        <span class="marca-nombre">Hospital de Clínicas</span>
        <span class="marca-sub">Dr. Manuel Quintela</span>
      </div>
    </div>
    <span class="cabecera-etiqueta">INFORMACIÓN PARA PACIENTES</span>
    <h1><?= e($categoria['nombre']) ?></h1>
    <p class="cabecera-desc">Tocá un documento para leerlo o descargalo a tu celular.</p>
  </header>

  <main class="contenido">
    <?php if (!$documentos): ?>
      <div class="tarjeta vacio">
        <p>Todavía no hay documentos disponibles en esta área.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($documentos as $d): $id = (int) $d['id_documento']; ?>
      <article class="tarjeta documento">
        <div class="documento-info">
          <span class="icono-pdf" aria-hidden="true">PDF</span>
          <div>
            <h2><?= e($d['titulo']) ?></h2>
            <?php if (!empty($d['descripcion'])): ?><p><?= e($d['descripcion']) ?></p><?php endif; ?>
            <span class="documento-meta">PDF · <?= e($d['tamano']) ?></span>
          </div>
        </div>
        <div class="documento-botones">
          <a class="btn-ver" href="visor.php?id=<?= $id ?>">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Ver
          </a>
          <a class="btn-descargar" href="archivo.php?id=<?= $id ?>&amp;descargar=1" aria-label="Descargar <?= e($d['titulo']) ?>">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"></path><path d="M7 10l5 5 5-5"></path><path d="M4 21h16"></path></svg>
          </a>
        </div>
      </article>
    <?php endforeach; ?>

    <?php if ($encuesta): ?>
      <!-- Encuesta del area: el paciente la responde sin escanear otro QR -->
      <section class="tarjeta encuesta-tarjeta<?= $yaRespondio ? ' respondida' : '' ?>" aria-labelledby="titulo-encuesta">
        <div class="encuesta-icono" aria-hidden="true">
          <?php if ($yaRespondio): ?>
            <svg class="icono" viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
          <?php else: ?>
            <svg class="icono" viewBox="0 0 24 24"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"></path></svg>
          <?php endif; ?>
        </div>
        <?php if ($yaRespondio): ?>
          <h2 id="titulo-encuesta">¡Gracias por tu opinión!</h2>
          <p>Ya respondiste la encuesta de esta área.</p>
        <?php else: ?>
          <h2 id="titulo-encuesta">¿Nos ayudás a mejorar?</h2>
          <p>Contanos cómo te atendieron. Es <strong>anónima</strong> y lleva menos de un minuto.</p>
          <a class="btn-encuesta" href="../../Modulo_Encuestas/Controlador/encuesta.php?qr=<?= e(urlencode($codigoQr)) ?>">
            Responder encuesta
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"></path><path d="M13 6l6 6-6 6"></path></svg>
          </a>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <div class="tarjeta info">
      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5"></path><path d="M12 7.5h.01"></path></svg>
      <span>Si tenés dudas, consultá al personal del hospital.</span>
    </div>
  </main>

<?php else: ?>

  <main class="pantalla-simple">
    <div class="marca">
      <div class="marca-logo borde"><img src="../../assets/hospitalLogo.png" alt="Hospital de Clínicas"></div>
      <span class="marca-nombre oscuro">Hospital de Clínicas</span>
    </div>
    <div class="mensaje">
      <div class="mensaje-icono">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><path d="M14 14l7 7"></path><path d="M21 14l-7 7"></path></svg>
      </div>
      <h1>No encontramos estos documentos</h1>
      <p>El código QR no es válido. Si necesitás ayuda, consultá al personal del hospital.</p>
    </div>
  </main>

<?php endif; ?>

  <footer class="pie">S.I.G.S.M. · Desarrollado por Nexalud</footer>
<script src="../../Servicios_Comunes/Vista_General/assets/js/idioma_en.js"></script>
<script src="../../Servicios_Comunes/Vista_General/assets/js/comun.js"></script>
</body>
</html>
