<?php
// Vista: alta o edicion de traslado.
// Variables: $tipos, $ubicaciones, $canales, $proveedores, $vehiculos, $choferes, $enfermeros, $rutas, $datos, $errores,
//            $paciente, $cargaManual, $traslado (null si es nuevo), $idTraslado, $token
$raiz = '../../';
$seccionActiva = 'traslados';

$tipoElegido = null;
foreach ($tipos as $t) {
    if ((int) $t['id_tipo_elemento'] === $datos['tipo_elemento']) {
        $tipoElegido = $t;
    }
}
$pideCedula = $tipoElegido && $tipoElegido['requiere_paciente'];
$titulo = $traslado ? 'Editar traslado T-' . str_pad((string) $idTraslado, 4, '0', STR_PAD_LEFT) : 'Nuevo traslado';

function opciones(array $filas, string $id, string $texto, int $elegido, string $vacio): string
{
    $html = '<option value="">' . e($vacio) . '</option>';
    foreach ($filas as $f) {
        $html .= '<option value="' . (int) $f[$id] . '"' . ((int) $f[$id] === $elegido ? ' selected' : '') . '>' . e($f[$texto]) . '</option>';
    }
    return $html;
}
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
        <a href="traslados.php">Traslados</a>
        <span aria-hidden="true">/</span>
        <span><?= e($titulo) ?></span>
      </nav>
      <div class="encabezado"><h1><?= e($titulo) ?></h1></div>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php elseif ($errores): ?>
      <div class="aviso error" role="alert">Revisá los campos marcados en rojo.</div>
    <?php endif; ?>

    <form class="formulario-encuesta" method="post" action="traslado_form.php<?= $traslado ? '?id=' . $idTraslado : '' ?>" id="form-traslado" novalidate
          data-rutas="<?= e(json_encode($rutas)) ?>">
      <input type="hidden" name="token" value="<?= e($token) ?>">

      <div class="fila-columnas">
        <div class="columna-lateral columna-ancha">
          <!-- 1. Qué se traslada -->
          <section class="tarjeta formulario">
            <h2>1 · Qué se traslada</h2>

            <fieldset class="<?= claseCampo($errores, 'tipo_elemento') ?> grupo-opciones">
              <legend class="etiqueta">Tipo <span class="obligatorio" aria-hidden="true">*</span></legend>
              <div class="opciones-tarjeta">
                <?php foreach ($tipos as $t): ?>
                  <label class="opcion-tarjeta">
                    <input type="radio" name="tipo_elemento" value="<?= (int) $t['id_tipo_elemento'] ?>" data-requiere="<?= (int) $t['requiere_paciente'] ?>"
                      <?= (int) $t['id_tipo_elemento'] === $datos['tipo_elemento'] ? 'checked' : '' ?>>
                    <span><strong><?= e($t['nombre']) ?></strong></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <?= errorCampo($errores, 'tipo_elemento') ?>
            </fieldset>

            <div class="<?= claseCampo($errores, 'ci') ?>" id="zona-ci" <?= $pideCedula ? '' : 'hidden' ?>>
              <label for="ci">Cédula del paciente <span class="obligatorio" aria-hidden="true">*</span></label>
              <div class="campo-boton">
                <input id="ci" name="ci" type="text" inputmode="numeric" maxlength="12" value="<?= e($datos['ci']) ?>" placeholder="Ej: 3.456.789-4" autocomplete="off">
                <button type="button" class="btn btn-secundario" id="buscar-paciente">Buscar</button>
              </div>
              <span class="ayuda" id="resultado-paciente" aria-live="polite"><?= $paciente ? 'Paciente: <strong>' . e($paciente['nombre'] . ' ' . $paciente['apellido']) . '</strong>' : 'Los datos se traen del sistema del hospital.' ?></span>
              <?= errorCampo($errores, 'ci') ?>

              <!-- Ingreso manual: aparece si la cédula no está en el sistema del hospital -->
              <div class="campos-fila" id="zona-manual" <?= $cargaManual ? '' : 'hidden' ?>>
                <div class="<?= claseCampo($errores, 'nombre') ?>">
                  <label for="nombre">Nombre <span class="obligatorio" aria-hidden="true">*</span></label>
                  <input id="nombre" name="nombre" type="text" maxlength="100" value="<?= e($datos['nombre']) ?>" autocomplete="off">
                  <?= errorCampo($errores, 'nombre') ?>
                </div>
                <div class="<?= claseCampo($errores, 'apellido') ?>">
                  <label for="apellido">Apellido <span class="obligatorio" aria-hidden="true">*</span></label>
                  <input id="apellido" name="apellido" type="text" maxlength="100" value="<?= e($datos['apellido']) ?>" autocomplete="off">
                  <?= errorCampo($errores, 'apellido') ?>
                </div>
              </div>
            </div>

            <div class="<?= claseCampo($errores, 'motivo') ?>">
              <label for="motivo">Motivo o detalle <span class="obligatorio" aria-hidden="true">*</span></label>
              <textarea id="motivo" name="motivo" maxlength="255" placeholder="Ej: Consulta con cirugía vascular / Muestras de tejido para estudio"><?= e($datos['motivo']) ?></textarea>
              <?= errorCampo($errores, 'motivo') ?>
            </div>
          </section>

          <!-- 2. Recorrido y horario -->
          <section class="tarjeta formulario">
            <h2>2 · Recorrido y horario</h2>
            <div class="campos-fila">
              <div class="<?= claseCampo($errores, 'origen') ?>">
                <label for="origen">Sale de <span class="obligatorio" aria-hidden="true">*</span></label>
                <select id="origen" name="origen"><?= opciones($ubicaciones, 'id_ubicacion', 'nombre', $datos['origen'], 'Elegí el origen') ?></select>
                <?= errorCampo($errores, 'origen') ?>
              </div>
              <div class="<?= claseCampo($errores, 'destino') ?>">
                <label for="destino">Va a <span class="obligatorio" aria-hidden="true">*</span></label>
                <select id="destino" name="destino"><?= opciones($ubicaciones, 'id_ubicacion', 'nombre', $datos['destino'], 'Elegí el destino') ?></select>
                <?= errorCampo($errores, 'destino') ?>
              </div>
            </div>
            <p class="info-ruta" id="info-ruta" aria-live="polite"></p>

            <div class="campos-fila">
              <div class="<?= claseCampo($errores, 'salida') ?>">
                <label for="salida">Hora de salida <span class="obligatorio" aria-hidden="true">*</span></label>
                <input id="salida" name="salida" type="datetime-local" value="<?= e($datos['salida']) ?>">
                <?= errorCampo($errores, 'salida') ?>
              </div>
              <div class="<?= claseCampo($errores, 'llegada') ?>">
                <div class="campo-cabecera">
                  <label for="llegada">Llegada estimada</label>
                  <span class="chip-opcional">Opcional</span>
                </div>
                <input id="llegada" name="llegada" type="datetime-local" value="<?= e($datos['llegada']) ?>">
                <span class="ayuda">Vacía = se calcula con el tiempo de la ruta.</span>
                <?= errorCampo($errores, 'llegada') ?>
              </div>
            </div>

            <div class="<?= claseCampo($errores, 'canal') ?>">
              <label for="canal">La solicitud llegó por <span class="obligatorio" aria-hidden="true">*</span></label>
              <select id="canal" name="canal"><?= opciones($canales, 'id_canal', 'nombre', $datos['canal'], 'Elegí una opción') ?></select>
              <?= errorCampo($errores, 'canal') ?>
            </div>
          </section>
        </div>

        <!-- 3. Quién lo hace -->
        <section class="tarjeta formulario columna-angosta">
          <h2>3 · Quién lo hace</h2>

          <div class="opciones-tarjeta una-columna">
            <label class="opcion-tarjeta">
              <input type="radio" name="modo" value="propio" <?= $datos['modo'] === 'propio' ? 'checked' : '' ?>>
              <span><strong>Con recursos del hospital</strong><small>Vehículo, chofer y enfermero propios.</small></span>
            </label>
            <label class="opcion-tarjeta">
              <input type="radio" name="modo" value="externo" <?= $datos['modo'] === 'externo' ? 'checked' : '' ?>>
              <span><strong>Derivar a una empresa externa</strong><small>Cuando no hay recursos libres.</small></span>
            </label>
          </div>

          <div class="zona-tipo" id="zona-propio" <?= $datos['modo'] === 'propio' ? '' : 'hidden' ?>>
            <div class="<?= claseCampo($errores, 'vehiculo') ?>">
              <label for="vehiculo">Vehículo <span class="obligatorio" aria-hidden="true">*</span></label>
              <select id="vehiculo" name="vehiculo">
                <option value="">Elegí un vehículo libre</option>
                <?php foreach ($vehiculos as $v): ?>
                  <option value="<?= (int) $v['id_vehiculo'] ?>" data-admite="<?= e((string) $v['admite']) ?>" <?= (int) $v['id_vehiculo'] === $datos['vehiculo'] ? 'selected' : '' ?>>
                    <?= e($v['tipo'] . ' · ' . $v['matricula'] . ' · ' . $v['marca'] . ' ' . $v['modelo']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="ayuda" id="ayuda-vehiculo">Solo aparecen los que están en servicio y libres.</span>
              <?= errorCampo($errores, 'vehiculo') ?>
            </div>
            <div class="<?= claseCampo($errores, 'chofer') ?>">
              <label for="chofer">Chofer <span class="obligatorio" aria-hidden="true">*</span></label>
              <select id="chofer" name="chofer"><?= opciones($choferes, 'id_usuario', 'nombre', $datos['chofer'], $choferes ? 'Elegí un chofer libre' : 'No hay choferes libres') ?></select>
              <?= errorCampo($errores, 'chofer') ?>
            </div>
            <div class="<?= claseCampo($errores, 'enfermero') ?>">
              <label for="enfermero">Enfermero <span class="obligatorio" aria-hidden="true">*</span></label>
              <select id="enfermero" name="enfermero"><?= opciones($enfermeros, 'id_usuario', 'nombre', $datos['enfermero'], $enfermeros ? 'Elegí un enfermero libre' : 'No hay enfermeros libres') ?></select>
              <span class="ayuda">Es el responsable del paciente durante el viaje.</span>
              <?= errorCampo($errores, 'enfermero') ?>
            </div>
          </div>

          <div class="zona-tipo" id="zona-externo" <?= $datos['modo'] === 'externo' ? '' : 'hidden' ?>>
            <div class="<?= claseCampo($errores, 'proveedor') ?>">
              <label for="proveedor">Empresa <span class="obligatorio" aria-hidden="true">*</span></label>
              <select id="proveedor" name="proveedor"><?= opciones($proveedores, 'id_proveedor', 'nombre', $datos['proveedor'], 'Elegí la empresa') ?></select>
              <?= errorCampo($errores, 'proveedor') ?>
            </div>
          </div>

          <div class="botones-form">
            <a class="btn btn-secundario" href="<?= $traslado ? 'traslado.php?id=' . $idTraslado : 'traslados.php' ?>">Cancelar</a>
            <button type="submit" class="btn btn-primario">
              <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
              <?= $traslado ? 'Guardar cambios' : 'Programar traslado' ?>
            </button>
          </div>
        </section>
      </div>
    </form>
  </main>
</div>
<script src="../Vista/assets/js/traslado_form.js"></script>
</body>
</html>
