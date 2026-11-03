<?php
// Vista: formulario de vehiculo. Variables: $vehiculo (null si es nuevo), $datos, $tipos, $errores, $token
$raiz = '../../';
$seccionActiva = 'flota';
$titulo = $vehiculo ? 'Editar vehículo' : 'Nuevo vehículo';
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
        <a href="flota.php">Flota</a>
        <span aria-hidden="true">/</span>
        <span><?= e($vehiculo ? $vehiculo['matricula'] : 'Nuevo vehículo') ?></span>
      </nav>
      <div class="encabezado"><h1><?= e($titulo) ?></h1></div>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>

    <div class="fila-columnas">
      <form class="tarjeta formulario" method="post" action="vehiculo_form.php<?= $vehiculo ? '?id=' . (int) $vehiculo['id_vehiculo'] : '' ?>" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <fieldset class="<?= claseCampo($errores, 'tipo') ?> grupo-opciones">
          <legend class="etiqueta">Tipo <span class="obligatorio" aria-hidden="true">*</span></legend>
          <div class="opciones-tarjeta">
            <?php foreach ($tipos as $t): ?>
              <label class="opcion-tarjeta">
                <input type="radio" name="tipo" value="<?= (int) $t['id_tipo_vehiculo'] ?>" <?= (int) $t['id_tipo_vehiculo'] === $datos['tipo'] ? 'checked' : '' ?>>
                <span><strong><?= e($t['nombre']) ?></strong></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?= errorCampo($errores, 'tipo') ?>
        </fieldset>

        <div class="campos-fila">
          <div class="<?= claseCampo($errores, 'matricula') ?>">
            <label for="matricula">Matrícula <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="matricula" name="matricula" type="text" maxlength="8" value="<?= e($datos['matricula']) ?>" placeholder="Ej: SCH 1001" autocapitalize="characters">
            <?= errorCampo($errores, 'matricula') ?>
          </div>
          <div class="<?= claseCampo($errores, 'anho') ?>">
            <label for="anho">Año <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="anho" name="anho" type="text" inputmode="numeric" maxlength="4" value="<?= e($datos['anho']) ?>">
            <?= errorCampo($errores, 'anho') ?>
          </div>
        </div>

        <div class="campos-fila">
          <div class="<?= claseCampo($errores, 'marca') ?>">
            <label for="marca">Marca <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="marca" name="marca" type="text" maxlength="50" value="<?= e($datos['marca']) ?>" placeholder="Ej: Mercedes-Benz">
            <?= errorCampo($errores, 'marca') ?>
          </div>
          <div class="<?= claseCampo($errores, 'modelo') ?>">
            <label for="modelo">Modelo <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="modelo" name="modelo" type="text" maxlength="50" value="<?= e($datos['modelo']) ?>" placeholder="Ej: Sprinter">
            <?= errorCampo($errores, 'modelo') ?>
          </div>
        </div>

        <div class="botones-form">
          <a class="btn btn-secundario" href="flota.php">Cancelar</a>
          <button type="submit" class="btn btn-primario">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
            Guardar vehículo
          </button>
        </div>
      </form>

      <aside class="tarjeta tarjeta-ayuda">
        <h2>Qué puede llevar</h2>
        <p>Una <strong>ambulancia</strong> puede llevar pacientes, material biológico, equipamiento e insumos.</p>
        <p>Un <strong>auto</strong> no puede llevar pacientes: solo material biológico, equipamiento chico e insumos.</p>
        <p>Al armar un traslado, el sistema solo ofrece los vehículos que pueden llevar lo que se pidió.</p>
      </aside>
    </div>
  </main>
</div>
</body>
</html>
