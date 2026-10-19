<?php
require_once __DIR__ . '/../Conexion_BD/conexion.php';

class UsuarioModelo
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = conectarBD();
    }

    // Consulta base: usuario con sus roles (lista de nombres separada por comas)
    private const SELECT_USUARIO = "SELECT u.id_usuario, u.nombre_usuario, u.contrasenha_hash, u.nombre, u.apellido, u.email, u.activo,
                                           GROUP_CONCAT(r.nombre_rol ORDER BY r.id_rol) AS roles
                                    FROM usuario u
                                    LEFT JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
                                    LEFT JOIN rol r ON r.id_rol = ur.id_rol";

    private function conRoles(?array $usuario): ?array
    {
        if (!$usuario) {
            return null;
        }
        $usuario['roles'] = $usuario['roles'] ? explode(',', $usuario['roles']) : [];
        return $usuario;
    }

    private function buscarUno(string $condicion, string $tipos, ...$valores): ?array
    {
        $stmt = $this->db->prepare(self::SELECT_USUARIO . " WHERE $condicion GROUP BY u.id_usuario");
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();

        return $this->conRoles($stmt->get_result()->fetch_assoc());
    }

    // Devuelve el usuario con sus roles, o null si no existe
    public function buscarPorNombre(string $nombreUsuario): ?array
    {
        return $this->buscarUno('u.nombre_usuario = ?', 's', $nombreUsuario);
    }

    public function buscarPorId(int $idUsuario): ?array
    {
        return $this->buscarUno('u.id_usuario = ?', 'i', $idUsuario);
    }

    // Para "olvide mi contraseña": se puede escribir el usuario o el correo
    public function buscarPorNombreOEmail(string $texto): ?array
    {
        return $this->buscarUno('(u.nombre_usuario = ? OR u.email = ?)', 'ss', $texto, $texto);
    }

    public function cambiarContrasena(int $idUsuario, string $hash): void
    {
        $stmt = $this->db->prepare("UPDATE usuario SET contrasenha_hash = ? WHERE id_usuario = ?");
        $stmt->bind_param('si', $hash, $idUsuario);
        $stmt->execute();
    }

    // ---------- Administracion de usuarios ----------

    public function listar(): array
    {
        $resultado = $this->db->query(self::SELECT_USUARIO . " GROUP BY u.id_usuario ORDER BY u.activo DESC, u.apellido, u.nombre");

        return array_map([$this, 'conRoles'], $resultado->fetch_all(MYSQLI_ASSOC));
    }

    public function listarRoles(): array
    {
        return $this->db->query("SELECT id_rol, nombre_rol, descripcion FROM rol ORDER BY id_rol")->fetch_all(MYSQLI_ASSOC);
    }

    public function existeNombre(string $nombreUsuario, int $excepto = 0): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM usuario WHERE nombre_usuario = ? AND id_usuario <> ?");
        $stmt->bind_param('si', $nombreUsuario, $excepto);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    public function existeEmail(string $email, int $excepto = 0): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM usuario WHERE email = ? AND id_usuario <> ?");
        $stmt->bind_param('si', $email, $excepto);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // Crea el usuario sin una contraseña que sirva: la elige el mismo con el enlace que le llega por correo
    public function crear(array $datos, array $idsRoles): int
    {
        $this->db->begin_transaction();

        try {
            $hashInutil = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                "INSERT INTO usuario (nombre_usuario, contrasenha_hash, nombre, apellido, email, activo) VALUES (?, ?, ?, ?, ?, 1)"
            );
            $stmt->bind_param('sssss', $datos['nombre_usuario'], $hashInutil, $datos['nombre'], $datos['apellido'], $datos['email']);
            $stmt->execute();
            $idUsuario = $this->db->insert_id;

            $this->guardarRoles($idUsuario, $idsRoles);

            $this->db->commit();
            return $idUsuario;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function actualizar(int $idUsuario, array $datos, array $idsRoles): void
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare(
                "UPDATE usuario SET nombre_usuario = ?, nombre = ?, apellido = ?, email = ? WHERE id_usuario = ?"
            );
            $stmt->bind_param('ssssi', $datos['nombre_usuario'], $datos['nombre'], $datos['apellido'], $datos['email'], $idUsuario);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM usuario_rol WHERE id_usuario = ?");
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();

            $this->guardarRoles($idUsuario, $idsRoles);

            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function guardarRoles(int $idUsuario, array $idsRoles): void
    {
        $stmt = $this->db->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (?, ?)");
        foreach (array_unique($idsRoles) as $idRol) {
            $idRol = (int) $idRol;
            $stmt->bind_param('ii', $idUsuario, $idRol);
            $stmt->execute();
        }
    }

    // Los usuarios no se borran (quedan sus documentos cargados): se desactivan
    public function cambiarEstado(int $idUsuario): void
    {
        $stmt = $this->db->prepare("UPDATE usuario SET activo = 1 - activo WHERE id_usuario = ?");
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
    }

    // Cuantos administradores activos quedan (para no dejar el sistema sin ninguno)
    public function contarAdministradoresActivos(int $excepto = 0): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT u.id_usuario)
             FROM usuario u
             INNER JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
             INNER JOIN rol r ON r.id_rol = ur.id_rol
             WHERE r.nombre_rol = 'administrador' AND u.activo = 1 AND u.id_usuario <> ?"
        );
        $stmt->bind_param('i', $excepto);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_row()[0];
    }
}
