-- =====================================================================
-- S.I.G.S.M. - Datos de prueba para la demo (no van en producción)
-- Correr UNA sola vez, después de sigsm.sql.
--   1) Respuestas de encuestas, para que Resultados tenga números.
--   2) Traslados de ejemplo (en viaje, programado, finalizados y cancelado),
--      con horarios relativos a "ahora" para que siempre se vean actuales.
-- =====================================================================

SET NAMES utf8mb4;
START TRANSACTION;

-- ---------------------------------------------------------------------
-- Respuestas de encuestas
-- ---------------------------------------------------------------------

-- Encuesta de satisfacción (id 1): pregunta 1 = opciones 1 a 5, pregunta 2 = opciones 6 a 8, pregunta 3 = abierta
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-03 14:26:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-04 10:18:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 3, NULL), (@r, 2, 6, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-05 16:13:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 4, NULL), (@r, 2, 8, NULL), (@r, 3, NULL, 'Todo muy claro y rápido.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-06 09:35:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 7, NULL), (@r, 3, NULL, 'El baño de la sala no estaba muy limpio.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-08 19:04:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'La enfermera de la noche fue muy atenta.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-20 10:44:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-24 15:56:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 2, NULL), (@r, 2, 7, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-26 10:25:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 7, NULL), (@r, 3, NULL, 'Me sentí bien acompañado durante el ingreso.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-08-28 15:18:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-03 16:07:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 4, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Faltaría más información sobre los horarios de visita.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-03 17:07:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Me sentí bien acompañado durante el ingreso.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-09 16:27:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Me sentí bien acompañado durante el ingreso.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-12 13:37:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 7, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-14 17:13:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'El baño de la sala no estaba muy limpio.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-15 11:23:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'La enfermera de la noche fue muy atenta.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-15 16:52:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 3, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Sería bueno tener wifi para los pacientes.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-16 08:14:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Demoraron bastante en darme la cama, pero después todo bien.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-16 08:36:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 4, NULL), (@r, 2, 6, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-21 09:52:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 7, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-09-23 09:37:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 5, NULL), (@r, 2, 7, NULL), (@r, 3, NULL, 'Todo muy claro y rápido.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-10-01 09:36:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 4, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Me sentí bien acompañado durante el ingreso.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-10-01 15:37:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 2, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Demoraron bastante en darme la cama, pero después todo bien.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-10-12 13:19:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 3, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Mucha espera en la guardia antes de subir a sala.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (1, '2026-10-14 18:40:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, 1, 3, NULL), (@r, 2, 6, NULL), (@r, 3, NULL, 'Muy buena atención, me explicaron todo con paciencia.');

-- Encuesta de satisfaccion del usuario trasplantado
SET @cat = (SELECT id_categoria FROM categoria WHERE codigo_qr = 'QR-CAT-0003');
INSERT INTO encuesta (id_categoria, titulo, descripcion, fecha_inicio, fecha_fin, segmento) VALUES (@cat, 'Satisfacción del usuario trasplantado', 'Tu opinión nos ayuda a mejorar el programa de trasplante.', '2026-09-01', NULL, 'Usuarios trasplantados');
SET @enc = LAST_INSERT_ID();

INSERT INTO pregunta (id_encuesta, texto_pregunta, tipo_pregunta, orden, obligatoria) VALUES (@enc, '¿Cómo calificarías la atención del equipo de trasplante?', 'escala', 1, 1);
SET @p1 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p1, '1 - Muy mala'); SET @e1 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p1, '2 - Mala'); SET @e2 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p1, '3 - Regular'); SET @e3 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p1, '4 - Buena'); SET @e4 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p1, '5 - Muy buena'); SET @e5 = LAST_INSERT_ID();
INSERT INTO pregunta (id_encuesta, texto_pregunta, tipo_pregunta, orden, obligatoria) VALUES (@enc, '¿Recibiste la información que necesitabas antes del alta?', 'opcion_unica', 2, 1);
SET @p2 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p2, 'Sí, completa'); SET @i1 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p2, 'En parte'); SET @i2 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p2, 'No'); SET @i3 = LAST_INSERT_ID();
INSERT INTO pregunta (id_encuesta, texto_pregunta, tipo_pregunta, orden, obligatoria) VALUES (@enc, '¿Recomendarías el programa a otra persona que lo necesite?', 'opcion_unica', 3, 1);
SET @p3 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p3, 'Sí'); SET @c1 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p3, 'No estoy seguro'); SET @c2 = LAST_INSERT_ID();
INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES (@p3, 'No'); SET @c3 = LAST_INSERT_ID();
INSERT INTO pregunta (id_encuesta, texto_pregunta, tipo_pregunta, orden, obligatoria) VALUES (@enc, '¿Qué podríamos mejorar?', 'abierta', 4, 0);
SET @p4 = LAST_INSERT_ID();

INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-04 11:39:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-05 13:47:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i2, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Mejorar la coordinación de los turnos de laboratorio.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-11 16:49:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e4, NULL), (@r, @p2, @i2, NULL), (@r, @p3, @c3, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-12 14:47:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e4, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Los controles a veces se atrasan mucho.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-13 19:10:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e4, NULL), (@r, @p2, @i3, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Mejorar la coordinación de los turnos de laboratorio.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-18 19:34:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-19 18:16:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i2, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Mejorar la coordinación de los turnos de laboratorio.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-23 09:54:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e3, NULL), (@r, @p2, @i3, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Me hubiera gustado recibir las indicaciones por escrito antes.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-24 09:09:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'La sala de espera es chica para tanta gente.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-24 15:30:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c2, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-25 11:33:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'El seguimiento después del alta fue excelente.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-25 18:14:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i2, NULL), (@r, @p3, @c1, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-09-30 11:51:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e3, NULL), (@r, @p2, @i3, NULL), (@r, @p3, @c1, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-10-11 16:23:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e2, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL);
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-10-14 13:49:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e4, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c2, NULL), (@r, @p4, NULL, 'Mejorar la coordinación de los turnos de laboratorio.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-10-14 16:19:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c2, NULL), (@r, @p4, NULL, 'Las nefrólogas siempre estuvieron disponibles.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-10-16 17:23:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i1, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Las nefrólogas siempre estuvieron disponibles.');
INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES (@enc, '2026-10-17 09:44:00');
SET @r = LAST_INSERT_ID();
INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (@r, @p1, @e5, NULL), (@r, @p2, @i2, NULL), (@r, @p3, @c1, NULL), (@r, @p4, NULL, 'Me hubiera gustado recibir las indicaciones por escrito antes.');


-- ---------------------------------------------------------------------
-- Traslados de ejemplo
-- ---------------------------------------------------------------------

-- Copia local de pacientes ya consultados a la API (los mismos de la API simulada)
INSERT INTO paciente (ci, nombre, apellido) VALUES
('34567894', 'María', 'Silva'),
('41234563', 'Juan', 'Rodríguez'),
('29876545', 'Lucía', 'Gómez');

SET @funcionario = 2;  -- tLeites

-- 1) Paciente en viaje al Hospital Maciel (salió hace 22 minutos)
INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud)
VALUES ('34567894', 1, 1, 2, 1, @funcionario, NOW() - INTERVAL 70 MINUTE, 'Consulta con cirugía vascular', 'aprobada');
SET @s = LAST_INSERT_ID();
INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_ruta, hora_salida_prev, hora_salida_real, hora_llegada_prev)
VALUES (@s, 1, 5, 8, 1, NOW() - INTERVAL 25 MINUTE, NOW() - INTERVAL 22 MINUTE, NOW() - INTERVAL 5 MINUTE);
SET @t = LAST_INSERT_ID();
INSERT INTO historial_estado (id_traslado, id_estado, fecha_hora, id_usuario, observaciones) VALUES
(@t, 1, NOW() - INTERVAL 70 MINUTE, @funcionario, NULL),
(@t, 2, NOW() - INTERVAL 22 MINUTE, @funcionario, 'Salió con oxígeno');

-- 2) Material biológico programado al Banco de Órganos (sale en hora y media)
INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud)
VALUES (NULL, 2, 1, 5, 2, @funcionario, NOW() - INTERVAL 30 MINUTE, 'Muestras de tejido para estudio', 'aprobada');
SET @s = LAST_INSERT_ID();
INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_ruta, hora_salida_prev, hora_llegada_prev)
VALUES (@s, 5, 6, 9, 4, NOW() + INTERVAL 90 MINUTE, NOW() + INTERVAL 95 MINUTE);
SET @t = LAST_INSERT_ID();
INSERT INTO historial_estado (id_traslado, id_estado, fecha_hora, id_usuario) VALUES (@t, 1, NOW() - INTERVAL 30 MINUTE, @funcionario);

