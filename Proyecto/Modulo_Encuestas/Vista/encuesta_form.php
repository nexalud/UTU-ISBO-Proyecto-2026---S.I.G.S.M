<?php
// Vista: crear / editar una encuesta con su lista de preguntas.
// Variables: $encuesta (null si es nueva), $datos, $preguntas, $categorias, $errores, $erroresPreguntas,
//            $preguntasBloqueadas, $token

$raiz = '../../';
$seccionActiva = 'encuestas';
$titulo = $encuesta ? 'Editar encuesta' : 'Nueva encuesta';
$accion = $encuesta ? 'encuesta_form.php?id=' . (int) $encuesta['id_encuesta'] : 'encuesta_form.php';

// Una pregunta del editor. $i es el indice del campo (en la plantilla vale __N__).
function editorPregunta($i, array $p, ?string $error): void
{
    $tipo = $p['tipo'];
    $opciones = $p['opciones'] ?: ['', ''];
    ?>
    <li class="pregunta-editor<?= $error ? ' con-error' : '' ?>" data-pregunta data-indice="<?= e((string) $i) ?>">
      <div class="pregunta-editor-cabecera">
        <span class="pregunta-tipo">PREGUNTA <span data-numero></span></span>
        <div class="acciones-fila">
          <button type="button" class="btn-icono" data-subir aria-label="Subir pregunta" title="Subir">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5"></path><path d="M6 11l6-6 6 6"></path></svg>
          </button>
          <button type="button" class="btn-icono" data-bajar aria-label="Bajar pregunta" title="Bajar">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M6 13l6 6 6-6"></path></svg>
          </button>
          <button type="button" class="btn-icono peligro" data-quitar-pregunta aria-label="Quitar pregunta" title="Quitar">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg>
          </button>
        </div>
      </div>

      <div class="campo">
        <label for="p<?= e((string) $i) ?>-texto">Pregunta</label>
        <input id="p<?= e((string) $i) ?>-texto" name="preguntas[<?= e((string) $i) ?>][texto]" type="text" maxlength="255"
               value="<?= e($p['texto']) ?>" placeholder="Ej: ¿Cómo calificarías la atención recibida?">
      </div>

      <div class="campos-fila">
        <div class="campo">
          <label for="p<?= e((string) $i) ?>-tipo">Tipo de respuesta</label>
          <select id="p<?= e((string) $i) ?>-tipo" name="preguntas[<?= e((string) $i) ?>][tipo]" data-tipo>
            <?php foreach (GestionEncuestaModelo::TIPOS_PREGUNTA as $clave => $texto): ?>
              <option value="<?= e($clave) ?>" <?= $clave === $tipo ? 'selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="interruptor interruptor-chico">
          <span><strong>Obligatoria</strong><small>El paciente no puede enviar sin responderla.</small></span>
          <input type="checkbox" name="preguntas[<?= e((string) $i) ?>][obligatoria]" value="1" <?= $p['obligatoria'] ? 'checked' : '' ?>>
        </label>
      </div>

      <div class="zona-tipo" data-zona="opcion_unica" <?= $tipo === 'opcion_unica' ? '' : 'hidden' ?>>
        <span class="etiqueta-chica">Opciones (el paciente elige una)</span>
        <ul class="lista-opciones" data-opciones>
          <?php foreach ($opciones as $o): ?>
            <li>
              <span class="punto-opcion" aria-hidden="true"></span>
              <input type="text" name="preguntas[<?= e((string) $i) ?>][opciones][]" maxlength="150" value="<?= e($o) ?>" placeholder="Escribí una opción" aria-label="Opción">
              <button type="button" class="btn-icono" data-quitar-opcion aria-label="Quitar opción" title="Quitar opción">
                <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12"></path><path d="M18 6L6 18"></path></svg>
              </button>
            </li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-texto" data-agregar-opcion>+ Agregar opción</button>
      </div>

      <div class="zona-tipo" data-zona="escala" <?= $tipo === 'escala' ? '' : 'hidden' ?>>
        <span class="etiqueta-chica">El paciente elige de 1 a 5 estrellas</span>
        <div class="vista-escala">
          <?php foreach (GestionEncuestaModelo::OPCIONES_ESCALA as $o): ?><span><?= e($o) ?></span><?php endforeach; ?>
        </div>
      </div>

      <div class="zona-tipo" data-zona="abierta" <?= $tipo === 'abierta' ? '' : 'hidden' ?>>
        <span class="etiqueta-chica">El paciente escribe con sus palabras (hasta 500 caracteres)</span>
      </div>

      <?php if ($error): ?>
        <p class="mensaje-error" role="alert">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
          <?= e($error) ?>
        </p>
      <?php endif; ?>
    </li>
    <?php
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
        <a href="encuestas.php">Encuestas</a>
        <span aria-hidden="true">/</span>
        <span><?= e($encuesta ? $encuesta['titulo'] : 'Nueva encuesta') ?></span>
      </nav>
      <div class="encabezado"><h1><?= e($titulo) ?></h1></div>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="aviso error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>

    <?php if (!$categorias): ?>
      <div class="aviso error" role="alert">
        Primero tenés que crear una categoría de tipo encuesta (es la que da el QR).
        <a href="../../Modulo_Documentacion/Controlador/categoria_form.php?tipo=encuesta">Crear categoría</a>
      </div>
    <?php endif; ?>

    <form class="formulario-encuesta" method="post" action="<?= e($accion) ?>" novalidate>
      <input type="hidden" name="token" value="<?= e($token) ?>">

      <div class="fila-columnas">
        <section class="tarjeta formulario">
          <h2>Datos de la encuesta</h2>
          <p class="nota" style="font-size:14px">Los campos con <strong class="obligatorio">*</strong> son obligatorios.</p>

          <div class="<?= claseCampo($errores, 'titulo') ?>">
            <label for="titulo">Título <span class="obligatorio" aria-hidden="true">*</span></label>
            <input id="titulo" name="titulo" type="text" maxlength="150" value="<?= e($datos['titulo']) ?>"
                   placeholder="Ej: Satisfacción del usuario trasplantado">
            <span class="ayuda">El paciente lo ve arriba de la encuesta.</span>
            <?= errorCampo($errores, 'titulo') ?>
          </div>

          <div class="<?= claseCampo($errores, 'descripcion') ?>">
            <div class="campo-cabecera">
              <label for="descripcion">Texto de bienvenida</label>
              <span class="chip-opcional">Opcional</span>
            </div>
            <textarea id="descripcion" name="descripcion" maxlength="255"
                      placeholder="Ej: Tu opinión nos ayuda a mejorar. Es anónima y lleva menos de un minuto."><?= e($datos['descripcion']) ?></textarea>
            <?= errorCampo($errores, 'descripcion') ?>
          </div>

          <div class="campos-fila">
            <div class="<?= claseCampo($errores, 'categoria') ?>">
              <label for="categoria">Categoría (QR) <span class="obligatorio" aria-hidden="true">*</span></label>
              <select id="categoria" name="categoria">
                <option value="">Elegí una categoría</option>
                <?php foreach ($categorias as $c): ?>
                  <option value="<?= (int) $c['id_categoria'] ?>" <?= (int) $c['id_categoria'] === $datos['categoria'] ? 'selected' : '' ?>>
                    <?= e($c['nombre']) ?> (<?= e($c['codigo_qr']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <?= errorCampo($errores, 'categoria') ?>
            </div>

            <div class="<?= claseCampo($errores, 'segmento') ?>">
              <label for="segmento">Segmento <span class="obligatorio" aria-hidden="true">*</span></label>
              <input id="segmento" name="segmento" type="text" maxlength="100" value="<?= e($datos['segmento']) ?>"
                     placeholder="Ej: Usuarios trasplantados" list="segmentos-sugeridos">
              <datalist id="segmentos-sugeridos">
                <option value="Pacientes internados">
                <option value="Pacientes ambulatorios">
                <option value="Usuarios trasplantados">
                <option value="Pacientes de nefrología">
                <option value="Acompañantes y familiares">
              </datalist>
              <span class="ayuda">A quiénes está dirigida.</span>
              <?= errorCampo($errores, 'segmento') ?>
            </div>
          </div>

          <div class="campos-fila">
            <div class="<?= claseCampo($errores, 'fecha_inicio') ?>">
              <label for="fecha_inicio">Abre el <span class="obligatorio" aria-hidden="true">*</span></label>
              <input id="fecha_inicio" name="fecha_inicio" type="date" value="<?= e($datos['fecha_inicio']) ?>">
              <?= errorCampo($errores, 'fecha_inicio') ?>
            </div>
            <div class="<?= claseCampo($errores, 'fecha_fin') ?>">
              <div class="campo-cabecera">
                <label for="fecha_fin">Cierra el</label>
                <span class="chip-opcional">Opcional</span>
              </div>
              <input id="fecha_fin" name="fecha_fin" type="date" value="<?= e($datos['fecha_fin']) ?>">
              <span class="ayuda">Vacío = queda abierta hasta que la cierres.</span>
              <?= errorCampo($errores, 'fecha_fin') ?>
            </div>
          </div>
        </section>

        <aside class="tarjeta tarjeta-ayuda">
          <h2>Cómo funciona</h2>
          <p>El paciente escanea el QR de la categoría y responde la encuesta que esté <strong>abierta</strong> ese día. Si hay dos abiertas en la misma categoría, se muestra la más nueva.</p>
          <p>Las respuestas son <strong>anónimas</strong>: no se guarda ningún dato del paciente.</p>
          <p>La primera pregunta de escala se usa para la <strong>calificación promedio</strong> en Resultados.</p>
        </aside>
      </div>

      <section class="tarjeta seccion-preguntas">
        <div class="lista-cabecera">
          <div>
            <h2>Preguntas</h2>
            <p class="nota" style="font-size:14px;margin-top:4px">Se muestran al paciente en este orden.</p>
          </div>
        </div>

        <?php if (isset($errores['preguntas'])): ?>
          <div class="aviso error" role="alert"><?= e($errores['preguntas']) ?></div>
        <?php endif; ?>

        <?php if ($preguntasBloqueadas): ?>
          <div class="aviso info">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
            <span>Esta encuesta ya tiene <?= (int) $encuesta['respuestas'] ?> respuestas, así que las preguntas no se pueden cambiar (se mezclarían los resultados).
              Si necesitás otras preguntas, <a href="encuesta_form.php?duplicar=<?= (int) $encuesta['id_encuesta'] ?>">creá una copia</a> y cerrá esta.</span>
          </div>
          <ol class="lista-preguntas-fija">
            <?php foreach ($preguntas as $p): ?>
              <li>
                <strong><?= e($p['texto']) ?></strong>
                <span>
                  <?= e(GestionEncuestaModelo::TIPOS_PREGUNTA[$p['tipo']]) ?> · <?= $p['obligatoria'] ? 'Obligatoria' : 'Opcional' ?>
                  <?php if ($p['tipo'] === 'opcion_unica'): ?> · <?= e(implode(' / ', $p['opciones'])) ?><?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php else: ?>
          <ol class="lista-preguntas" id="lista-preguntas" data-siguiente="<?= count($preguntas) ?>">
            <?php foreach ($preguntas as $n => $p) { editorPregunta($n, $p, $erroresPreguntas[$n] ?? null); } ?>
          </ol>
          <button type="button" class="btn btn-secundario agregar-pregunta" id="agregar-pregunta">
            <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            Agregar pregunta
          </button>

          <template id="plantilla-pregunta">
            <?php editorPregunta('__N__', ['texto' => '', 'tipo' => 'opcion_unica', 'obligatoria' => true, 'opciones' => []], null); ?>
          </template>
        <?php endif; ?>
      </section>

      <div class="tarjeta botones-form barra-guardar">
        <a class="btn btn-secundario" href="encuestas.php">Cancelar</a>
        <button type="submit" class="btn btn-primario">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
          <?= $encuesta ? 'Guardar cambios' : 'Crear encuesta' ?>
        </button>
      </div>
    </form>
  </main>
</div>
<script src="../Vista/assets/js/editor_encuesta.js"></script>
</body>
</html>
