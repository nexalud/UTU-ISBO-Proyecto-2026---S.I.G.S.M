<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';

// Traslados: alta (solicitud + asignacion), seguimiento del estado e historial.
class TrasladoModelo
{
    // Estados (tabla estado_traslado)
    public const PROGRAMADO = 1;
    public const EN_VIAJE   = 2;
    public const EN_DESTINO = 3;
    public const REGRESANDO = 4;
    public const FINALIZADO = 5;
    public const CANCELADO  = 6;

    // Ciclo normal: de cada estado se pasa al siguiente con un boton
    public const SIGUIENTE = [
        self::PROGRAMADO => self::EN_VIAJE,
        self::EN_VIAJE   => self::EN_DESTINO,
        self::EN_DESTINO => self::REGRESANDO,
        self::REGRESANDO => self::FINALIZADO,
    ];

    // Texto del boton para avanzar desde cada estado
    public const ACCION = [
        self::PROGRAMADO => 'Registrar salida',
        self::EN_VIAJE   => 'Registrar llegada al destino',
        self::EN_DESTINO => 'Registrar regreso',
        self::REGRESANDO => 'Registrar llegada al hospital',
    ];

    // Un traslado "ocupa" vehiculo y personal mientras no este finalizado ni cancelado
    public const ACTIVOS = [self::PROGRAMADO, self::EN_VIAJE, self::EN_DESTINO, self::REGRESANDO];

