<?php
// Panel de gestion de traslados: los que estan en curso, los finalizados y los cancelados.

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/TrasladoModelo.php';
$usuarioActual = requerirModulo('ambulancias');

$modelo = new TrasladoModelo();

$vistas = ['activos' => 'En curso y programados', 'finalizados' => 'Finalizados', 'cancelados' => 'Cancelados'];
$vista  = array_key_exists($_GET['vista'] ?? '', $vistas) ? $_GET['vista'] : 'activos';

$resumen   = $modelo->resumen();
$busqueda  = mb_substr(trim($_GET['q'] ?? ''), 0, 50);
$traslados = $modelo->listar($vista, $busqueda);

$aviso = tomarAviso();

require __DIR__ . '/../Vista/traslados.php';
