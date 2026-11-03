<?php
// Lo usa el formulario de traslado (con JavaScript) para traer nombre y apellido por la cédula.
// Responde JSON. Solo para funcionarios con sesion: los datos de pacientes no son publicos.

require_once __DIR__ . '/../../Servicios_Comunes/Autenticacion/sesion.php';
require_once __DIR__ . '/../Modelo/PacienteApi.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = usuarioActual();
if (!$usuario || !puedeEntrarAlPanel($usuario) || !puedeUsarModulo($usuario, 'ambulancias')) {
    http_response_code(401);
    echo json_encode(['error' => 'Iniciá sesión de nuevo.']);
    exit;
}

$ci = PacienteApi::limpiar((string) ($_GET['ci'] ?? ''));

if (!PacienteApi::cedulaValida($ci)) {
    http_response_code(422);
    echo json_encode(['error' => 'La cédula no es válida. Revisá el dígito verificador.']);
    exit;
}

$paciente = (new PacienteApi())->buscarEnTodo($ci);

if (!$paciente) {
    // El formulario muestra los campos para cargarlo a mano
    http_response_code(404);
    echo json_encode(['error' => 'No está en el sistema del hospital. Cargá el nombre y el apellido a mano.']);
    exit;
}

echo json_encode($paciente, JSON_UNESCAPED_UNICODE);
