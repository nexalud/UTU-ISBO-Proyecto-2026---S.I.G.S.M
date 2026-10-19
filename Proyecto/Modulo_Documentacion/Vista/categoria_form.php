<?php
// Vista: formulario de categoria (nueva o editar).
// Variables: $categoria (null si es nueva), $datos, $errores, $tipoBloqueado, $token

$raiz = '../../';
$seccionActiva = 'categorias';
$titulo = $categoria ? 'Editar categoría' : 'Nueva categoría';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo) ?> · S.I.G.S.M.</title>
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
        <a href="categorias.php">Categorías</a>
        <span aria-hidden="true">/</span>
        <span><?= e($categoria ? $categoria['nombre'] : 'Nueva categoría') ?></span>
      </nav>
      <div class="encabezado"><h1><?= e($titulo) ?></h1></div>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>

    <div class="fila-columnas">
      <form class="tarjeta formulario" method="post" action="categoria_form.php<?= $categoria ? '?id=' . (int) $categoria['id_categoria'] : '' ?>" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <p class="nota" style="font-size:14px">Los campos con <strong class="obligatorio">*</strong> son obligatorios.</p>

        <div class="<?= claseCampo($errores, 'nombre') ?>">
          <label for="nombre">Nombre <span class="obligatorio" aria-hidden="true">*</span></label>
          <input id="nombre" name="nombre" type="text" maxlength="100" required value="<?= e($datos['nombre']) ?>"
                 placeholder="Ej: Nefrología y trasplante" <?= isset($errores['nombre']) ? 'aria-describedby="error-nombre"' : '' ?>>
          <span class="ayuda">Es el título que ve el paciente arriba de la lista.</span>
          <?= errorCampo($errores, 'nombre') ?>
        </div>

        <div class="<?= claseCampo($errores, 'descripcion') ?>">
          <div class="campo-cabecera">
            <label for="descripcion">Descripción</label>
            <span class="chip-opcional">Opcional</span>
          </div>
          <textarea id="descripcion" name="descripcion" maxlength="255"
                    placeholder="Ej: Indicaciones para pacientes del centro de nefrología"><?= e($datos['descripcion']) ?></textarea>
          <?= errorCampo($errores, 'descripcion') ?>
        </div>

        <fieldset class="<?= claseCampo($errores, 'tipo') ?> grupo-opciones">
          <legend class="etiqueta">¿Qué muestra el QR? <span class="obligatorio" aria-hidden="true">*</span></legend>
          <?php if ($tipoBloqueado): ?>
            <input type="hidden" name="tipo" value="<?= e($datos['tipo']) ?>">
          <?php endif; ?>
          <div class="opciones-tarjeta">
            <label class="opcion-tarjeta">
              <input type="radio" name="tipo" value="documental" <?= $datos['tipo'] === 'documental' ? 'checked' : '' ?> <?= $tipoBloqueado ? 'disabled' : '' ?>>
              <span>
                <strong>Documentos</strong>
                <small>El paciente ve la lista de PDF de esta categoría.</small>
              </span>
            </label>
            <label class="opcion-tarjeta">
              <input type="radio" name="tipo" value="encuesta" <?= $datos['tipo'] === 'encuesta' ? 'checked' : '' ?> <?= $tipoBloqueado ? 'disabled' : '' ?>>
              <span>
                <strong>Encuesta</strong>
                <small>El paciente va directo a la encuesta abierta de esta categoría.</small>
              </span>
            </label>
          </div>
          <?php if ($tipoBloqueado): ?>
            <span class="ayuda">No se puede cambiar porque la categoría ya tiene <?= $datos['tipo'] === 'encuesta' ? 'encuestas' : 'documentos' ?>.</span>
          <?php endif; ?>
          <?= errorCampo($errores, 'tipo') ?>
        </fieldset>

        <div class="botones-form">
          <a class="btn btn-secundario" href="categorias.php">Cancelar</a>
          <button type="submit" class="btn btn-primario">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
            <?= $categoria ? 'Guardar cambios' : 'Crear categoría' ?>
          </button>
        </div>
      </form>

      <aside class="tarjeta tarjeta-ayuda">
        <h2>Sobre el QR</h2>
        <?php if ($categoria): ?>
          <p>El código de esta categoría es <strong><?= e($categoria['codigo_qr']) ?></strong> y no cambia aunque edites el nombre. Los QR que ya imprimiste siguen funcionando.</p>
        <?php else: ?>
          <p>Al crearla, el sistema le asigna un código fijo (por ejemplo <strong>QR-CAT-0006</strong>) y ya podés imprimir o descargar su QR.</p>
        <?php endif; ?>
        <p>Una categoría de documentos agrupa PDF informativos. Una de encuesta lleva a la encuesta que esté abierta en ese momento.</p>
      </aside>
    </div>
  </main>
</div>
</body>
</html>
