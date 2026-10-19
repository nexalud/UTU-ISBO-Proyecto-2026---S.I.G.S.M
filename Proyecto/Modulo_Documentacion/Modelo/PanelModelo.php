<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';

// Numeros para la pantalla de inicio del panel (solo lectura)
class PanelModelo
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = conectarBD();
    }

    private function numero(string $sql): int
    {
        return (int) $this->db->query($sql)->fetch_row()[0];
    }

    public function resumen(): array
    {
        $abiertas = "(e.fecha_inicio IS NULL OR e.fecha_inicio <= CURDATE()) AND (e.fecha_fin IS NULL OR e.fecha_fin >= CURDATE())";

        return [
            'documentos'          => $this->numero("SELECT COUNT(*) FROM documento"),
            'documentos_activos'  => $this->numero("SELECT COUNT(*) FROM documento WHERE activo = 1"),
            'categorias_docs'     => $this->numero("SELECT COUNT(*) FROM categoria WHERE tipo_categoria = 'documental'"),
            'categorias_encuesta' => $this->numero("SELECT COUNT(*) FROM categoria WHERE tipo_categoria = 'encuesta'"),
            'encuestas_abiertas'  => $this->numero("SELECT COUNT(*) FROM encuesta e WHERE $abiertas"),
            'encuestas'           => $this->numero("SELECT COUNT(*) FROM encuesta"),
            'respuestas_mes'      => $this->numero(
                "SELECT COUNT(*) FROM respuesta_encuesta WHERE fecha_envio >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
            ),
            'respuestas_mes_anterior' => $this->numero(
                "SELECT COUNT(*) FROM respuesta_encuesta
                 WHERE fecha_envio >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
                   AND fecha_envio <  DATE_FORMAT(CURDATE(), '%Y-%m-01')"
            ),
        ];
    }

    // Promedio de la primera pregunta de escala de cada encuesta, sobre todas las respuestas.
    // La opcion de escala vale 1..5 segun su posicion (igual que en Resultados).
    public function calificacionGeneral(): ?float
    {
        $sql = "SELECT AVG(o_pos.valor)
                FROM respuesta_pregunta rp
                INNER JOIN (
                    SELECT o.id_opcion,
                           (SELECT COUNT(*) FROM opcion_respuesta o2
                            WHERE o2.id_pregunta = o.id_pregunta AND o2.id_opcion <= o.id_opcion) AS valor
                    FROM opcion_respuesta o
                    INNER JOIN pregunta p ON p.id_pregunta = o.id_pregunta
                    WHERE p.tipo_pregunta = 'escala'
                      AND p.orden = (SELECT MIN(p2.orden) FROM pregunta p2
                                     WHERE p2.id_encuesta = p.id_encuesta AND p2.tipo_pregunta = 'escala')
                ) AS o_pos ON o_pos.id_opcion = rp.id_opcion";

        $valor = $this->db->query($sql)->fetch_row()[0];
        return $valor === null ? null : (float) $valor;
    }

    public function ultimosDocumentos(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id_documento, d.titulo, d.fecha_carga, d.activo, c.nombre AS categoria
             FROM documento d
             INNER JOIN categoria c ON c.id_categoria = d.id_categoria
             ORDER BY d.fecha_carga DESC, d.id_documento DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limite);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Encuestas abiertas hoy con sus respuestas totales y de los ultimos 7 dias
    public function encuestasAbiertas(): array
    {
        $sql = "SELECT e.id_encuesta, e.titulo, e.segmento, c.nombre AS categoria,
                       (SELECT COUNT(*) FROM respuesta_encuesta r WHERE r.id_encuesta = e.id_encuesta) AS respuestas,
                       (SELECT COUNT(*) FROM respuesta_encuesta r
                        WHERE r.id_encuesta = e.id_encuesta AND r.fecha_envio >= CURDATE() - INTERVAL 6 DAY) AS semana
                FROM encuesta e
                LEFT JOIN categoria c ON c.id_categoria = e.id_categoria
                WHERE (e.fecha_inicio IS NULL OR e.fecha_inicio <= CURDATE())
                  AND (e.fecha_fin IS NULL OR e.fecha_fin >= CURDATE())
                ORDER BY respuestas DESC";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
