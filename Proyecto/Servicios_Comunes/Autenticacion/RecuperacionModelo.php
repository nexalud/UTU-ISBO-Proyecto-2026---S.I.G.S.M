<?php
require_once __DIR__ . '/../Conexion_BD/conexion.php';

// Codigos de un solo uso para crear o restablecer la contraseña.
// Al usuario le llega el codigo en el enlace; en la base solo queda su hash.
class RecuperacionModelo
{
    public const MINUTOS_RESTABLECER = 30;
    public const MINUTOS_BIENVENIDA  = 48 * 60;

    private mysqli $db;

    public function __construct()
    {
        $this->db = conectarBD();
    }

    // Crea un codigo nuevo y devuelve el codigo en texto (para el enlace)
    public function crear(int $idUsuario, string $tipo): string
    {
        $codigo  = bin2hex(random_bytes(32));
        $hash    = hash('sha256', $codigo);
        $minutos = $tipo === 'bienvenida' ? self::MINUTOS_BIENVENIDA : self::MINUTOS_RESTABLECER;

        // Un codigo nuevo anula los anteriores que no se usaron
        $this->anularPendientes($idUsuario);

        $stmt = $this->db->prepare(
            "INSERT INTO recuperacion_contrasena (id_usuario, token_hash, tipo, fecha_expira)
             VALUES (?, ?, ?, NOW() + INTERVAL ? MINUTE)"
        );
        $stmt->bind_param('issi', $idUsuario, $hash, $tipo, $minutos);
        $stmt->execute();

        return $codigo;
    }

    // Devuelve el codigo con los datos del usuario si existe, no vencio, no se uso y el usuario esta activo
    public function buscarValido(string $codigo): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $codigo)) {
            return null;
        }
        $hash = hash('sha256', $codigo);

        $sql = "SELECT r.id_recuperacion, r.tipo, u.id_usuario, u.nombre_usuario, u.nombre
                FROM recuperacion_contrasena r
                INNER JOIN usuario u ON u.id_usuario = r.id_usuario
                WHERE r.token_hash = ? AND r.fecha_uso IS NULL AND r.fecha_expira > NOW() AND u.activo = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $hash);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function marcarUsado(int $idRecuperacion): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE recuperacion_contrasena SET fecha_uso = NOW() WHERE id_recuperacion = ? AND fecha_uso IS NULL"
        );
        $stmt->bind_param('i', $idRecuperacion);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }

    public function anularPendientes(int $idUsuario): void
    {
        $stmt = $this->db->prepare(
            "UPDATE recuperacion_contrasena SET fecha_uso = NOW() WHERE id_usuario = ? AND fecha_uso IS NULL"
        );
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
    }

    // Cuantos pedidos de "olvide mi contraseña" hizo en la ultima hora (para frenar abusos)
    public function pedidosRecientes(int $idUsuario): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM recuperacion_contrasena
             WHERE id_usuario = ? AND tipo = 'restablecer' AND fecha_creacion > NOW() - INTERVAL 1 HOUR"
        );
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_row()[0];
    }
}
