<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';

class DocumentoModelo
{
    // Carpeta donde se guardan los PDF subidos (con nombres aleatorios)
    public const CARPETA_ARCHIVOS = __DIR__ . '/../archivos/';
    public const TAMANO_MAXIMO = 10 * 1024 * 1024; // 10 MB

    private mysqli $db;

    // Revisa el PDF subido. Devuelve el mensaje de error, o null si esta bien.
    // Si no es obligatorio (al editar) y no se eligio archivo, tambien devuelve null.
    public static function validarPdf(?array $archivo, bool $obligatorio): ?string
    {
        if (!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return $obligatorio ? 'Elegí el archivo PDF.' : null;
        }
        if (in_array($archivo['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            || $archivo['size'] > self::TAMANO_MAXIMO) {
            return 'El archivo es demasiado grande. El máximo es 10 MB.';
        }
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return 'No se pudo subir el archivo. Probá de nuevo.';
        }

        // Que sea un PDF de verdad: por el contenido, no por la extension
        $tipo   = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $inicio = file_get_contents($archivo['tmp_name'], false, null, 0, 5);

        return ($tipo !== 'application/pdf' || $inicio !== '%PDF-') ? 'El archivo tiene que ser un PDF.' : null;
    }

    // Mueve el PDF a la carpeta de archivos con un nombre aleatorio. Devuelve el nombre o null si falla.
    public static function guardarPdf(string $rutaTemporal): ?string
    {
        // Nunca se usa el nombre que manda el usuario
        $nombre  = bin2hex(random_bytes(16)) . '.pdf';
        $destino = self::CARPETA_ARCHIVOS . $nombre;

        if (!is_dir(self::CARPETA_ARCHIVOS) || !move_uploaded_file($rutaTemporal, $destino)) {
            error_log('No se pudo mover el PDF a ' . $destino);
            return null;
        }
        return $nombre;
    }

    public static function borrarPdf(string $nombre): void
    {
        $ruta = self::CARPETA_ARCHIVOS . basename($nombre);
        if (is_file($ruta)) {
            unlink($ruta);
        }
    }

    public function __construct()
    {
        $this->db = conectarBD();
    }

    // Categorias de tipo documental con la cantidad de documentos de cada una
    public function listarCategorias(): array
    {
        $sql = "SELECT c.id_categoria, c.nombre, c.codigo_qr,
                       COUNT(d.id_documento) AS total,
                       COALESCE(SUM(d.activo = 1), 0) AS activos
                FROM categoria c
                LEFT JOIN documento d ON d.id_categoria = c.id_categoria
                WHERE c.tipo_categoria = 'documental'
                GROUP BY c.id_categoria, c.nombre, c.codigo_qr
                ORDER BY c.nombre";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // Documentos para el panel del funcionario (activos e inactivos), filtrados por categoria y busqueda
    public function listarDocumentos(int $idCategoria, string $busqueda): array
    {
        $sql = "SELECT d.id_documento, d.titulo, d.descripcion, d.archivo_url, d.fecha_carga, d.activo,
                       c.nombre AS categoria
                FROM documento d
                INNER JOIN categoria c ON c.id_categoria = d.id_categoria
                WHERE c.tipo_categoria = 'documental'
                  AND (? = 0 OR d.id_categoria = ?)
                  AND d.titulo LIKE ?
                ORDER BY d.fecha_carga DESC, d.id_documento DESC";

        $patron = '%' . $busqueda . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iis', $idCategoria, $idCategoria, $patron);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function existeCategoriaDocumental(int $idCategoria): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM categoria WHERE id_categoria = ? AND tipo_categoria = 'documental'");
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    public function crear(string $titulo, ?string $descripcion, string $archivo, int $idCategoria, int $idUsuario, bool $activo): int
    {
        $sql = "INSERT INTO documento (titulo, descripcion, archivo_url, id_categoria, id_usuario_carga, activo)
                VALUES (?, ?, ?, ?, ?, ?)";

        $activoNum = $activo ? 1 : 0;
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssiii', $titulo, $descripcion, $archivo, $idCategoria, $idUsuario, $activoNum);
        $stmt->execute();

        return $this->db->insert_id;
    }

    // Documento completo para el formulario de edicion (activo o no)
    public function obtener(int $idDocumento): ?array
    {
        $sql = "SELECT d.id_documento, d.titulo, d.descripcion, d.archivo_url, d.fecha_carga, d.id_categoria, d.activo,
                       c.nombre AS categoria
                FROM documento d
                INNER JOIN categoria c ON c.id_categoria = d.id_categoria
                WHERE d.id_documento = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idDocumento);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    // Si $archivo es null se mantiene el PDF que ya tenia
    public function actualizar(int $idDocumento, string $titulo, ?string $descripcion, int $idCategoria, bool $activo, ?string $archivo): void
    {
        $activoNum = $activo ? 1 : 0;

        if ($archivo === null) {
            $stmt = $this->db->prepare(
                "UPDATE documento SET titulo = ?, descripcion = ?, id_categoria = ?, activo = ? WHERE id_documento = ?"
            );
            $stmt->bind_param('ssiii', $titulo, $descripcion, $idCategoria, $activoNum, $idDocumento);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE documento SET titulo = ?, descripcion = ?, id_categoria = ?, activo = ?, archivo_url = ? WHERE id_documento = ?"
            );
            $stmt->bind_param('ssiisi', $titulo, $descripcion, $idCategoria, $activoNum, $archivo, $idDocumento);
        }

        $stmt->execute();
    }

    // Borra el registro; el controlador despues borra el PDF del disco
    public function eliminar(int $idDocumento): bool
    {
        $stmt = $this->db->prepare("DELETE FROM documento WHERE id_documento = ?");
        $stmt->bind_param('i', $idDocumento);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }

    // Activa o desactiva un documento
    public function cambiarEstado(int $idDocumento): bool
    {
        $stmt = $this->db->prepare("UPDATE documento SET activo = 1 - activo WHERE id_documento = ?");
        $stmt->bind_param('i', $idDocumento);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }

    // Solo devuelve el documento si esta activo (lo que puede ver el paciente)
    public function obtenerActivo(int $idDocumento): ?array
    {
        $stmt = $this->db->prepare("SELECT id_documento, titulo, archivo_url FROM documento WHERE id_documento = ? AND activo = 1");
        $stmt->bind_param('i', $idDocumento);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function buscarCategoriaPorQr(string $codigoQr): ?array
    {
        $stmt = $this->db->prepare("SELECT id_categoria, nombre, descripcion, tipo_categoria FROM categoria WHERE codigo_qr = ?");
        $stmt->bind_param('s', $codigoQr);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function documentosActivosDeCategoria(int $idCategoria): array
    {
        $sql = "SELECT id_documento, titulo, descripcion, archivo_url
                FROM documento
                WHERE id_categoria = ? AND activo = 1
                ORDER BY titulo";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
