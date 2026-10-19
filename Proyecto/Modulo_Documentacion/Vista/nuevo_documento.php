<?php
// Vista: formulario de carga de documento.
// Variables: $categorias, $datos, $errores, $token

$raiz = '../../';
$seccionActiva = 'documentos';

function errorCampo(array $errores, string $campo): string
{
    if (!isset($errores[$campo])) {
        return '';
    }
    return '<p class="mensaje-error" id="error-' . $campo . '" role="alert">'
        . '<svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>'
        . e($errores[$campo]) . '</p>';
}

function claseCampo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? 'campo con-error' : 'campo';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nuevo documento · S.I.G.S.M.</title>
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
        <a href="documentos.php">Documentos y QR</a>
        <span aria-hidden="true">/</span>
        <span>Nuevo documento</span>
      </nav>
      <div class="encabezado"><h1>Nuevo documento</h1></div>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>

    <div class="fila-columnas">
      <form class="tarjeta formulario" method="post" action="nuevo_documento.php" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= DocumentoModelo::TAMANO_MAXIMO ?>">

        <p class="nota" style="font-size:14px">Los campos con <strong class="obligatorio">*</strong> son obligatorios.</p>

        <div class="<?= claseCampo($errores, 'titulo') ?>">
          <label for="titulo">Título <span class="obligatorio" aria-hidden="true">*</span></label>
          <input id="titulo" name="titulo" type="text" maxlength="150" required value="<?= e($datos['titulo']) ?>"
                 placeholder="Ej: Indicaciones para pacientes en tratamiento con warfarina"
                 aria-describedby="ayuda-titulo<?= isset($errores['titulo']) ? ' error-titulo' : '' ?>">
          <span class="ayuda" id="ayuda-titulo">Es lo que ve el paciente al escanear el QR.</span>
          <?= errorCampo($errores, 'titulo') ?>
        </div>

        <div class="<?= claseCampo($errores, 'categoria') ?>">
          <label for="categoria">Categoría <span class="obligatorio" aria-hidden="true">*</span></label>
          <select id="categoria" name="categoria" required>
            <option value="">Elegí una categoría</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int) $c['id_categoria'] ?>" <?= (int) $c['id_categoria'] === $datos['categoria'] ? 'selected' : '' ?>>
                <?= e($c['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?= errorCampo($errores, 'categoria') ?>
        </div>

        <div class="<?= claseCampo($errores, 'descripcion') ?>">
          <div class="campo-cabecera">
            <label for="descripcion">Descripción</label>
            <span class="chip-opcional">Opcional</span>
          </div>
          <textarea id="descripcion" name="descripcion" maxlength="255"
                    placeholder="Una línea que ayude al paciente a saber de qué trata"><?= e($datos['descripcion']) ?></textarea>
          <?= errorCampo($errores, 'descripcion') ?>
        </div>

        <div class="<?= claseCampo($errores, 'archivo') ?>">
          <span class="etiqueta" id="etiqueta-archivo">Archivo PDF <span class="obligatorio" aria-hidden="true">*</span></span>
          <label class="zona-archivo" id="zona-archivo" for="archivo">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15V4"></path><path d="M7 9l5-5 5 5"></path><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"></path></svg>
            <strong>Arrastrá el PDF acá o <u>elegí un archivo</u></strong>
            <span>Solo PDF, hasta 10 MB</span>
          </label>
          <input class="sr-only" id="archivo" name="archivo" type="file" accept="application/pdf,.pdf" required aria-labelledby="etiqueta-archivo">
          <div class="archivo-elegido" id="archivo-elegido">
            <span class="icono-pdf" aria-hidden="true">PDF</span>
            <div>
              <strong data-nombre></strong>
              <span data-tamano></span>
            </div>
            <button type="button" class="btn-icono" id="quitar-archivo" aria-label="Quitar archivo">
              <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12"></path><path d="M18 6L6 18"></path></svg>
            </button>
          </div>
          <?= errorCampo($errores, 'archivo') ?>
        </div>

        <label class="interruptor">
          <span>
            <strong>Visible para los pacientes</strong>
            <small>Si lo desactivás, el documento se guarda pero no aparece en el QR.</small>
          </span>
          <input type="checkbox" name="activo" <?= $datos['activo'] ? 'checked' : '' ?>>
        </label>

        <div class="botones-form">
          <a class="btn btn-secundario" href="documentos.php">Cancelar</a>
          <button type="submit" class="btn btn-primario">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
            Guardar documento
          </button>
        </div>
      </form>

      <aside class="tarjeta tarjeta-ayuda">
        <h2>Antes de subirlo</h2>
        <p>El documento aparece en el QR de su categoría apenas lo guardás. No hace falta imprimir un QR nuevo.</p>
        <p>Revisá que el PDF sea información general para pacientes: <strong>nunca subas documentos con datos de un paciente</strong>, porque cualquiera que escanee el QR los puede ver.</p>
      </aside>
    </div>
  </main>
</div>
<script src="../Vista/assets/js/documentos.js"></script>
</body>
</html>
