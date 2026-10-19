<?php
require_once __DIR__ . '/../../Servicios_Comunes/Conexion_BD/conexion.php';

class EncuestaModelo
{
    protected mysqli $db;

    public function __construct()
    {
        $this->db = conectarBD();
    }

    // Busca la encuesta vigente asociada al QR de una categoria de tipo 'encuesta'
    public function buscarPorQr(string $codigoQr): ?array
    {
        $sql = "SELECT e.id_encuesta, e.titulo, e.descripcion, c.nombre AS categoria
                FROM encuesta e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria
                WHERE c.codigo_qr = ?
                  AND c.tipo_categoria = 'encuesta'
                  AND (e.fecha_inicio IS NULL OR e.fecha_inicio <= CURDATE())
                  AND (e.fecha_fin IS NULL OR e.fecha_fin >= CURDATE())
                ORDER BY e.id_encuesta DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $codigoQr);
        $stmt->execute();
        $encuesta = $stmt->get_result()->fetch_assoc();

        return $encuesta ?: null;
    }

    // Devuelve las preguntas de la encuesta, en orden, cada una con sus opciones
    public function obtenerPreguntas(int $idEncuesta): array
    {
        $sql = "SELECT id_pregunta, texto_pregunta, tipo_pregunta, obligatoria
                FROM pregunta
                WHERE id_encuesta = ?
                ORDER BY orden, id_pregunta";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $preguntas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $fila['id_pregunta'] = (int) $fila['id_pregunta'];
            $fila['obligatoria'] = (bool) $fila['obligatoria'];
            $fila['opciones'] = [];
            $preguntas[$fila['id_pregunta']] = $fila;
        }

        if (!$preguntas) {
            return [];
        }

        $sql = "SELECT o.id_opcion, o.id_pregunta, o.texto_opcion
                FROM opcion_respuesta o
                INNER JOIN pregunta p ON p.id_pregunta = o.id_pregunta
                WHERE p.id_encuesta = ?
                ORDER BY o.id_opcion";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $idPregunta = (int) $fila['id_pregunta'];
            $preguntas[$idPregunta]['opciones'][] = [
                'id_opcion' => (int) $fila['id_opcion'],
                'texto'     => $fila['texto_opcion'],
            ];
        }

        return array_values($preguntas);
    }

    // Guarda una respuesta completa (cabecera + una fila por pregunta) en una sola transaccion.
    // No se guarda ningun dato del paciente: la encuesta es anonima.
    public function guardarRespuesta(int $idEncuesta, array $respuestas): void
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare("INSERT INTO respuesta_encuesta (id_encuesta) VALUES (?)");
            $stmt->bind_param('i', $idEncuesta);
            $stmt->execute();
            $idRespEncuesta = $this->db->insert_id;

            $stmt = $this->db->prepare(
                "INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre)
                 VALUES (?, ?, ?, ?)"
            );

            foreach ($respuestas as $r) {
                $stmt->bind_param('iiis', $idRespEncuesta, $r['id_pregunta'], $r['id_opcion'], $r['texto_libre']);
                $stmt->execute();
            }

            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
