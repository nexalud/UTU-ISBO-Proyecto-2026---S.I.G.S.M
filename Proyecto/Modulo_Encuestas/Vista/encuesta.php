<?php
// Vista de la encuesta del paciente. La carga el controlador con:
// $estado, $encuesta, $preguntas, $errores, $valores, $errorGeneral, $token, $codigoQr, $LARGO_MAXIMO

$total   = count($preguntas);
$tituloPagina = $estado === 'form' ? $encuesta['titulo'] : ($estado === 'gracias' ? 'Gracias por responder' : 'Encuesta no disponible');

// Para las preguntas de escala: "1 - Muy mala" se muestra como "Muy mala"
function textoEscala(string $texto): string
{
    return preg_replace('/^\s*\d+\s*-\s*/', '', $texto);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($tituloPagina) ?> · Hospital de Clínicas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Vista/assets/css/encuesta.css">
</head>
<body>

<?php if ($estado === 'form'): ?>

  <header class="cabecera">
    <div class="marca">
      <div class="marca-logo"><img src="../../assets/hospitalLogo.png" alt="Hospital de Clínicas"></div>
      <div class="marca-texto">
        <span class="marca-nombre">Hospital de Clínicas</span>
        <span class="marca-sub">Dr. Manuel Quintela</span>
      </div>
    </div>
    <h1><?= e($encuesta['titulo']) ?></h1>
    <p class="cabecera-desc"><?= e($encuesta['descripcion'] ?: 'Tu opinión nos ayuda a mejorar la atención.') ?></p>
  </header>

  <main class="contenido">
    <div class="aviso">
      <p>
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
        <span>Es <strong>anónima</strong>: no guardamos tu nombre ni tu cédula.</span>
      </p>
      <p>
        <span class="aviso-asterisco" aria-hidden="true">*</span>
        <span>Las preguntas con <strong class="rojo">*</strong> son obligatorias.</span>
      </p>
    </div>

    <form method="post" action="encuesta.php?qr=<?= e(urlencode($codigoQr)) ?>" id="form-encuesta" novalidate>
      <input type="hidden" name="token" value="<?= e($token) ?>">

      <?php foreach ($preguntas as $i => $p):
        $id      = $p['id_pregunta'];
        $campo   = 'p' . $id;
        $tieneError = isset($errores[$id]);
        $valor   = $valores[$id] ?? null;
      ?>
      <fieldset class="pregunta<?= $tieneError ? ' con-error' : '' ?>"
                data-tipo="<?= e($p['tipo_pregunta']) ?>"
                data-obligatoria="<?= $p['obligatoria'] ? '1' : '0' ?>"
                aria-describedby="err-<?= $id ?>">
        <?php $cabecera = '<span class="pregunta-top"><span class="pregunta-num">PREGUNTA ' . ($i + 1) . ' DE ' . $total . '</span>'
              . (!$p['obligatoria'] ? '<span class="chip-opcional">Opcional</span>' : '') . '</span>'; ?>

        <?php if ($p['tipo_pregunta'] === 'abierta'): ?>
          <?= $cabecera ?>
          <label class="pregunta-texto" for="<?= $campo ?>">
            <?= e($p['texto_pregunta']) ?><?php if ($p['obligatoria']): ?>&nbsp;<span class="rojo" aria-hidden="true">*</span><?php endif; ?>
          </label>
          <textarea id="<?= $campo ?>" name="<?= $campo ?>" maxlength="<?= $LARGO_MAXIMO ?>"
                    placeholder="Contanos qué te gustó o qué podemos mejorar"
                    <?= $p['obligatoria'] ? 'aria-required="true"' : '' ?>><?= e(is_string($valor) ? $valor : '') ?></textarea>
          <span class="contador" aria-live="polite"><span data-contador><?= mb_strlen(is_string($valor) ? $valor : '') ?></span> / <?= $LARGO_MAXIMO ?></span>

        <?php elseif ($p['tipo_pregunta'] === 'escala'):
          $elegida = 0;
          foreach ($p['opciones'] as $n => $o) { if ($o['id_opcion'] === $valor) { $elegida = $n + 1; } }
        ?>
          <legend class="pregunta-legend">
            <?= $cabecera ?>
            <span class="pregunta-texto"><?= e($p['texto_pregunta']) ?><?php if ($p['obligatoria']): ?>&nbsp;<span class="rojo" aria-hidden="true">*</span><?php endif; ?></span>
          </legend>
          <div class="estrellas" data-elegida="<?= $elegida ?>">
            <?php foreach ($p['opciones'] as $n => $o):
              $idInput = $campo . '_' . $o['id_opcion'];
            ?>
              <input class="sr-only" type="radio" id="<?= $idInput ?>" name="<?= $campo ?>"
                     value="<?= $o['id_opcion'] ?>" data-texto="<?= e(textoEscala($o['texto'])) ?>"
                     <?= $o['id_opcion'] === $valor ? 'checked' : '' ?>>
              <label class="estrella<?= $n < $elegida ? ' activa' : '' ?>" for="<?= $idInput ?>">
                <span class="sr-only"><?= $n + 1 ?> de <?= count($p['opciones']) ?>: <?= e(textoEscala($o['texto'])) ?></span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.8l2.8 5.7 6.3.9-4.6 4.4 1.1 6.2L12 17.1 6.4 20l1.1-6.2L2.9 9.4l6.3-.9z"></path></svg>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="estrellas-pie">
            <span><?= e(textoEscala($p['opciones'][0]['texto'] ?? '')) ?></span>
            <strong data-texto-elegido><?= $elegida ? e(textoEscala($p['opciones'][$elegida - 1]['texto'])) : 'Tocá una estrella' ?></strong>
            <span><?= e(textoEscala(end($p['opciones'])['texto'] ?? '')) ?></span>
          </div>

        <?php else: // opcion_unica (y opcion_multiple: la base guarda una sola respuesta por pregunta) ?>
          <legend class="pregunta-legend">
            <?= $cabecera ?>
            <span class="pregunta-texto"><?= e($p['texto_pregunta']) ?><?php if ($p['obligatoria']): ?>&nbsp;<span class="rojo" aria-hidden="true">*</span><?php endif; ?></span>
          </legend>
          <div class="opciones">
            <?php foreach ($p['opciones'] as $o): ?>
              <label class="opcion">
                <input class="sr-only" type="radio" name="<?= $campo ?>" value="<?= $o['id_opcion'] ?>"
                       <?= $o['id_opcion'] === $valor ? 'checked' : '' ?>>
                <span class="punto" aria-hidden="true"></span>
                <span><?= e($o['texto']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <p class="error-msg" id="err-<?= $id ?>" role="alert"><?php if ($tieneError): ?>
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
          <?= e($errores[$id]) ?>
        <?php endif; ?></p>
      </fieldset>
      <?php endforeach; ?>

      <button type="submit" class="btn-enviar">
        Enviar respuesta
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"></path><path d="M13 6l6 6-6 6"></path></svg>
      </button>
      <p class="error-general" id="error-general" role="alert"><?= e($errorGeneral) ?></p>
    </form>
  </main>

<?php else: ?>

  <main class="pantalla-simple">
    <div class="marca">
      <div class="marca-logo borde"><img src="../../assets/hospitalLogo.png" alt="Hospital de Clínicas"></div>
      <span class="marca-nombre oscuro">Hospital de Clínicas</span>
    </div>

    <div class="mensaje">
      <?php if ($estado === 'gracias'): ?>
        <div class="mensaje-icono ok">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
        </div>
        <h1>¡Gracias por tu respuesta!</h1>
        <p>Tu opinión quedó registrada de forma anónima y nos ayuda a mejorar la atención del hospital.</p>
        <p class="suave">Ya podés cerrar esta página.</p>
      <?php else: ?>
        <div class="mensaje-icono mal">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><path d="M14 14l7 7"></path><path d="M21 14l-7 7"></path></svg>
        </div>
        <h1>No encontramos esta encuesta</h1>
        <p>El código QR no es válido o la encuesta ya cerró. Si necesitás ayuda, consultá al personal del hospital.</p>
      <?php endif; ?>
    </div>
  </main>

<?php endif; ?>

  <footer class="pie">S.I.G.S.M. · Desarrollado por Nexalud</footer>

<?php if ($estado === 'form'): ?>
  <script src="../Vista/assets/js/encuesta.js"></script>
<?php endif; ?>
</body>
</html>
