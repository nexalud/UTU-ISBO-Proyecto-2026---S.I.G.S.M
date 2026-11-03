<?php
// API SIMULADA de pacientes del hospital. Responde en JSON, como lo haría la API real.
//   GET pacientes.php?ci=34567894  ->  {"ci":"34567894","nombre":"María","apellido":"Silva",...}
// En el servidor real, PacienteApi apunta a la API del hospital (variable API_PACIENTES_URL) y este archivo no se usa.

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$ci = preg_replace('/\D/', '', (string) ($_GET['ci'] ?? ''));
$pacientes = require __DIR__ . '/datos_pacientes.php';

if (!isset($pacientes[$ci])) {
    http_response_code(404);
    echo json_encode(['error' => 'No existe un paciente con esa cédula']);
    exit;
}

echo json_encode(['ci' => $ci] + $pacientes[$ci], JSON_UNESCAPED_UNICODE);
