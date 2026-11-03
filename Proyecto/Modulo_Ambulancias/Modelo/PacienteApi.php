<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';

// Consulta los datos de un paciente por cédula a la API externa del hospital.
// - Si existe la variable API_PACIENTES_URL (servidor real), se llama por HTTP a esa API.
// - Si no, se usa la simulacion de /Proyecto/API_Pacientes (para XAMPP y para la demo).
// Lo que se encuentra se guarda en la tabla paciente como copia local.
class PacienteApi
{
    // Valida una cédula uruguaya con su dígito verificador (ej: 3.456.789-4)
    public static function cedulaValida(string $ci): bool
    {
        if (!preg_match('/^\d{7,8}$/', $ci)) {
            return false;
        }
        $ci = str_pad($ci, 8, '0', STR_PAD_LEFT);
        $pesos = [2, 9, 8, 7, 6, 3, 4];
        $suma = 0;
        for ($i = 0; $i < 7; $i++) {
            $suma += (int) $ci[$i] * $pesos[$i];
        }
        return (10 - $suma % 10) % 10 === (int) $ci[7];
    }

    // Saca puntos y guiones: "3.456.789-4" -> "34567894"
    public static function limpiar(string $ci): string
    {
        return preg_replace('/\D/', '', $ci);
    }

    // Busca primero en la API; si no esta, en los pacientes ya guardados (por ejemplo, cargados a mano).
    // Devuelve ['ci', 'nombre', 'apellido', 'origen' => 'api'|'local'] o null si no existe en ningun lado
    public function buscarEnTodo(string $ci): ?array
    {
        $paciente = $this->buscar($ci);
        if ($paciente) {
            return $paciente + ['origen' => 'api'];
        }
        $db = conectarBD();
        $stmt = $db->prepare("SELECT ci, nombre, apellido FROM paciente WHERE ci = ?");
        $ci = self::limpiar($ci);
        $stmt->bind_param('s', $ci);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        return $fila ? $fila + ['origen' => 'local'] : null;
    }

    // Ingreso manual (cuando la API no tiene al paciente o no responde)
    public function guardarManual(string $ci, string $nombre, string $apellido): array
    {
        $paciente = ['ci' => self::limpiar($ci), 'nombre' => $nombre, 'apellido' => $apellido];
        $this->guardarCopia($paciente);
        return $paciente;
    }

    // Devuelve ['ci', 'nombre', 'apellido'] o null si no existe
    public function buscar(string $ci): ?array
    {
        $ci = self::limpiar($ci);
        if (!self::cedulaValida($ci)) {
            return null;
        }

        $url = getenv('API_PACIENTES_URL');
        $datos = $url ? $this->consultarHttp($url, $ci) : $this->consultarSimulacion($ci);

        if (!$datos) {
            return null;
        }

        $paciente = ['ci' => $ci, 'nombre' => (string) $datos['nombre'], 'apellido' => (string) $datos['apellido']];
        $this->guardarCopia($paciente);
        return $paciente;
    }

    private function consultarSimulacion(string $ci): ?array
    {
        $pacientes = require __DIR__ . '/../../API_Pacientes/datos_pacientes.php';
        return $pacientes[$ci] ?? null;
    }

    private function consultarHttp(string $url, string $ci): ?array
    {
        $contexto = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $respuesta = @file_get_contents(rtrim($url, '?') . '?ci=' . urlencode($ci), false, $contexto);
        if ($respuesta === false) {
            error_log('La API de pacientes no respondió');
            return null;
        }
        $datos = json_decode($respuesta, true);
        return (is_array($datos) && isset($datos['nombre'], $datos['apellido'])) ? $datos : null;
    }

    // Copia local: si ya estaba, se actualizan los datos y la fecha de consulta
    private function guardarCopia(array $p): void
    {
        $db = conectarBD();
        $stmt = $db->prepare(
            "INSERT INTO paciente (ci, nombre, apellido) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), apellido = VALUES(apellido), fecha_consulta_api = NOW()"
        );
        $stmt->bind_param('sss', $p['ci'], $p['nombre'], $p['apellido']);
        $stmt->execute();
    }
}
