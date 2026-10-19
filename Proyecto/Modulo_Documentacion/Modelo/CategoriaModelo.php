<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';

// Categorias: cada una tiene su propio QR.
// Las de tipo 'documental' agrupan documentos; las de tipo 'encuesta' llevan a una encuesta.
class CategoriaModelo
{
    public const TIPOS = [
        'documental' => 'Documentos',
        'encuesta'   => 'Encuesta',
    ];

    private mysqli $db;

    public function __construct()
    {
        $this->db = conectarBD();
    }

    // Todas las categorias con cuantos documentos y encuestas tiene cada una
    public function listar(): array
    {
        $sql = "SELECT c.id_categoria, c.nombre, c.descripcion, c.codigo_qr, c.tipo_categoria,
                       (SELECT COUNT(*) FROM documento d WHERE d.id_categoria = c.id_categoria) AS documentos,
                       (SELECT COUNT(*) FROM documento d WHERE d.id_categoria = c.id_categoria AND d.activo = 1) AS documentos_activos,
                       (SELECT COUNT(*) FROM encuesta e WHERE e.id_categoria = c.id_categoria) AS encuestas
                FROM categoria c
                ORDER BY c.tipo_categoria, c.nombre";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener(int $idCategoria): ?array
    {
        $sql = "SELECT c.id_categoria, c.nombre, c.descripcion, c.codigo_qr, c.tipo_categoria,
                       (SELECT COUNT(*) FROM documento d WHERE d.id_categoria = c.id_categoria) AS documentos,
                       (SELECT COUNT(*) FROM encuesta e WHERE e.id_categoria = c.id_categoria) AS encuestas
                FROM categoria c
                WHERE c.id_categoria = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    // Para no repetir nombres (la base tambien lo impide con UNIQUE)
    public function existeNombre(string $nombre, int $excepto = 0): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM categoria WHERE nombre = ? AND id_categoria <> ?");
        $stmt->bind_param('si', $nombre, $excepto);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // Crea la categoria y le asigna su codigo de QR: QR-CAT-0006, QR-CAT-0007...
    public function crear(string $nombre, ?string $descripcion, string $tipo): int
    {
        $this->db->begin_transaction();

        try {
            // Codigo temporal unico hasta saber el id
            $temporal = 'TMP-' . bin2hex(random_bytes(8));
            $stmt = $this->db->prepare(
                "INSERT INTO categoria (nombre, descripcion, codigo_qr, tipo_categoria) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('ssss', $nombre, $descripcion, $temporal, $tipo);
            $stmt->execute();
            $id = $this->db->insert_id;

            $codigo = sprintf('QR-CAT-%04d', $id);
            $stmt = $this->db->prepare("UPDATE categoria SET codigo_qr = ? WHERE id_categoria = ?");
            $stmt->bind_param('si', $codigo, $id);
            $stmt->execute();

            $this->db->commit();
            return $id;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // El codigo del QR nunca cambia: asi los QR ya impresos siguen funcionando
    public function actualizar(int $idCategoria, string $nombre, ?string $descripcion, string $tipo): void
    {
        $stmt = $this->db->prepare(
            "UPDATE categoria SET nombre = ?, descripcion = ?, tipo_categoria = ? WHERE id_categoria = ?"
        );
        $stmt->bind_param('sssi', $nombre, $descripcion, $tipo, $idCategoria);
        $stmt->execute();
    }

    // Solo se puede borrar si no tiene documentos ni encuestas
    public function eliminar(int $idCategoria): bool
    {
        $sql = "DELETE FROM categoria
                WHERE id_categoria = ?
                  AND NOT EXISTS (SELECT 1 FROM documento d WHERE d.id_categoria = categoria.id_categoria)
                  AND NOT EXISTS (SELECT 1 FROM encuesta e WHERE e.id_categoria = categoria.id_categoria)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }
}
