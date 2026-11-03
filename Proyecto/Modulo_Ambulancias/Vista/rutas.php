<?php
// Vista: rutas y lugares. Variables: $rutas, $ubicaciones, $tiposUbicacion, $ruta, $lugar, $errores, $aviso, $token
$raiz = '../../';
$seccionActiva = 'rutas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rutas · S.I.G.S.M.</title>
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
        <h1>Rutas</h1>
        <p>Rutina base de cada recorrido del circuito nacional. Con el tiempo de la ruta se calcula la llegada estimada.</p>
      </div>
    </div>

    <?php require __DIR__ . '/../../Servicios_Comunes/Vista_General/aviso.php'; ?>
    <?php if (isset($errores['general'])): ?><div class="aviso error" role="alert"><?= e($errores['general']) ?></div><?php endif; ?>

    <div class="fila-columnas">
      <section class="tarjeta lista-docs">
        <div class="lista-cabecera"><h2>Rutas cargadas</h2><span class="etiqueta-info"><?= count($rutas) ?> rutas</span></div>
        <div class="tabla-scroll">
          <table class="tabla">
            <thead>
              <tr>
                <th scope="col">Recorrido</th>
                <th scope="col">Distancia</th>
                <th scope="col">Tiempo</th>
                <th scope="col">Usada</th>
                <th scope="col" class="col-acciones"><span class="sr-only">Acciones</span></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rutas as $r): ?>
                <tr>
                  <td>
                    <strong class="recorrido"><?= e($r['origen']) ?> → <?= e($r['destino']) ?></strong>
                    <?php if ($r['descripcion']): ?><span class="sub"><?= e($r['descripcion']) ?></span><?php endif; ?>
                  </td>
                  <td class="sin-corte"><?= e(number_format((float) $r['distancia_km'], 1, ',', '.')) ?> km</td>
                  <td class="sin-corte"><?= (int) $r['tiempo_min'] >= 60 ? intdiv((int) $r['tiempo_min'], 60) . ' h ' . ((int) $r['tiempo_min'] % 60) . ' min' : (int) $r['tiempo_min'] . ' min' ?></td>
                  <td><?= (int) $r['usos'] ?> veces</td>
                  <td class="col-acciones">
                    <?php if (!(int) $r['usos']): ?>
                      <form method="post" action="rutas.php" data-confirmar="¿Eliminar la ruta <?= e($r['origen'] . ' → ' . $r['destino']) ?>?">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= (int) $r['id_ruta'] ?>">
                        <button type="submit" class="btn-icono peligro" aria-label="Eliminar ruta" title="Eliminar">
                          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M6 7l1 13h10l1-13"></path><path d="M9 7V4h6v3"></path></svg>
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <div class="columna-lateral">
        <form class="tarjeta formulario" method="post" action="rutas.php" novalidate>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <input type="hidden" name="accion" value="ruta">
          <h2>Nueva ruta</h2>
          <?php if (isset($errores['ruta'])): ?><div class="aviso error" role="alert"><?= e($errores['ruta']) ?></div><?php endif; ?>
          <div class="campo">
            <label for="origen">Desde</label>
            <select id="origen" name="origen">
              <?php foreach ($ubicaciones as $u): ?><option value="<?= (int) $u['id_ubicacion'] ?>" <?= (int) $u['id_ubicacion'] === $ruta['origen'] ? 'selected' : '' ?>><?= e($u['nombre']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="campo">
            <label for="destino">Hasta</label>
            <select id="destino" name="destino">
              <option value="">Elegí el destino</option>
              <?php foreach ($ubicaciones as $u): ?><option value="<?= (int) $u['id_ubicacion'] ?>" <?= (int) $u['id_ubicacion'] === $ruta['destino'] ? 'selected' : '' ?>><?= e($u['nombre']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="campos-fila">
            <div class="campo">
              <label for="km">Kilómetros</label>
              <input id="km" name="km" type="text" inputmode="decimal" value="<?= e($ruta['km']) ?>" placeholder="Ej: 22,5">
            </div>
            <div class="campo">
              <label for="minutos">Minutos</label>
              <input id="minutos" name="minutos" type="text" inputmode="numeric" value="<?= e($ruta['minutos']) ?>" placeholder="Ej: 35">
            </div>
          </div>
          <div class="campo">
            <div class="campo-cabecera"><label for="descripcion">Por dónde va</label><span class="chip-opcional">Opcional</span></div>
            <input id="descripcion" name="descripcion" type="text" maxlength="255" value="<?= e($ruta['descripcion']) ?>" placeholder="Ej: Ruta 5 hasta Las Piedras">
          </div>
          <button type="submit" class="btn btn-primario">Agregar ruta</button>
        </form>

        <form class="tarjeta formulario" method="post" action="rutas.php" novalidate>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <input type="hidden" name="accion" value="lugar">
          <h2>Nuevo lugar</h2>
          <?php if (isset($errores['lugar'])): ?><div class="aviso error" role="alert"><?= e($errores['lugar']) ?></div><?php endif; ?>
          <div class="campo">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" type="text" maxlength="100" value="<?= e($lugar['nombre']) ?>" placeholder="Ej: Hospital de Florida">
          </div>
          <div class="campo">
            <label for="direccion">Dirección</label>
            <input id="direccion" name="direccion" type="text" maxlength="255" value="<?= e($lugar['direccion']) ?>">
          </div>
          <div class="campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
              <?php foreach ($tiposUbicacion as $clave => $texto): ?><option value="<?= e($clave) ?>" <?= $clave === $lugar['tipo'] ? 'selected' : '' ?>><?= e($texto) ?></option><?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-secundario">Agregar lugar</button>
        </form>
      </div>
    </div>
  </main>
</div>
<script src="../../Servicios_Comunes/Vista_General/assets/js/panel.js"></script>
</body>
</html>
