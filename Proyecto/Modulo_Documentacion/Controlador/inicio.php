<?php
// Pantalla de inicio del panel: resumen con los numeros del sistema.

require_once __DIR__ . '/../../Servicios_Comunes/Seguridad/helpers.php';
require_once __DIR__ . '/../Modelo/PanelModelo.php';

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
$usuarioActual = requerirFuncionario();

$modelo      = new PanelModelo();
$resumen     = $modelo->resumen();
$calificacion = $modelo->calificacionGeneral();
$documentos  = $modelo->ultimosDocumentos();
$encuestas   = $modelo->encuestasAbiertas();

// Diferencia de respuestas contra el mes anterior
$diferencia = $resumen['respuestas_mes'] - $resumen['respuestas_mes_anterior'];

$hora   = (int) date('G');
$saludo = $hora < 12 ? 'Buen día' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');

$aviso = tomarAviso();

require __DIR__ . '/../Vista/inicio.php';
