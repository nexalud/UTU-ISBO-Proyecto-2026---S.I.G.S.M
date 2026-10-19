<?php
require_once __DIR__ . '/EncuestaModelo.php';

// Consultas para el panel de resultados (solo lectura).
// Hereda obtenerPreguntas() de EncuestaModelo.
class ResultadosModelo extends EncuestaModelo
{
    public function listarEncuestas(): array
    {
        $sql = "SELECT e.id_encuesta, e.titulo, e.segmento, e.fecha_inicio, e.fecha_fin,
                       c.nombre AS categoria, c.codigo_qr
                FROM encuesta e
                LEFT JOIN categoria c ON c.id_categoria = e.id_categoria
                ORDER BY e.id_encuesta";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // Cantidad de personas que respondieron en el periodo [desde, hasta)
    public function contarRespuestas(int $idEncuesta, string $desde, string $hasta): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM respuesta_encuesta WHERE id_encuesta = ? AND fecha_envio >= ? AND fecha_envio < ?"
        );
        $stmt->bind_param('iss', $idEncuesta, $desde, $hasta);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_row()[0];
    }

    // Cuantas veces se eligio cada opcion en el periodo: [id_opcion => cantidad]
    public function contarOpciones(int $idEncuesta, string $desde, string $hasta): array
    {
        $sql = "SELECT rp.id_opcion, COUNT(*) AS cantidad
                FROM respuesta_pregunta rp
                INNER JOIN respuesta_encuesta r ON r.id_resp_encuesta = rp.id_resp_encuesta
                WHERE r.id_encuesta = ?
                  AND r.fecha_envio >= ? AND r.fecha_envio < ?
                  AND rp.id_opcion IS NOT NULL
                GROUP BY rp.id_opcion";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $idEncuesta, $desde, $hasta);
        $stmt->execute();

        $conteo = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $fila) {
            $conteo[(int) $fila['id_opcion']] = (int) $fila['cantidad'];
        }
        return $conteo;
    }

    // Comentarios de las preguntas abiertas, los mas nuevos primero.
    // Junto a cada uno viene la opcion que esa misma persona eligio en la primera pregunta de escala.
    public function comentarios(int $idEncuesta, string $desde, string $hasta, int $limite = 30): array
    {
        $sql = "SELECT rp.id_pregunta, rp.texto_libre, r.fecha_envio,
                       (SELECT rp2.id_opcion
                        FROM respuesta_pregunta rp2
                        INNER JOIN pregunta p2 ON p2.id_pregunta = rp2.id_pregunta
                        WHERE rp2.id_resp_encuesta = r.id_resp_encuesta AND p2.tipo_pregunta = 'escala'
                        ORDER BY p2.orden
                        LIMIT 1) AS id_opcion_escala
                FROM respuesta_pregunta rp
                INNER JOIN respuesta_encuesta r ON r.id_resp_encuesta = rp.id_resp_encuesta
                INNER JOIN pregunta p ON p.id_pregunta = rp.id_pregunta
                WHERE r.id_encuesta = ?
                  AND r.fecha_envio >= ? AND r.fecha_envio < ?
                  AND p.tipo_pregunta = 'abierta'
                  AND rp.texto_libre IS NOT NULL AND rp.texto_libre <> ''
                ORDER BY r.fecha_envio DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('issi', $idEncuesta, $desde, $hasta, $limite);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function contarComentarios(int $idEncuesta, string $desde, string $hasta): int
    {
        $sql = "SELECT COUNT(*)
                FROM respuesta_pregunta rp
                INNER JOIN respuesta_encuesta r ON r.id_resp_encuesta = rp.id_resp_encuesta
                INNER JOIN pregunta p ON p.id_pregunta = rp.id_pregunta
                WHERE r.id_encuesta = ?
                  AND r.fecha_envio >= ? AND r.fecha_envio < ?
                  AND p.tipo_pregunta = 'abierta'
                  AND rp.texto_libre IS NOT NULL AND rp.texto_libre <> ''";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $idEncuesta, $desde, $hasta);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_row()[0];
    }
}
