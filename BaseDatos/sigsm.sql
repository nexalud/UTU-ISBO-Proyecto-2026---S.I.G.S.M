-- =====================================================================
-- S.I.G.S.M. - Sistema Informático de Gestión de Servicios Médicos
-- Nexalud · Hospital de Clínicas · 2026
--
-- Base completa: documentación y encuestas (módulo 1) y ambulancias (módulo 2).
-- Se importa sobre una base vacía llamada sigsm:
--   CREATE DATABASE sigsm CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = "+00:00";
START TRANSACTION;

-- ---------------------------------------------------------------------
-- Usuarios y roles
-- ---------------------------------------------------------------------

CREATE TABLE rol (
  id_rol      INT          NOT NULL AUTO_INCREMENT,
  nombre_rol  VARCHAR(50)  NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_rol),
  UNIQUE KEY uq_rol_nombre (nombre_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE usuario (
  id_usuario       INT          NOT NULL AUTO_INCREMENT,
  nombre_usuario   VARCHAR(50)  NOT NULL CHECK (CHAR_LENGTH(nombre_usuario) >= 4),
  contrasenha_hash VARCHAR(255) NOT NULL,              -- bcrypt, nunca la contraseña en texto
  nombre           VARCHAR(100) NOT NULL,
  apellido         VARCHAR(100) NOT NULL,
  email            VARCHAR(150) NOT NULL CHECK (email LIKE '%_@_%.__%'),
  activo           TINYINT(1)   NOT NULL DEFAULT 1,     -- los usuarios no se borran: se desactivan
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuario_nombre (nombre_usuario),
  UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE usuario_rol (
  id_usuario       INT      NOT NULL,
  id_rol           INT      NOT NULL,
  fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario, id_rol),
  KEY idx_ur_rol (id_rol),
  CONSTRAINT fk_ur_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ur_rol     FOREIGN KEY (id_rol)     REFERENCES rol (id_rol)         ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Enlaces de un solo uso para crear o restablecer la contraseña (llegan por correo).
-- Se guarda el hash SHA-256 del código, nunca el código.
CREATE TABLE recuperacion_contrasena (
  id_recuperacion INT      NOT NULL AUTO_INCREMENT,
  id_usuario      INT      NOT NULL,
  token_hash      CHAR(64) NOT NULL,
  tipo            ENUM('restablecer', 'bienvenida') NOT NULL DEFAULT 'restablecer',
  fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_expira    DATETIME NOT NULL,
  fecha_uso       DATETIME DEFAULT NULL,
  PRIMARY KEY (id_recuperacion),
  UNIQUE KEY uq_recuperacion_token (token_hash),
  KEY idx_recuperacion_usuario (id_usuario, fecha_creacion),
  CONSTRAINT fk_recuperacion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Documentación: cada categoría es un área del hospital y tiene UN QR.
-- Ese QR muestra los documentos del área y, si hay, su encuesta abierta.
-- ---------------------------------------------------------------------

CREATE TABLE categoria (
  id_categoria INT          NOT NULL AUTO_INCREMENT,
  nombre       VARCHAR(100) NOT NULL,
  descripcion  VARCHAR(255) DEFAULT NULL,
  codigo_qr    VARCHAR(255) NOT NULL,                  -- fijo: los QR impresos no dejan de funcionar
  PRIMARY KEY (id_categoria),
  UNIQUE KEY uq_categoria_nombre (nombre),
  UNIQUE KEY uq_categoria_qr (codigo_qr)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE documento (
  id_documento     INT          NOT NULL AUTO_INCREMENT,
  titulo           VARCHAR(150) NOT NULL,
  descripcion      VARCHAR(255) DEFAULT NULL,
  archivo_url      VARCHAR(255) NOT NULL,              -- nombre aleatorio del PDF en el servidor
  fecha_carga      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_categoria     INT          NOT NULL,
  id_usuario_carga INT          NOT NULL,
  activo           TINYINT(1)   NOT NULL DEFAULT 1,     -- inactivo = no lo ve el paciente
  PRIMARY KEY (id_documento),
  KEY idx_doc_categoria (id_categoria),
  KEY fk_doc_usuario (id_usuario_carga),
  CONSTRAINT fk_doc_categoria FOREIGN KEY (id_categoria)     REFERENCES categoria (id_categoria) ON UPDATE CASCADE,
  CONSTRAINT fk_doc_usuario   FOREIGN KEY (id_usuario_carga) REFERENCES usuario (id_usuario)     ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Encuestas (anónimas): no se guarda ningún dato del paciente
-- ---------------------------------------------------------------------

CREATE TABLE encuesta (
  id_encuesta  INT          NOT NULL AUTO_INCREMENT,
  id_categoria INT          DEFAULT NULL,              -- en qué QR (área) aparece
  titulo       VARCHAR(150) NOT NULL,
  descripcion  VARCHAR(255) DEFAULT NULL,
  fecha_inicio DATE         DEFAULT NULL,
  fecha_fin    DATE         DEFAULT NULL,              -- vacía = abierta hasta que se cierre
  segmento     VARCHAR(100) DEFAULT NULL,              -- a quiénes está dirigida
  PRIMARY KEY (id_encuesta),
  KEY fk_enc_categoria (id_categoria),
  CONSTRAINT fk_enc_categoria FOREIGN KEY (id_categoria) REFERENCES categoria (id_categoria) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pregunta (
  id_pregunta    INT          NOT NULL AUTO_INCREMENT,
  id_encuesta    INT          NOT NULL,
  texto_pregunta VARCHAR(255) NOT NULL,
  tipo_pregunta  ENUM('abierta', 'opcion_unica', 'escala') NOT NULL,
  orden          INT          NOT NULL,
  obligatoria    TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id_pregunta),
  KEY idx_preg_encuesta (id_encuesta),
  CONSTRAINT fk_preg_encuesta FOREIGN KEY (id_encuesta) REFERENCES encuesta (id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE opcion_respuesta (
  id_opcion    INT          NOT NULL AUTO_INCREMENT,
  id_pregunta  INT          NOT NULL,
  texto_opcion VARCHAR(150) NOT NULL,
  PRIMARY KEY (id_opcion),
  KEY fk_opc_pregunta (id_pregunta),
  CONSTRAINT fk_opc_pregunta FOREIGN KEY (id_pregunta) REFERENCES pregunta (id_pregunta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Una fila por cada vez que alguien responde
CREATE TABLE respuesta_encuesta (
  id_resp_encuesta INT      NOT NULL AUTO_INCREMENT,
  id_encuesta      INT      NOT NULL,
  fecha_envio      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_resp_encuesta),
  KEY fk_re_encuesta (id_encuesta),
  CONSTRAINT fk_re_encuesta FOREIGN KEY (id_encuesta) REFERENCES encuesta (id_encuesta) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Lo que contestó en cada pregunta: una opción o un texto
CREATE TABLE respuesta_pregunta (
  id_resp_pregunta INT          NOT NULL AUTO_INCREMENT,
  id_resp_encuesta INT          NOT NULL,
  id_pregunta      INT          NOT NULL,
  id_opcion        INT          DEFAULT NULL,
  texto_libre      VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (id_resp_pregunta),
  UNIQUE KEY uq_rp_sesion_pregunta (id_resp_encuesta, id_pregunta),
  KEY fk_rp_pregunta (id_pregunta),
  KEY fk_rp_opcion (id_opcion),
  CONSTRAINT fk_rp_resp_encuesta FOREIGN KEY (id_resp_encuesta) REFERENCES respuesta_encuesta (id_resp_encuesta) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rp_pregunta      FOREIGN KEY (id_pregunta)      REFERENCES pregunta (id_pregunta)  ON UPDATE CASCADE,
  CONSTRAINT fk_rp_opcion        FOREIGN KEY (id_opcion)        REFERENCES opcion_respuesta (id_opcion) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- Ambulancias: flota, lugares, rutas y traslados (Bloque 3 del DER)
-- ---------------------------------------------------------------------

-- Copia local de los pacientes que se consultaron a la API externa del hospital (por cédula)
CREATE TABLE paciente (
  ci                 VARCHAR(12)  NOT NULL,
  nombre             VARCHAR(50)  NOT NULL,
  apellido           VARCHAR(50)  NOT NULL,
  fecha_consulta_api DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ci)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tipo_vehiculo (
  id_tipo_vehiculo INT          NOT NULL AUTO_INCREMENT,
  nombre           VARCHAR(50)  NOT NULL,
  descripcion      VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_tipo_vehiculo),
  UNIQUE KEY uq_tipo_vehiculo_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Qué se traslada: un paciente, material biológico, equipamiento...
CREATE TABLE tipo_elemento (
  id_tipo_elemento  INT          NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(50)  NOT NULL,
  descripcion       VARCHAR(255) DEFAULT NULL,
  requiere_paciente TINYINT(1)   NOT NULL DEFAULT 0,  -- 1 = hay que indicar la cédula del paciente
  PRIMARY KEY (id_tipo_elemento),
  UNIQUE KEY uq_tipo_elemento_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- "No todos los traslados se pueden hacer en cualquier vehículo": qué tipo de vehículo puede llevar qué
CREATE TABLE compatibilidad (
  id_tipo_vehiculo INT          NOT NULL,
  id_tipo_elemento INT          NOT NULL,
  observaciones    VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_tipo_vehiculo, id_tipo_elemento),
  CONSTRAINT fk_compat_tipo_vehiculo FOREIGN KEY (id_tipo_vehiculo) REFERENCES tipo_vehiculo (id_tipo_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_compat_tipo_elemento FOREIGN KEY (id_tipo_elemento) REFERENCES tipo_elemento (id_tipo_elemento) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE vehiculo (
  id_vehiculo      INT         NOT NULL AUTO_INCREMENT,
  matricula        VARCHAR(10) NOT NULL,
  marca            VARCHAR(50) NOT NULL,
  modelo           VARCHAR(50) NOT NULL,
  anho             INT         NOT NULL CHECK (anho BETWEEN 1990 AND 2100),
  id_tipo_vehiculo INT         NOT NULL,
  activo           TINYINT(1)  NOT NULL DEFAULT 1,     -- 0 = fuera de servicio (taller, baja)
  PRIMARY KEY (id_vehiculo),
  UNIQUE KEY uq_vehiculo_matricula (matricula),
  CONSTRAINT fk_vehiculo_tipo FOREIGN KEY (id_tipo_vehiculo) REFERENCES tipo_vehiculo (id_tipo_vehiculo) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ubicacion (
  id_ubicacion   INT          NOT NULL AUTO_INCREMENT,
  nombre         VARCHAR(100) NOT NULL,
  direccion      VARCHAR(255) NOT NULL,
  tipo_ubicacion ENUM('hospital', 'centro_salud', 'domicilio', 'otro') NOT NULL,
  PRIMARY KEY (id_ubicacion),
  UNIQUE KEY uq_ubicacion_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Rutina base de cada recorrido: distancia y tiempo estimado entre dos lugares
-- (las claves que se usan en un CHECK no llevan ON UPDATE CASCADE: MariaDB no lo permite, y los id nunca cambian)
CREATE TABLE ruta (
  id_ruta      INT          NOT NULL AUTO_INCREMENT,
  id_origen    INT          NOT NULL,
  id_destino   INT          NOT NULL,
  descripcion  VARCHAR(255) DEFAULT NULL,
  distancia_km DECIMAL(6,2) NOT NULL CHECK (distancia_km > 0),
  tiempo_min   INT          NOT NULL CHECK (tiempo_min > 0),
  PRIMARY KEY (id_ruta),
  UNIQUE KEY uq_ruta_origen_destino (id_origen, id_destino),
  CONSTRAINT ck_ruta_distinta CHECK (id_origen <> id_destino),
  CONSTRAINT fk_ruta_origen  FOREIGN KEY (id_origen)  REFERENCES ubicacion (id_ubicacion),
  CONSTRAINT fk_ruta_destino FOREIGN KEY (id_destino) REFERENCES ubicacion (id_ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Por dónde llegó el pedido de traslado
CREATE TABLE canal_solicitud (
  id_canal INT         NOT NULL AUTO_INCREMENT,
  nombre   VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_canal),
  UNIQUE KEY uq_canal_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Empresa externa a la que se deriva el traslado cuando el hospital no tiene recursos libres
CREATE TABLE proveedor_externo (
  id_proveedor INT          NOT NULL AUTO_INCREMENT,
  nombre       VARCHAR(100) NOT NULL,
  contacto     VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (id_proveedor),
  UNIQUE KEY uq_proveedor_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ciclo de un traslado: programado -> en viaje -> en destino -> regresando -> finalizado (o cancelado)
CREATE TABLE estado_traslado (
  id_estado INT         NOT NULL AUTO_INCREMENT,
  nombre    VARCHAR(50) NOT NULL,
  orden     INT         NOT NULL,
  PRIMARY KEY (id_estado),
  UNIQUE KEY uq_estado_nombre (nombre),
  UNIQUE KEY uq_estado_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE solicitud_traslado (
  id_solicitud       INT          NOT NULL AUTO_INCREMENT,
  ci_paciente        VARCHAR(12)  DEFAULT NULL,          -- solo si lo que se traslada es un paciente
  id_tipo_elemento   INT          NOT NULL,
  id_origen          INT          NOT NULL,
  id_destino         INT          NOT NULL,
  id_canal           INT          NOT NULL,
  id_usuario_solicit INT          NOT NULL,              -- funcionario que la registró
  fecha_solicitud    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  motivo             VARCHAR(255) DEFAULT NULL,
  estado_solicitud   ENUM('pendiente', 'aprobada', 'rechazada', 'anulada') NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (id_solicitud),
  KEY idx_solicitud_estado (estado_solicitud),
  CONSTRAINT ck_solicitud_distinta CHECK (id_origen <> id_destino),
  CONSTRAINT fk_solicitud_paciente      FOREIGN KEY (ci_paciente)        REFERENCES paciente (ci)                    ON UPDATE CASCADE,
  CONSTRAINT fk_solicitud_tipo_elemento FOREIGN KEY (id_tipo_elemento)   REFERENCES tipo_elemento (id_tipo_elemento) ON UPDATE CASCADE,
  CONSTRAINT fk_solicitud_origen        FOREIGN KEY (id_origen)          REFERENCES ubicacion (id_ubicacion),
  CONSTRAINT fk_solicitud_destino       FOREIGN KEY (id_destino)         REFERENCES ubicacion (id_ubicacion),
  CONSTRAINT fk_solicitud_canal         FOREIGN KEY (id_canal)           REFERENCES canal_solicitud (id_canal)       ON UPDATE CASCADE,
  CONSTRAINT fk_solicitud_usuario       FOREIGN KEY (id_usuario_solicit) REFERENCES usuario (id_usuario)             ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE traslado (
  id_traslado       INT      NOT NULL AUTO_INCREMENT,
  id_solicitud      INT      NOT NULL,
  id_vehiculo       INT      DEFAULT NULL,
  id_chofer         INT      DEFAULT NULL,
  id_enfermero      INT      DEFAULT NULL,
  id_proveedor      INT      DEFAULT NULL,
  id_ruta           INT      DEFAULT NULL,
  hora_salida_prev  DATETIME NOT NULL,
  hora_salida_real  DATETIME DEFAULT NULL,
  hora_llegada_prev DATETIME DEFAULT NULL,
  hora_llegada_real DATETIME DEFAULT NULL,
  hora_retorno      DATETIME DEFAULT NULL,
  PRIMARY KEY (id_traslado),
  UNIQUE KEY uq_traslado_solicitud (id_solicitud),
  -- Se hace con recursos del hospital (vehículo + chofer + enfermero) o se deriva a un proveedor, nunca las dos cosas
  CONSTRAINT ck_traslado_recursos CHECK (
    (id_vehiculo IS NOT NULL AND id_chofer IS NOT NULL AND id_enfermero IS NOT NULL AND id_proveedor IS NULL)
    OR (id_vehiculo IS NULL AND id_chofer IS NULL AND id_enfermero IS NULL AND id_proveedor IS NOT NULL)
  ),
  CONSTRAINT ck_traslado_personas CHECK (id_chofer IS NULL OR id_chofer <> id_enfermero),
  CONSTRAINT ck_traslado_llegada  CHECK (hora_llegada_real IS NULL OR hora_salida_real IS NULL OR hora_llegada_real >= hora_salida_real),
  CONSTRAINT ck_traslado_retorno  CHECK (hora_retorno IS NULL OR hora_llegada_real IS NULL OR hora_retorno >= hora_llegada_real),
  CONSTRAINT fk_traslado_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitud_traslado (id_solicitud) ON UPDATE CASCADE,
  CONSTRAINT fk_traslado_vehiculo  FOREIGN KEY (id_vehiculo)  REFERENCES vehiculo (id_vehiculo),
  CONSTRAINT fk_traslado_chofer    FOREIGN KEY (id_chofer)    REFERENCES usuario (id_usuario),
  CONSTRAINT fk_traslado_enfermero FOREIGN KEY (id_enfermero) REFERENCES usuario (id_usuario),
  CONSTRAINT fk_traslado_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedor_externo (id_proveedor),
  CONSTRAINT fk_traslado_ruta      FOREIGN KEY (id_ruta)      REFERENCES ruta (id_ruta)                    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cada cambio de estado queda registrado: quién, cuándo y con qué observación (así se hace el seguimiento)
CREATE TABLE historial_estado (
  id_historial  INT          NOT NULL AUTO_INCREMENT,
  id_traslado   INT          NOT NULL,
  id_estado     INT          NOT NULL,
  fecha_hora    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_usuario    INT          NOT NULL,
  observaciones VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_historial),
  KEY idx_historial_traslado (id_traslado, id_historial),
  CONSTRAINT fk_historial_traslado FOREIGN KEY (id_traslado) REFERENCES traslado (id_traslado)       ON UPDATE CASCADE,
  CONSTRAINT fk_historial_estado   FOREIGN KEY (id_estado)   REFERENCES estado_traslado (id_estado)  ON UPDATE CASCADE,
  CONSTRAINT fk_historial_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuario (id_usuario)         ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================================
-- Datos iniciales
-- =====================================================================

INSERT INTO rol (id_rol, nombre_rol, descripcion) VALUES
(1, 'administrador', 'Acceso completo a los dos módulos y a la gestión de usuarios'),
(2, 'funcionario', 'Documentos, categorías, QR y encuestas'),
(3, 'operador_ue', 'Traslados, flota y rutas'),
(4, 'enfermero', 'Acompaña al paciente durante el traslado (no entra al sistema)'),
(5, 'chofer', 'Conduce los vehículos de la flota (no entra al sistema)');
INSERT INTO usuario (id_usuario, nombre_usuario, contrasenha_hash, nombre, apellido, email, activo) VALUES
(1, 'eBanfi', '$2y$10$ZiB853b42WTDysHN1Xb4d.b0wbHUP0bwQPbvW1thdkVGnv0zMdDAK', 'Ezequiel', 'Banfi', 'eBanfi@isbo.edu.uy', 1),
(2, 'tLeites', '$2y$10$vqzdTW0Uan2Lr9MJ2aF/we0jsWvqGCCgzG5xrxmWogyjZwXk2oDVG', 'Thiago', 'Leites', 'tLeites@isbo.edu.uy', 1),
(3, 'mGomez', '$2y$10$mjl0XltDTpmTvUTxjUrnO.ZoBo4dbQiRgxItSAwHyi8cMIX0OVL6e', 'Mathias', 'Gómez', 'mGomez@isbo.edu.uy', 1),
(4, 'sAlmeida', '$2y$10$nV5ScDW1QQSY7wJch3izkOVj38JMa1YMScz3EG50UOTDUJj5zjEvC', 'Sabrina', 'Almeida', 'sAlmeida@isbo.edu.uy', 1);
INSERT INTO usuario_rol (id_usuario, id_rol, fecha_asignacion) VALUES
(1, 1, '2026-08-23 00:00:00'),
(2, 2, '2026-08-23 00:00:00'),
(3, 3, '2026-08-23 00:00:00'),
(4, 2, '2026-08-23 00:00:00');

-- Áreas del hospital con los documentos que pide la letra (los PDF se cargan desde el panel)
INSERT INTO categoria (id_categoria, nombre, descripcion, codigo_qr) VALUES
(1, 'Imagenología y medicina nuclear', 'Preparación para estudios, pertecneciato y centellograma de perfusión miocárdica', 'QR-CAT-0001'),
(2, 'Cardiología', 'Ecocardiogramas y tratamiento con warfarina', 'QR-CAT-0002'),
(3, 'Nefrología y trasplante', 'Ingreso al centro, plan de alta e indicaciones para usuarios trasplantados', 'QR-CAT-0003'),
(4, 'Urología', 'Prostatectomía radical: indicaciones e información para el paciente', 'QR-CAT-0004'),
(5, 'Salud sexual y reproductiva', 'Indicaciones de interrupción voluntaria del embarazo', 'QR-CAT-0005'),
(6, 'Cuidados generales', 'Prevención de infecciones y pauta para pacientes ostomizados', 'QR-CAT-0006');

-- Encuesta estándar de satisfacción, en el QR de Cuidados generales
INSERT INTO encuesta (id_encuesta, id_categoria, titulo, descripcion, fecha_inicio, fecha_fin, segmento) VALUES
(1, 6, 'Satisfacción con la atención', 'Tu opinión nos ayuda a mejorar. Es anónima y lleva menos de un minuto.', '2026-08-01', NULL, 'Pacientes internados');

INSERT INTO pregunta (id_pregunta, id_encuesta, texto_pregunta, tipo_pregunta, orden, obligatoria) VALUES
(1, 1, '¿Cómo calificarías la atención recibida?', 'escala', 1, 1),
(2, 1, '¿El personal te explicó claramente los procedimientos?', 'opcion_unica', 2, 1),
(3, 1, '¿Tenés comentarios adicionales?', 'abierta', 3, 0);

INSERT INTO opcion_respuesta (id_opcion, id_pregunta, texto_opcion) VALUES
(1, 1, '1 - Muy mala'),
(2, 1, '2 - Mala'),
(3, 1, '3 - Regular'),
(4, 1, '4 - Buena'),
(5, 1, '5 - Muy buena'),
(6, 2, 'Sí, en todo momento'),
(7, 2, 'Parcialmente'),
(8, 2, 'No');

INSERT INTO respuesta_encuesta (id_resp_encuesta, id_encuesta, fecha_envio) VALUES
(1, 1, '2026-08-10 09:15:00'),
(2, 1, '2026-08-15 14:30:00');

INSERT INTO respuesta_pregunta (id_resp_pregunta, id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES
(1, 1, 1, 5, NULL),
(2, 1, 2, 6, NULL),
(3, 1, 3, NULL, 'Muy buena atención, todo el equipo fue muy amable.'),
(4, 2, 1, 4, NULL),
(5, 2, 2, 7, NULL),
(6, 2, 3, NULL, 'Tuve que esperar bastante en la sala antes de que me atendieran.');

-- ---------------------------------------------------------------------
-- Datos base de ambulancias
-- ---------------------------------------------------------------------

-- Choferes y enfermeros: son usuarios con su rol. No entran al panel, así que su contraseña no sirve para nada.
INSERT INTO usuario (id_usuario, nombre_usuario, contrasenha_hash, nombre, apellido, email, activo) VALUES
(5,  'cRodriguez', '$2y$10$Ywho7UzoZ/tsUdOMiN72ZeHrV6MALg.klCvk81NdOt4c0fSmorpOi', 'Carlos', 'Rodríguez', 'cRodriguez@hc.edu.uy', 1),
(6,  'jMartinez',  '$2y$10$Ywho7UzoZ/tsUdOMiN72ZeHrV6MALg.klCvk81NdOt4c0fSmorpOi', 'José',   'Martínez',  'jMartinez@hc.edu.uy', 1),
(7,  'aFernandez', '$2y$10$Ywho7UzoZ/tsUdOMiN72ZeHrV6MALg.klCvk81NdOt4c0fSmorpOi', 'Ana',    'Fernández', 'aFernandez@hc.edu.uy', 1),
(8,  'lPerez',     '$2y$10$Ywho7UzoZ/tsUdOMiN72ZeHrV6MALg.klCvk81NdOt4c0fSmorpOi', 'Laura',  'Pérez',     'lPerez@hc.edu.uy', 1),
(9,  'dLopez',     '$2y$10$Ywho7UzoZ/tsUdOMiN72ZeHrV6MALg.klCvk81NdOt4c0fSmorpOi', 'Diego',  'López',     'dLopez@hc.edu.uy', 1),
(10, 'cSanchez',   '$2y$10$Ywho7UzoZ/tsUdOMiN72ZeHrV6MALg.klCvk81NdOt4c0fSmorpOi', 'Carmen', 'Sánchez',   'cSanchez@hc.edu.uy', 1);

INSERT INTO usuario_rol (id_usuario, id_rol) VALUES
(5, 5), (6, 5), (7, 5),
(8, 4), (9, 4), (10, 4);

INSERT INTO tipo_vehiculo (id_tipo_vehiculo, nombre, descripcion) VALUES
(1, 'Ambulancia', 'Unidad equipada para trasladar pacientes'),
(2, 'Auto', 'Vehículo liviano para material e insumos');

INSERT INTO tipo_elemento (id_tipo_elemento, nombre, descripcion, requiere_paciente) VALUES
(1, 'Paciente', 'Traslado de una persona', 1),
(2, 'Material biológico', 'Órganos, muestras o sangre', 0),
(3, 'Equipamiento médico', 'Equipos que requieren transporte cuidadoso', 0),
(4, 'Insumos', 'Medicamentos y materiales', 0);

-- Un paciente solo viaja en ambulancia; el resto puede ir en cualquiera de los dos
INSERT INTO compatibilidad (id_tipo_vehiculo, id_tipo_elemento, observaciones) VALUES
(1, 1, 'Los pacientes siempre viajan en ambulancia'),
(1, 2, NULL), (2, 2, 'Con conservadora refrigerada'),
(1, 3, NULL), (2, 3, 'Solo equipos chicos'),
(1, 4, NULL), (2, 4, NULL);

INSERT INTO vehiculo (id_vehiculo, matricula, marca, modelo, anho, id_tipo_vehiculo, activo) VALUES
(1, 'SCH 1001', 'Mercedes-Benz', 'Sprinter', 2022, 1, 1),
(2, 'SCH 1002', 'Ford', 'Transit', 2021, 1, 1),
(3, 'SCH 1003', 'Mercedes-Benz', 'Sprinter', 2023, 1, 1),
(4, 'SCH 1004', 'Renault', 'Master', 2020, 1, 0),
(5, 'SCH 2001', 'Chevrolet', 'Onix', 2022, 2, 1),
(6, 'SCH 2002', 'Fiat', 'Cronos', 2021, 2, 1);

INSERT INTO ubicacion (id_ubicacion, nombre, direccion, tipo_ubicacion) VALUES
(1, 'Hospital de Clínicas', 'Av. Italia s/n, Montevideo', 'hospital'),
(2, 'Hospital Maciel', '25 de Mayo 174, Montevideo', 'hospital'),
(3, 'Hospital Pereira Rossell', 'Bvar. Artigas 1550, Montevideo', 'hospital'),
(4, 'Instituto Nacional del Cáncer', '8 de Octubre 3265, Montevideo', 'centro_salud'),
(5, 'Banco Nacional de Órganos y Tejidos', 'Av. Italia s/n, Montevideo', 'centro_salud'),
(6, 'Hospital de Las Piedras', 'Av. Artigas 714, Las Piedras, Canelones', 'hospital'),
(7, 'Hospital de Maldonado', 'Ventura Alegre s/n, Maldonado', 'hospital'),
(8, 'Hospital Escuela del Litoral', 'Montecaseros 520, Paysandú', 'hospital'),
(9, 'Hospital Regional Salto', 'Av. Juan H. Molina, Salto', 'hospital');

-- Rutina base: los recorridos salen del Hospital de Clínicas
INSERT INTO ruta (id_ruta, id_origen, id_destino, descripcion, distancia_km, tiempo_min) VALUES
(1, 1, 2, 'Av. Italia y 18 de Julio hasta Ciudad Vieja', 5.50, 20),
(2, 1, 3, 'Av. Italia y Bvar. Artigas', 3.20, 12),
(3, 1, 4, 'Por Av. Italia y 8 de Octubre', 2.80, 10),
(4, 1, 5, 'Dentro del mismo predio', 0.30, 5),
(5, 1, 6, 'Ruta 5 hasta Las Piedras', 22.00, 35),
(6, 1, 7, 'Rambla e Interbalnearia', 135.00, 110),
(7, 1, 8, 'Ruta 1 y Ruta 3', 375.00, 270),
(8, 1, 9, 'Ruta 5 y Ruta 3', 495.00, 330);

INSERT INTO canal_solicitud (id_canal, nombre) VALUES
(1, 'Teléfono'),
(2, 'Sistema del hospital'),
(3, 'Presencial'),
(4, 'Correo electrónico');

INSERT INTO proveedor_externo (id_proveedor, nombre, contacto) VALUES
(1, 'Emergencia móvil contratada', 'Central 24 horas'),
(2, 'Traslados especiales del interior', 'Coordinación por correo');

INSERT INTO estado_traslado (id_estado, nombre, orden) VALUES
(1, 'Programado', 1),
(2, 'En viaje', 2),
(3, 'En destino', 3),
(4, 'Regresando', 4),
(5, 'Finalizado', 5),
(6, 'Cancelado', 6);

-- =====================================================================
-- Vistas para las consultas más frecuentes
-- =====================================================================

-- Estado actual de cada traslado = el último registro de su historial
CREATE VIEW v_estado_actual_traslado AS
SELECT h.id_traslado, h.id_estado, e.nombre AS estado, h.fecha_hora AS desde
FROM historial_estado h
INNER JOIN estado_traslado e ON e.id_estado = h.id_estado
WHERE h.id_historial = (SELECT MAX(h2.id_historial) FROM historial_estado h2 WHERE h2.id_traslado = h.id_traslado);

-- Traslados que todavía no terminaron (programados o en la calle), con lo que se ve en el panel
CREATE VIEW v_traslados_activos AS
SELECT t.id_traslado, ea.id_estado, ea.estado, s.ci_paciente,
       CONCAT(p.nombre, ' ', p.apellido) AS paciente, te.nombre AS tipo_elemento,
       o.nombre AS origen, d.nombre AS destino, v.matricula, t.hora_salida_prev, t.hora_llegada_prev
FROM traslado t
INNER JOIN v_estado_actual_traslado ea ON ea.id_traslado = t.id_traslado
INNER JOIN solicitud_traslado s ON s.id_solicitud = t.id_solicitud
INNER JOIN tipo_elemento te     ON te.id_tipo_elemento = s.id_tipo_elemento
INNER JOIN ubicacion o          ON o.id_ubicacion = s.id_origen
INNER JOIN ubicacion d          ON d.id_ubicacion = s.id_destino
LEFT JOIN paciente p            ON p.ci = s.ci_paciente
LEFT JOIN vehiculo v            ON v.id_vehiculo = t.id_vehiculo
WHERE ea.id_estado IN (1, 2, 3, 4);

-- Cada área (categoría) con su QR y cuántos documentos tiene
CREATE VIEW v_documentos_por_categoria AS
SELECT c.id_categoria, c.nombre, c.codigo_qr,
       COUNT(d.id_documento) AS total,
       COALESCE(SUM(d.activo = 1), 0) AS activos
FROM categoria c
LEFT JOIN documento d ON d.id_categoria = c.id_categoria
GROUP BY c.id_categoria, c.nombre, c.codigo_qr;

COMMIT;