    // Estado actual = el ultimo registro del historial
    private const ESTADO_ACTUAL = "(SELECT h.id_estado FROM historial_estado h
                                   WHERE h.id_traslado = t.id_traslado
                                   ORDER BY h.id_historial DESC LIMIT 1)";

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

    private function lista(array $ids): string
    {
        return implode(',', array_map('intval', $ids));
    }

    // ---------- Consultas para el panel ----------

    private const SELECT_TRASLADO = "SELECT t.id_traslado, t.hora_salida_prev, t.hora_salida_real, t.hora_llegada_prev,
               t.hora_llegada_real, t.hora_retorno, t.id_vehiculo, t.id_proveedor, t.id_chofer, t.id_enfermero,
               s.id_solicitud, s.motivo, s.fecha_solicitud, s.ci_paciente, s.id_tipo_elemento, s.id_origen, s.id_destino, s.id_canal,
               te.nombre AS tipo_elemento, te.requiere_paciente,
               CONCAT(p.nombre, ' ', p.apellido) AS paciente, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
               o.nombre AS origen, d.nombre AS destino, cs.nombre AS canal,
               v.matricula, CONCAT(v.marca, ' ', v.modelo) AS vehiculo, tv.nombre AS tipo_vehiculo,
               CONCAT(ch.nombre, ' ', ch.apellido) AS chofer,
               CONCAT(en.nombre, ' ', en.apellido) AS enfermero,
               pr.nombre AS proveedor, r.distancia_km, r.tiempo_min,
               CONCAT(us.nombre, ' ', us.apellido) AS registrado_por,
               et.id_estado, et.nombre AS estado
        FROM traslado t
        INNER JOIN solicitud_traslado s ON s.id_solicitud = t.id_solicitud
        INNER JOIN tipo_elemento te     ON te.id_tipo_elemento = s.id_tipo_elemento
        INNER JOIN ubicacion o          ON o.id_ubicacion = s.id_origen
        INNER JOIN ubicacion d          ON d.id_ubicacion = s.id_destino
        INNER JOIN canal_solicitud cs   ON cs.id_canal = s.id_canal
        INNER JOIN usuario us           ON us.id_usuario = s.id_usuario_solicit
        LEFT JOIN paciente p            ON p.ci = s.ci_paciente
        LEFT JOIN vehiculo v            ON v.id_vehiculo = t.id_vehiculo
        LEFT JOIN tipo_vehiculo tv      ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
        LEFT JOIN usuario ch            ON ch.id_usuario = t.id_chofer
        LEFT JOIN usuario en            ON en.id_usuario = t.id_enfermero
        LEFT JOIN proveedor_externo pr  ON pr.id_proveedor = t.id_proveedor
        LEFT JOIN ruta r                ON r.id_ruta = t.id_ruta
        INNER JOIN estado_traslado et   ON et.id_estado = " . self::ESTADO_ACTUAL;

    // $vista: 'activos', 'finalizados' o 'cancelados'.
    // $buscar filtra por paciente (cédula, nombre o apellido) o por vehículo (matrícula)
    public function listar(string $vista, string $buscar = ''): array
    {
        $estados = ['activos' => self::ACTIVOS, 'finalizados' => [self::FINALIZADO], 'cancelados' => [self::CANCELADO]][$vista]
            ?? self::ACTIVOS;
        $orden = $vista === 'activos' ? 'et.orden DESC, t.hora_salida_prev' : 't.hora_salida_prev DESC';

        return $this->filas(
            self::SELECT_TRASLADO . " WHERE et.id_estado IN (" . $this->lista($estados) . ")
              AND CONCAT_WS(' ', s.ci_paciente, p.nombre, p.apellido, v.matricula, REPLACE(v.matricula, ' ', '')) LIKE ?
            ORDER BY $orden LIMIT 100",
            's', '%' . $buscar . '%'
        );
    }

    public function obtener(int $idTraslado): ?array
    {
        return $this->filas(self::SELECT_TRASLADO . " WHERE t.id_traslado = ?", 'i', $idTraslado)[0] ?? null;
    }

    public function historial(int $idTraslado): array
    {
        return $this->filas(
            "SELECT h.id_estado, e.nombre AS estado, h.fecha_hora, h.observaciones, CONCAT(u.nombre, ' ', u.apellido) AS usuario
             FROM historial_estado h
             INNER JOIN estado_traslado e ON e.id_estado = h.id_estado
             INNER JOIN usuario u ON u.id_usuario = h.id_usuario
             WHERE h.id_traslado = ?
             ORDER BY h.id_historial",
            'i', $idTraslado
        );
    }

    // Numeros de arriba del panel
    public function resumen(): array
    {
        $fila = $this->filas(
            "SELECT
                SUM(" . self::ESTADO_ACTUAL . " IN (" . $this->lista([self::EN_VIAJE, self::EN_DESTINO, self::REGRESANDO]) . ")) AS en_curso,
                SUM(" . self::ESTADO_ACTUAL . " = " . self::PROGRAMADO . ") AS programados,
                SUM(" . self::ESTADO_ACTUAL . " = " . self::FINALIZADO . " AND DATE(t.hora_retorno) = CURDATE()) AS finalizados_hoy
             FROM traslado t"
        )[0];

        $flota = $this->filas(
            "SELECT COUNT(*) AS activos,
                    SUM(v.id_vehiculo NOT IN (" . $this->sqlVehiculosOcupados() . ")) AS libres
             FROM vehiculo v WHERE v.activo = 1"
        )[0];

        return array_map('intval', $fila + $flota);
    }

    // ---------- Opciones del formulario ----------

    public function tiposElemento(): array
    {
        return $this->filas("SELECT id_tipo_elemento, nombre, requiere_paciente FROM tipo_elemento ORDER BY id_tipo_elemento");
    }

    public function ubicaciones(): array
    {
        return $this->filas("SELECT id_ubicacion, nombre FROM ubicacion ORDER BY id_ubicacion = 1 DESC, nombre");
    }

    public function canales(): array
    {
        return $this->filas("SELECT id_canal, nombre FROM canal_solicitud ORDER BY id_canal");
    }

    public function proveedores(): array
    {
        return $this->filas("SELECT id_proveedor, nombre FROM proveedor_externo ORDER BY nombre");
    }

    // Rutas conocidas, para calcular la llegada estimada en el formulario
    public function rutas(): array
    {
        return $this->filas("SELECT id_ruta, id_origen, id_destino, distancia_km, tiempo_min FROM ruta");
    }

    // $excepto: al editar un traslado, lo que tiene asignado ese traslado cuenta como libre
    private function sqlVehiculosOcupados(int $excepto = 0): string
    {
        return "SELECT t.id_vehiculo FROM traslado t
                WHERE t.id_vehiculo IS NOT NULL AND t.id_traslado <> " . $excepto . " AND " . self::ESTADO_ACTUAL . " IN (" . $this->lista(self::ACTIVOS) . ")";
    }

    // Vehiculos activos y libres, con la lista de tipos de elemento que pueden llevar ("1,2,3")
    public function vehiculosLibres(int $excepto = 0): array
    {
        return $this->filas(
            "SELECT v.id_vehiculo, v.matricula, v.marca, v.modelo, tv.nombre AS tipo,
                    (SELECT GROUP_CONCAT(c.id_tipo_elemento) FROM compatibilidad c WHERE c.id_tipo_vehiculo = v.id_tipo_vehiculo) AS admite
             FROM vehiculo v
             INNER JOIN tipo_vehiculo tv ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
             WHERE v.activo = 1 AND v.id_vehiculo NOT IN (" . $this->sqlVehiculosOcupados($excepto) . ")
             ORDER BY tv.id_tipo_vehiculo, v.matricula"
        );
    }

    // Choferes o enfermeros activos que no estan en un traslado en curso. $rol: 'chofer' o 'enfermero'
    public function personalLibre(string $rol, int $excepto = 0): array
    {
        $columna = $rol === 'chofer' ? 't.id_chofer' : 't.id_enfermero';
        return $this->filas(
            "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) AS nombre
             FROM usuario u
             INNER JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
             INNER JOIN rol r ON r.id_rol = ur.id_rol
             WHERE r.nombre_rol = ? AND u.activo = 1
               AND u.id_usuario NOT IN (SELECT $columna FROM traslado t
                                        WHERE $columna IS NOT NULL AND t.id_traslado <> " . $excepto . " AND " . self::ESTADO_ACTUAL . " IN (" . $this->lista(self::ACTIVOS) . "))
             ORDER BY u.nombre, u.apellido",
            's', $rol
        );
    }

    public function rutaEntre(int $origen, int $destino): ?array
    {
        return $this->filas("SELECT id_ruta, tiempo_min FROM ruta WHERE id_origen = ? AND id_destino = ?", 'ii', $origen, $destino)[0] ?? null;
    }

    // ---------- Alta y cambios de estado ----------

    // Crea la solicitud (aprobada), el traslado y el primer registro del historial, todo junto o nada
    public function crear(array $d, int $idUsuario): int
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, motivo, estado_solicitud)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'aprobada')"
            );
            $stmt->bind_param('siiiiis', $d['ci_paciente'], $d['tipo_elemento'], $d['origen'], $d['destino'], $d['canal'], $idUsuario, $d['motivo']);
            $stmt->execute();
            $idSolicitud = $this->db->insert_id;

            $stmt = $this->db->prepare(
                "INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_proveedor, id_ruta, hora_salida_prev, hora_llegada_prev)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('iiiiiiss', $idSolicitud, $d['vehiculo'], $d['chofer'], $d['enfermero'], $d['proveedor'],
                $d['ruta'], $d['salida'], $d['llegada']);
            $stmt->execute();
            $idTraslado = $this->db->insert_id;

            $this->registrarEstado($idTraslado, self::PROGRAMADO, $idUsuario, null);

            $this->db->commit();
            return $idTraslado;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Edita un traslado que todavia no salio (Programado). Devuelve false si ya salio o no existe.
    public function actualizar(int $idTraslado, array $d): bool
    {
        $actual = $this->obtener($idTraslado);
        if (!$actual || (int) $actual['id_estado'] !== self::PROGRAMADO) {
            return false;
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE solicitud_traslado SET ci_paciente = ?, id_tipo_elemento = ?, id_origen = ?, id_destino = ?, id_canal = ?, motivo = ?
                 WHERE id_solicitud = ?"
            );
            $stmt->bind_param('siiiisi', $d['ci_paciente'], $d['tipo_elemento'], $d['origen'], $d['destino'], $d['canal'], $d['motivo'], $actual['id_solicitud']);
            $stmt->execute();

            $stmt = $this->db->prepare(
                "UPDATE traslado SET id_vehiculo = ?, id_chofer = ?, id_enfermero = ?, id_proveedor = ?, id_ruta = ?, hora_salida_prev = ?, hora_llegada_prev = ?
                 WHERE id_traslado = ?"
            );
            $stmt->bind_param('iiiiissi', $d['vehiculo'], $d['chofer'], $d['enfermero'], $d['proveedor'], $d['ruta'], $d['salida'], $d['llegada'], $idTraslado);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function registrarEstado(int $idTraslado, int $idEstado, int $idUsuario, ?string $observaciones): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO historial_estado (id_traslado, id_estado, id_usuario, observaciones) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiis', $idTraslado, $idEstado, $idUsuario, $observaciones);
        $stmt->execute();
    }

    // Pasa al estado siguiente y guarda la hora real que corresponde.
    // $estadoVisto: el estado que tenia la pantalla; si cambio mientras tanto (otra persona, doble clic), no se hace nada.
    public function avanzar(int $idTraslado, int $estadoVisto, int $idUsuario, ?string $observaciones): bool
    {
        $actual = $this->obtener($idTraslado);
        if (!$actual || (int) $actual['id_estado'] !== $estadoVisto || !isset(self::SIGUIENTE[$estadoVisto])) {
            return false;
        }
        $nuevo = self::SIGUIENTE[$estadoVisto];

        $columna = [self::EN_VIAJE => 'hora_salida_real', self::EN_DESTINO => 'hora_llegada_real', self::FINALIZADO => 'hora_retorno'][$nuevo] ?? null;

        $this->db->begin_transaction();
        try {
            if ($columna) {
                $stmt = $this->db->prepare("UPDATE traslado SET $columna = NOW() WHERE id_traslado = ?");
                $stmt->bind_param('i', $idTraslado);
                $stmt->execute();
            }
            $this->registrarEstado($idTraslado, $nuevo, $idUsuario, $observaciones);
            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Solo se cancela antes de salir; la solicitud queda anulada
    public function cancelar(int $idTraslado, int $idUsuario, string $motivo): bool
    {
        $actual = $this->obtener($idTraslado);
        if (!$actual || (int) $actual['id_estado'] !== self::PROGRAMADO) {
            return false;
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("UPDATE solicitud_traslado SET estado_solicitud = 'anulada' WHERE id_solicitud = ?");
            $stmt->bind_param('i', $actual['id_solicitud']);
            $stmt->execute();
            $this->registrarEstado($idTraslado, self::CANCELADO, $idUsuario, $motivo);
            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