-- 3) Paciente al Pereira Rossell, finalizado ayer
INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud)
VALUES ('41234563', 1, 1, 3, 3, @funcionario, NOW() - INTERVAL 1 DAY - INTERVAL 3 HOUR, 'Estudio de imagen', 'aprobada');
SET @s = LAST_INSERT_ID();
INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_ruta, hora_salida_prev, hora_salida_real, hora_llegada_prev, hora_llegada_real, hora_retorno)
VALUES (@s, 2, 7, 10, 2, NOW() - INTERVAL 1 DAY - INTERVAL 2 HOUR, NOW() - INTERVAL 1 DAY - INTERVAL 115 MINUTE,
        NOW() - INTERVAL 1 DAY - INTERVAL 108 MINUTE, NOW() - INTERVAL 1 DAY - INTERVAL 100 MINUTE, NOW() - INTERVAL 1 DAY - INTERVAL 40 MINUTE);
SET @t = LAST_INSERT_ID();
INSERT INTO historial_estado (id_traslado, id_estado, fecha_hora, id_usuario, observaciones) VALUES
(@t, 1, NOW() - INTERVAL 1 DAY - INTERVAL 3 HOUR, @funcionario, NULL),
(@t, 2, NOW() - INTERVAL 1 DAY - INTERVAL 115 MINUTE, @funcionario, NULL),
(@t, 3, NOW() - INTERVAL 1 DAY - INTERVAL 100 MINUTE, @funcionario, 'Paciente entregado en guardia'),
(@t, 4, NOW() - INTERVAL 1 DAY - INTERVAL 55 MINUTE, @funcionario, NULL),
(@t, 5, NOW() - INTERVAL 1 DAY - INTERVAL 40 MINUTE, @funcionario, NULL);

-- 4) Equipamiento al Instituto del Cáncer, finalizado hoy temprano
INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud)
VALUES (NULL, 3, 1, 4, 4, @funcionario, NOW() - INTERVAL 5 HOUR, 'Bomba de infusión en préstamo', 'aprobada');
SET @s = LAST_INSERT_ID();
INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_ruta, hora_salida_prev, hora_salida_real, hora_llegada_prev, hora_llegada_real, hora_retorno)
VALUES (@s, 3, 5, 8, 3, NOW() - INTERVAL 4 HOUR, NOW() - INTERVAL 235 MINUTE, NOW() - INTERVAL 230 MINUTE, NOW() - INTERVAL 224 MINUTE, NOW() - INTERVAL 200 MINUTE);
SET @t = LAST_INSERT_ID();
INSERT INTO historial_estado (id_traslado, id_estado, fecha_hora, id_usuario) VALUES
(@t, 1, NOW() - INTERVAL 5 HOUR, @funcionario),
(@t, 2, NOW() - INTERVAL 235 MINUTE, @funcionario),
(@t, 3, NOW() - INTERVAL 224 MINUTE, @funcionario),
(@t, 4, NOW() - INTERVAL 215 MINUTE, @funcionario),
(@t, 5, NOW() - INTERVAL 200 MINUTE, @funcionario);

-- 5) Paciente a Salto derivado a una empresa externa (en viaje)
INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud)
VALUES ('29876545', 1, 1, 9, 1, @funcionario, NOW() - INTERVAL 3 HOUR, 'Regreso a su departamento después del alta', 'aprobada');
SET @s = LAST_INSERT_ID();
INSERT INTO traslado (id_solicitud, id_proveedor, id_ruta, hora_salida_prev, hora_salida_real, hora_llegada_prev)
VALUES (@s, 2, 8, NOW() - INTERVAL 2 HOUR, NOW() - INTERVAL 110 MINUTE, NOW() + INTERVAL 220 MINUTE);
SET @t = LAST_INSERT_ID();
INSERT INTO historial_estado (id_traslado, id_estado, fecha_hora, id_usuario, observaciones) VALUES
(@t, 1, NOW() - INTERVAL 3 HOUR, @funcionario, 'Sin ambulancia libre para un viaje tan largo: se deriva'),
(@t, 2, NOW() - INTERVAL 110 MINUTE, @funcionario, NULL);

-- 6) Insumos a Maldonado, cancelado
INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud)
VALUES (NULL, 4, 1, 7, 2, @funcionario, NOW() - INTERVAL 6 HOUR, 'Medicación oncológica', 'anulada');
SET @s = LAST_INSERT_ID();
INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_ruta, hora_salida_prev, hora_llegada_prev)
VALUES (@s, 6, 7, 10, 6, NOW() - INTERVAL 4 HOUR, NOW() - INTERVAL 130 MINUTE);
SET @t = LAST_INSERT_ID();
INSERT INTO historial_estado (id_traslado, id_estado, fecha_hora, id_usuario, observaciones) VALUES
(@t, 1, NOW() - INTERVAL 6 HOUR, @funcionario, NULL),
(@t, 6, NOW() - INTERVAL 5 HOUR, @funcionario, 'Lo retiran ellos por su cuenta');

COMMIT;
