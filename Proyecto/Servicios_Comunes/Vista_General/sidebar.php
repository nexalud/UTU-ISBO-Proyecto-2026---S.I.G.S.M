<?php
// Menu lateral del panel del funcionario.
// Antes de incluirlo, definir $seccionActiva ('inicio', 'documentos', 'categorias', 'encuestas', 'resultados', 'usuarios', 'mi_cuenta'),
// $raiz (ruta hasta /Proyecto/) y $usuarioActual (lo devuelve requerirFuncionario()).

$grupos = [
    '' => [
        'inicio' => [
            'texto' => 'Inicio',
            'url'   => $raiz . 'Modulo_Documentacion/Controlador/inicio.php',
            'icono' => '<path d="M3 11l9-7 9 7"></path><path d="M5 10v10h14V10"></path><path d="M10 20v-6h4v6"></path>',
        ],
    ],
    'DOCUMENTACIÓN' => [
        'documentos' => [
            'texto' => 'Documentos y QR',
            'url'   => $raiz . 'Modulo_Documentacion/Controlador/documentos.php',
            'icono' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><path d="M14 3v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path>',
        ],
        'categorias' => [
            'texto' => 'Categorías',
            'url'   => $raiz . 'Modulo_Documentacion/Controlador/categorias.php',
            'icono' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>',
        ],
    ],
    'ENCUESTAS' => [
        'encuestas' => [
            'texto' => 'Encuestas',
            'url'   => $raiz . 'Modulo_Encuestas/Controlador/encuestas.php',
            'icono' => '<rect x="5" y="3" width="14" height="18" rx="2"></rect><path d="M9 8h6"></path><path d="M9 12h6"></path><path d="M9 16h3"></path>',
        ],
        'resultados' => [
            'texto' => 'Resultados',
            'url'   => $raiz . 'Modulo_Encuestas/Controlador/resultados.php',
            'icono' => '<path d="M4 20V10"></path><path d="M10 20V4"></path><path d="M16 20v-7"></path><path d="M22 20H2"></path>',
        ],
    ],
];

// La seccion de usuarios solo la ve el administrador
if (!empty($usuarioActual) && esAdministrador($usuarioActual)) {
    $grupos['ADMINISTRACIÓN'] = [
        'usuarios' => [
            'texto' => 'Usuarios',
            'url'   => $raiz . 'Servicios_Comunes/Usuarios/usuarios.php',
            'icono' => '<circle cx="9" cy="8" r="3.5"></circle><path d="M2.5 20a6.5 6.5 0 0 1 13 0"></path><path d="M16 4.5a3.5 3.5 0 0 1 0 7"></path><path d="M18.5 14a6.5 6.5 0 0 1 3 6"></path>',
        ],
    ];
}
?>
<aside class="sidebar">
  <div class="sidebar-marca">
    <div class="sidebar-logo"><img src="<?= e($raiz) ?>assets/hospitalLogo.png" alt="Hospital de Clínicas"></div>
    <div>
      <span class="sidebar-nombre">Hospital de Clínicas</span>
      <span class="sidebar-sub">S.I.G.S.M.</span>
    </div>
  </div>

  <nav class="sidebar-nav" aria-label="Menú principal">
    <?php foreach ($grupos as $tituloGrupo => $enlaces): ?>
      <?php if ($tituloGrupo !== ''): ?><span class="sidebar-seccion"><?= e($tituloGrupo) ?></span><?php endif; ?>
      <?php foreach ($enlaces as $clave => $enlace): ?>
        <a class="sidebar-item<?= $seccionActiva === $clave ? ' activo' : '' ?>" href="<?= e($enlace['url']) ?>"
           <?= $seccionActiva === $clave ? 'aria-current="page"' : '' ?>>
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><?= $enlace['icono'] ?></svg>
          <?= e($enlace['texto']) ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>

    <span class="sidebar-seccion">SERVICIOS</span>
    <a class="sidebar-item" href="<?= e($raiz) ?>Modulo_Ambulancias/Vista/indexA.html">
      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 16V7h11v9"></path><path d="M14 10h4l3 3v3h-7"></path><circle cx="7" cy="17.5" r="2"></circle><circle cx="17" cy="17.5" r="2"></circle><path d="M8.5 9.5v3"></path><path d="M7 11h3"></path></svg>
      Ambulancias
      <span class="chip-pronto">Prototipo</span>
    </a>
  </nav>

  <?php if (!empty($usuarioActual)): ?>
    <div class="sidebar-usuario">
      <div class="sidebar-persona">
        <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($usuarioActual['nombre'], 0, 1) . mb_substr($usuarioActual['apellido'], 0, 1))) ?></span>
        <div>
          <span class="sidebar-nombre"><?= e($usuarioActual['nombre'] . ' ' . $usuarioActual['apellido']) ?></span>
          <span class="sidebar-sub"><?= e(rolPrincipal($usuarioActual)) ?></span>
        </div>
      </div>
      <a class="sidebar-item<?= $seccionActiva === 'mi_cuenta' ? ' activo' : '' ?>" href="<?= e($raiz) ?>Servicios_Comunes/Autenticacion/mi_cuenta.php">
        <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
        Mi cuenta
      </a>
      <form method="post" action="<?= e($raiz) ?>Servicios_Comunes/Autenticacion/logout.php">
        <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
        <button type="submit" class="sidebar-item salir">
          <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
          Cerrar sesión
        </button>
      </form>
    </div>
  <?php endif; ?>
</aside>
