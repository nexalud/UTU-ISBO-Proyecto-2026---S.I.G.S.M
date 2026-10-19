<?php
require_once __DIR__ . '/EncuestaModelo.php';

// Alta, edicion y baja de encuestas desde el panel.
// Hereda obtenerPreguntas() de EncuestaModelo.
class GestionEncuestaModelo extends EncuestaModelo
{
    public const TIPOS_PREGUNTA = [
        'escala'       => 'Escala del 1 al 5',
        'opcion_unica' => 'Opción única',
        'abierta'      => 'Respuesta libre',
    ];

    // Las preguntas de escala siempre tienen estas 5 opciones (la 1 es la peor)
    public const OPCIONES_ESCALA = ['1 - Muy mala', '2 - Mala', '3 - Regular', '4 - Buena', '5 - Muy buena'];

    // Abierta / Programada / Cerrada segun las fechas de hoy
    public static function estado(?string $inicio, ?string $fin): string
    {
        $hoy = date('Y-m-d');
        if ($inicio && $inicio > $hoy) {
            return 'Programada';
        }
        if ($fin && $fin < $hoy) {
            return 'Cerrada';
        }
        return 'Abierta';
    }

    // Encuestas con su categoria, cantidad de preguntas y de respuestas
    public function listar(): array
    {
        $sql = "SELECT e.id_encuesta, e.titulo, e.descripcion, e.segmento, e.fecha_inicio, e.fecha_fin, e.id_categoria,
                       c.nombre AS categoria, c.codigo_qr,
                       (SELECT COUNT(*) FROM pregunta p WHERE p.id_encuesta = e.id_encuesta) AS preguntas,
                       (SELECT COUNT(*) FROM respuesta_encuesta r WHERE r.id_encuesta = e.id_encuesta) AS respuestas
                FROM encuesta e
                LEFT JOIN categoria c ON c.id_categoria = e.id_categoria
                ORDER BY e.id_encuesta DESC";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener(int $idEncuesta): ?array
    {
        $sql = "SELECT e.id_encuesta, e.titulo, e.descripcion, e.segmento, e.fecha_inicio, e.fecha_fin, e.id_categoria,
                       (SELECT COUNT(*) FROM respuesta_encuesta r WHERE r.id_encuesta = e.id_encuesta) AS respuestas
                FROM encuesta e
                WHERE e.id_encuesta = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    // Categorias de tipo encuesta (las que pueden tener una encuesta asociada)
    public function listarCategorias(): array
    {
        $sql = "SELECT id_categoria, nombre, codigo_qr FROM categoria WHERE tipo_categoria = 'encuesta' ORDER BY nombre";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function existeCategoria(int $idCategoria): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM categoria WHERE id_categoria = ? AND tipo_categoria = 'encuesta'");
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // $datos: titulo, descripcion, segmento, fecha_inicio, fecha_fin, categoria
    // $preguntas: lista de ['texto', 'tipo', 'obligatoria', 'opciones' => [...]]
    public function crear(array $datos, array $preguntas): int
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO encuesta (id_categoria, titulo, descripcion, fecha_inicio, fecha_fin, segmento)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('isssss', $datos['categoria'], $datos['titulo'], $datos['descripcion'],
                $datos['fecha_inicio'], $datos['fecha_fin'], $datos['segmento']);
            $stmt->execute();
            $idEncuesta = $this->db->insert_id;

            $this->insertarPreguntas($idEncuesta, $preguntas);

            $this->db->commit();
            return $idEncuesta;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Si $preguntas es null, solo se cambian los datos generales (pasa cuando ya hay respuestas)
    public function actualizar(int $idEncuesta, array $datos, ?array $preguntas): void
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare(
                "UPDATE encuesta
                 SET id_categoria = ?, titulo = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, segmento = ?
                 WHERE id_encuesta = ?"
            );
            $stmt->bind_param('isssssi', $datos['categoria'], $datos['titulo'], $datos['descripcion'],
                $datos['fecha_inicio'], $datos['fecha_fin'], $datos['segmento'], $idEncuesta);
            $stmt->execute();

            if ($preguntas !== null) {
                // Sin respuestas no se pierde nada: se borran las preguntas (y sus opciones) y se cargan de nuevo
                $stmt = $this->db->prepare("DELETE FROM pregunta WHERE id_encuesta = ?");
                $stmt->bind_param('i', $idEncuesta);
                $stmt->execute();

                $this->insertarPreguntas($idEncuesta, $preguntas);
            }

            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function insertarPreguntas(int $idEncuesta, array $preguntas): void
    {
        $stmtPregunta = $this->db->prepare(
            "INSERT INTO pregunta (id_encuesta, texto_pregunta, tipo_pregunta, orden, obligatoria) VALUES (?, ?, ?, ?, ?)"
        );
        $stmtOpcion = $this->db->prepare("INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (?, ?)");

        foreach (array_values($preguntas) as $n => $p) {
            $orden       = $n + 1;
            $obligatoria = $p['obligatoria'] ? 1 : 0;
            $stmtPregunta->bind_param('issii', $idEncuesta, $p['texto'], $p['tipo'], $orden, $obligatoria);
            $stmtPregunta->execute();
            $idPregunta = $this->db->insert_id;

            $opciones = $p['tipo'] === 'escala' ? self::OPCIONES_ESCALA : ($p['tipo'] === 'opcion_unica' ? $p['opciones'] : []);
            foreach ($opciones as $texto) {
                $stmtOpcion->bind_param('is', $idPregunta, $texto);
                $stmtOpcion->execute();
            }
        }
    }

    // Cierra la encuesta desde hoy: el QR deja de mostrarla, pero las respuestas quedan
    public function cerrar(int $idEncuesta): bool
    {
        $sql = "UPDATE encuesta
                SET fecha_fin = CURDATE() - INTERVAL 1 DAY,
                    fecha_inicio = LEAST(COALESCE(fecha_inicio, CURDATE() - INTERVAL 1 DAY), CURDATE() - INTERVAL 1 DAY)
                WHERE id_encuesta = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }

    // Solo se borra si nadie respondio (las respuestas nunca se pierden)
    public function eliminar(int $idEncuesta): bool
    {
        $sql = "DELETE FROM encuesta
                WHERE id_encuesta = ?
                  AND NOT EXISTS (SELECT 1 FROM respuesta_encuesta r WHERE r.id_encuesta = encuesta.id_encuesta)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }
}
