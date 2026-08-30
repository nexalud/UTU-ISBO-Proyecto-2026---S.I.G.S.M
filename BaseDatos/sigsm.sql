-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-08-2026 a las 16:40:11
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sigsm`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `codigo_qr` varchar(255) NOT NULL,
  `tipo_categoria` enum('documental','encuesta') NOT NULL DEFAULT 'documental'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre`, `descripcion`, `codigo_qr`, `tipo_categoria`) VALUES
(1, 'Estudios de laboratorio', 'Análisis clínicos y resultados de laboratorio', 'QR-CAT-0001', 'documental'),
(2, 'Informes de imagenología', 'Radiografías, ecografías y tomografías', 'QR-CAT-0002', 'documental'),
(3, 'Altas médicas', 'Documentación de altas y epicrisis', 'QR-CAT-0003', 'documental'),
(4, 'Encuesta de bienvenida', 'Encuesta de satisfacción predeterminada al ingreso', 'QR-CAT-0004', 'encuesta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

CREATE TABLE `documento` (
  `id_documento` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `archivo_url` varchar(255) NOT NULL,
  `fecha_carga` datetime NOT NULL DEFAULT current_timestamp(),
  `id_categoria` int(11) NOT NULL,
  `id_usuario_carga` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documento`
--

INSERT INTO `documento` (`id_documento`, `titulo`, `descripcion`, `archivo_url`, `fecha_carga`, `id_categoria`, `id_usuario_carga`, `activo`) VALUES
(1, 'Hemograma completo - Juan Pérez', 'Resultado de laboratorio', '/archivos/hemograma_001.pdf', '2026-08-23 02:10:12', 1, 2, 1),
(2, 'Glucemia - María López', 'Resultado de laboratorio', '/archivos/glucemia_002.pdf', '2026-08-23 02:10:12', 1, 2, 1),
(3, 'Radiografía de tórax', 'Estudio de imagenología', '/archivos/rx_torax_003.pdf', '2026-08-23 02:10:12', 2, 3, 1),
(4, 'Ecografía abdominal', 'Estudio de imagenología', '/archivos/eco_abdom_004.pdf', '2026-08-23 02:10:12', 2, 3, 1),
(5, 'Epicrisis - internación cardiología', 'Resumen de alta médica', '/archivos/epicrisis_005.pdf', '2026-08-23 02:10:12', 3, 2, 1),
(6, 'Indicaciones post-alta', 'Cuidados posteriores al alta', '/archivos/indicaciones_006.pdf', '2026-08-23 02:10:12', 3, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta`
--

CREATE TABLE `encuesta` (
  `id_encuesta` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `segmento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuesta`
--

INSERT INTO `encuesta` (`id_encuesta`, `id_categoria`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_fin`, `segmento`) VALUES
(1, 4, 'Encuesta de bienvenida', 'Mide la satisfacción general del paciente al ingreso', '2026-08-01', NULL, 'Pacientes internados');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opcion_respuesta`
--

CREATE TABLE `opcion_respuesta` (
  `id_opcion` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `texto_opcion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `opcion_respuesta`
--

INSERT INTO `opcion_respuesta` (`id_opcion`, `id_pregunta`, `texto_opcion`) VALUES
(1, 1, '1 - Muy mala'),
(2, 1, '2 - Mala'),
(3, 1, '3 - Regular'),
(4, 1, '4 - Buena'),
(5, 1, '5 - Muy buena'),
(6, 2, 'Sí, en todo momento'),
(7, 2, 'Parcialmente'),
(8, 2, 'No');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pregunta`
--

CREATE TABLE `pregunta` (
  `id_pregunta` int(11) NOT NULL,
  `id_encuesta` int(11) NOT NULL,
  `texto_pregunta` varchar(255) NOT NULL,
  `tipo_pregunta` enum('abierta','opcion_unica','opcion_multiple','escala') NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pregunta`
--

INSERT INTO `pregunta` (`id_pregunta`, `id_encuesta`, `texto_pregunta`, `tipo_pregunta`, `orden`) VALUES
(1, 1, '¿Cómo calificarías la atención recibida?', 'escala', 1),
(2, 1, '¿El personal te explicó claramente los procedimientos?', 'opcion_unica', 2),
(3, 1, '¿Tenés comentarios adicionales?', 'abierta', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta_encuesta`
--

CREATE TABLE `respuesta_encuesta` (
  `id_resp_encuesta` int(11) NOT NULL,
  `id_encuesta` int(11) NOT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuesta_encuesta`
--

INSERT INTO `respuesta_encuesta` (`id_resp_encuesta`, `id_encuesta`, `fecha_envio`) VALUES
(1, 1, '2026-08-10 09:15:00'),
(2, 1, '2026-08-15 14:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta_pregunta`
--

CREATE TABLE `respuesta_pregunta` (
  `id_resp_pregunta` int(11) NOT NULL,
  `id_resp_encuesta` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_opcion` int(11) DEFAULT NULL,
  `texto_libre` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuesta_pregunta`
--

INSERT INTO `respuesta_pregunta` (`id_resp_pregunta`, `id_resp_encuesta`, `id_pregunta`, `id_opcion`, `texto_libre`) VALUES
(1, 1, 1, 5, NULL),
(2, 1, 2, 6, NULL),
(3, 1, 3, NULL, 'Muy buena atención, todo el equipo fue muy amable.'),
(4, 2, 1, 4, NULL),
(5, 2, 2, 7, NULL),
(6, 2, 3, NULL, 'Tuve que esperar bastante en la sala antes de que me atendieran.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'administrador', 'Administra usuarios, roles y configuración del sistema'),
(2, 'usuario_basico', 'Acceso de consulta a los módulos habilitados'),
(3, 'funcionario', 'Personal administrativo que gestiona categorías y documentos'),
(4, 'medico', 'Solicita traslados de pacientes y elementos'),
(5, 'enfermero', 'Solicita traslados y acompaña al paciente durante el traslado'),
(6, 'chofer', 'Conduce los vehículos de la flota del hospital'),
(7, 'operador_ue', 'Operador de la Unidad de Enlace; recibe y asigna las solicitudes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL CHECK (char_length(`nombre_usuario`) >= 4),
  `contrasenha_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL CHECK (`email` like '%_@_%.__%'),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `contrasenha_hash`, `nombre`, `apellido`, `email`, `activo`) VALUES
(1, 'eBanfi', '$2y$10$ZiB853b42WTDysHN1Xb4d.b0wbHUP0bwQPbvW1thdkVGnv0zMdDAK', 'Ezequiel', 'Banfi', 'eBanfi@isbo.edu.uy', 1),
(2, 'tLeites', '$2y$10$vqzdTW0Uan2Lr9MJ2aF/we0jsWvqGCCgzG5xrxmWogyjZwXk2oDVG', 'Thiago', 'Leites', 'tLeites@isbo.edu.uy', 1),
(3, 'mGomez', '$2y$10$mjl0XltDTpmTvUTxjUrnO.ZoBo4dbQiRgxItSAwHyi8cMIX0OVL6e', 'Mathias', 'Gómez', 'mGomez@isbo.edu.uy', 1),
(4, 'sAlmeida', '$2y$10$nV5ScDW1QQSY7wJch3izkOVj38JMa1YMScz3EG50UOTDUJj5zjEvC', 'Sabrina', 'Almeida', 'sAlmeida@isbo.edu.uy', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

CREATE TABLE `usuario_rol` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_rol`
--

INSERT INTO `usuario_rol` (`id_usuario`, `id_rol`, `fecha_asignacion`) VALUES
(1, 1, '2026-08-23 00:00:00'),
(1, 2, '2026-08-23 00:00:00'),
(2, 2, '2026-08-23 00:00:00'),
(2, 3, '2026-08-23 00:00:00'),
(3, 2, '2026-08-23 00:00:00'),
(4, 2, '2026-08-23 00:00:00'),
(4, 3, '2026-08-23 00:00:00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uq_categoria_nombre` (`nombre`),
  ADD UNIQUE KEY `uq_categoria_qr` (`codigo_qr`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `fk_doc_usuario` (`id_usuario_carga`),
  ADD KEY `idx_doc_categoria` (`id_categoria`);

--
-- Indices de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD PRIMARY KEY (`id_encuesta`),
  ADD KEY `fk_enc_categoria` (`id_categoria`);

--
-- Indices de la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  ADD PRIMARY KEY (`id_opcion`),
  ADD KEY `fk_opc_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `idx_preg_encuesta` (`id_encuesta`);

--
-- Indices de la tabla `respuesta_encuesta`
--
ALTER TABLE `respuesta_encuesta`
  ADD PRIMARY KEY (`id_resp_encuesta`),
  ADD KEY `fk_re_encuesta` (`id_encuesta`);

--
-- Indices de la tabla `respuesta_pregunta`
--
ALTER TABLE `respuesta_pregunta`
  ADD PRIMARY KEY (`id_resp_pregunta`),
  ADD UNIQUE KEY `uq_rp_sesion_pregunta` (`id_resp_encuesta`,`id_pregunta`),
  ADD KEY `fk_rp_pregunta` (`id_pregunta`),
  ADD KEY `fk_rp_opcion` (`id_opcion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_rol_nombre` (`nombre_rol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuario_nombre` (`nombre_usuario`),
  ADD UNIQUE KEY `uq_usuario_email` (`email`);

--
-- Indices de la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD PRIMARY KEY (`id_usuario`,`id_rol`),
  ADD KEY `idx_ur_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `documento`
--
ALTER TABLE `documento`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  MODIFY `id_encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `respuesta_encuesta`
--
ALTER TABLE `respuesta_encuesta`
  MODIFY `id_resp_encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `respuesta_pregunta`
--
ALTER TABLE `respuesta_pregunta`
  MODIFY `id_resp_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `fk_doc_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doc_usuario` FOREIGN KEY (`id_usuario_carga`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD CONSTRAINT `fk_enc_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  ADD CONSTRAINT `fk_opc_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `pregunta` (`id_pregunta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD CONSTRAINT `fk_preg_encuesta` FOREIGN KEY (`id_encuesta`) REFERENCES `encuesta` (`id_encuesta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta_encuesta`
--
ALTER TABLE `respuesta_encuesta`
  ADD CONSTRAINT `fk_re_encuesta` FOREIGN KEY (`id_encuesta`) REFERENCES `encuesta` (`id_encuesta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta_pregunta`
--
ALTER TABLE `respuesta_pregunta`
  ADD CONSTRAINT `fk_rp_opcion` FOREIGN KEY (`id_opcion`) REFERENCES `opcion_respuesta` (`id_opcion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rp_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `pregunta` (`id_pregunta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rp_resp_encuesta` FOREIGN KEY (`id_resp_encuesta`) REFERENCES `respuesta_encuesta` (`id_resp_encuesta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;