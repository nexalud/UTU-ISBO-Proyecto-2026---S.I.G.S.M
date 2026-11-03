<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';
require_once __DIR__ . '/TrasladoModelo.php';

// Flota (vehiculos), lugares y rutas del circuito nacional.
class FlotaModelo
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = conectarBD();
    }

    private function filas(string $sql, string $tipos = '', ...$valores): array
    {
        $stmt = $this->db->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$valores);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ---------- Vehiculos ----------

    // Cada vehiculo con su tipo y, si esta en un traslado en curso, cual.
    // $buscar filtra por matricula, marca, modelo o tipo (vacio = todos)
    public function listarVehiculos(string $buscar = ''): array
    {
        $activos = implode(',', TrasladoModelo::ACTIVOS);
        $patron  = '%' . $buscar . '%';
        return $this->filas(
            "SELECT v.id_vehiculo, v.matricula, v.marca, v.modelo, v.anho, v.activo, v.id_tipo_vehiculo, tv.nombre AS tipo,
                    (SELECT t.id_traslado FROM traslado t
                     WHERE t.id_vehiculo = v.id_vehiculo
                       AND (SELECT h.id_estado FROM historial_estado h WHERE h.id_traslado = t.id_traslado ORDER BY h.id_historial DESC LIMIT 1) IN ($activos)
                     LIMIT 1) AS en_traslado
             FROM vehiculo v
             INNER JOIN tipo_vehiculo tv ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
             WHERE CONCAT_WS(' ', v.matricula, v.marca, v.modelo, tv.nombre) LIKE ?
             ORDER BY v.activo DESC, tv.id_tipo_vehiculo, v.matricula",
            's', $patron
        );
    }

    public function obtenerVehiculo(int $id): ?array
    {
        return $this->filas("SELECT * FROM vehiculo WHERE id_vehiculo = ?", 'i', $id)[0] ?? null;
    }

    public function tiposVehiculo(): array
    {
        return $this->filas("SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo ORDER BY id_tipo_vehiculo");
    }

    public function existeMatricula(string $matricula, int $excepto = 0): bool
    {
        return (bool) $this->filas("SELECT 1 FROM vehiculo WHERE matricula = ? AND id_vehiculo <> ?", 'si', $matricula, $excepto);
    }

    public function guardarVehiculo(?int $id, array $d): void
    {
        if ($id) {
            $stmt = $this->db->prepare(
                "UPDATE vehiculo SET matricula = ?, marca = ?, modelo = ?, anho = ?, id_tipo_vehiculo = ? WHERE id_vehiculo = ?"
            );
            $stmt->bind_param('sssiii', $d['matricula'], $d['marca'], $d['modelo'], $d['anho'], $d['tipo'], $id);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO vehiculo (matricula, marca, modelo, anho, id_tipo_vehiculo, activo) VALUES (?, ?, ?, ?, ?, 1)"
            );
            $stmt->bind_param('sssii', $d['matricula'], $d['marca'], $d['modelo'], $d['anho'], $d['tipo']);
        }
        $stmt->execute();
    }

    // Poner o sacar de servicio (los vehiculos no se borran: quedan en el historial de traslados)
    public function cambiarEstadoVehiculo(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE vehiculo SET activo = 1 - activo WHERE id_vehiculo = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    // ---------- Lugares y rutas ----------

    public function listarUbicaciones(): array
    {
        return $this->filas("SELECT id_ubicacion, nombre, direccion, tipo_ubicacion FROM ubicacion ORDER BY id_ubicacion = 1 DESC, nombre");
    }

    public function existeUbicacion(string $nombre): bool
    {
        return (bool) $this->filas("SELECT 1 FROM ubicacion WHERE nombre = ?", 's', $nombre);
    }

    public function crearUbicacion(string $nombre, string $direccion, string $tipo): void
    {
        $stmt = $this->db->prepare("INSERT INTO ubicacion (nombre, direccion, tipo_ubicacion) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $nombre, $direccion, $tipo);
        $stmt->execute();
    }

    public function listarRutas(): array
    {
        return $this->filas(
            "SELECT r.id_ruta, r.descripcion, r.distancia_km, r.tiempo_min, o.nombre AS origen, d.nombre AS destino,
                    (SELECT COUNT(*) FROM traslado t WHERE t.id_ruta = r.id_ruta) AS usos
             FROM ruta r
             INNER JOIN ubicacion o ON o.id_ubicacion = r.id_origen
             INNER JOIN ubicacion d ON d.id_ubicacion = r.id_destino
             ORDER BY o.nombre, r.distancia_km"
        );
    }

    public function existeRuta(int $origen, int $destino): bool
    {
        return (bool) $this->filas("SELECT 1 FROM ruta WHERE id_origen = ? AND id_destino = ?", 'ii', $origen, $destino);
    }

    public function crearRuta(int $origen, int $destino, ?string $descripcion, float $km, int $minutos): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO ruta (id_origen, id_destino, descripcion, distancia_km, tiempo_min) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iisdi', $origen, $destino, $descripcion, $km, $minutos);
        $stmt->execute();
    }

    // Una ruta que ya se uso en un traslado no se borra (si se borrara, el traslado queda sin ruta)
    public function eliminarRuta(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM ruta WHERE id_ruta = ? AND NOT EXISTS (SELECT 1 FROM traslado t WHERE t.id_ruta = ruta.id_ruta)"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->affected_rows === 1;
    }
}
