-- =====================================================================
-- S.I.G.S.M. - Usuarios de la base de datos
-- Se ejecuta UNA vez, como root, después de importar sigsm.sql.
-- Antes de ejecutarlo, cambiar las dos contraseñas de abajo.
--
--   sigsm_admin : administrador de la base (mantenimiento, respaldos, cambios de estructura)
--   sigsm_app   : el que usa la aplicación. Solo puede leer y escribir lo que la app necesita:
--                 no puede crear ni borrar tablas, ni tocar las tablas de catálogo,
--                 ni modificar o borrar el historial de estados (queda inmutable).
--
-- Los permisos se dan a dos ROLES de la base (rol_sigsm_admin y rol_sigsm_app) y cada usuario recibe el suyo.
-- Cada usuario se crea para 'localhost' (XAMPP) y para '%' (el contenedor de la app en Docker).
-- =====================================================================

DROP USER IF EXISTS 'sigsm_admin'@'localhost', 'sigsm_admin'@'%', 'sigsm_app'@'localhost', 'sigsm_app'@'%';
DROP ROLE IF EXISTS rol_sigsm_admin, rol_sigsm_app;
CREATE ROLE rol_sigsm_admin;
CREATE ROLE rol_sigsm_app;

-- Administrador: todo sobre la base sigsm (y nada sobre otras bases)
GRANT ALL PRIVILEGES ON sigsm.* TO rol_sigsm_admin;

-- Aplicación: tablas que se leen, crean, editan y borran desde el panel
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm.categoria   TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm.documento   TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm.encuesta    TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm.pregunta    TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm.ruta        TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm.usuario_rol TO rol_sigsm_app;

-- Se crean y editan, pero nunca se borran desde la app
GRANT SELECT, INSERT, UPDATE ON sigsm.usuario                 TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE ON sigsm.recuperacion_contrasena TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE ON sigsm.paciente                TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE ON sigsm.vehiculo                TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE ON sigsm.ubicacion               TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE ON sigsm.solicitud_traslado      TO rol_sigsm_app;
GRANT SELECT, INSERT, UPDATE ON sigsm.traslado                TO rol_sigsm_app;

-- Solo se agregan filas: las respuestas de encuestas y el historial no se cambian nunca
GRANT SELECT, INSERT ON sigsm.opcion_respuesta   TO rol_sigsm_app;
GRANT SELECT, INSERT ON sigsm.respuesta_encuesta TO rol_sigsm_app;
GRANT SELECT, INSERT ON sigsm.respuesta_pregunta TO rol_sigsm_app;
GRANT SELECT, INSERT ON sigsm.historial_estado   TO rol_sigsm_app;

-- Catálogos y vistas: solo lectura
GRANT SELECT ON sigsm.rol               TO rol_sigsm_app;
GRANT SELECT ON sigsm.tipo_vehiculo     TO rol_sigsm_app;
GRANT SELECT ON sigsm.tipo_elemento     TO rol_sigsm_app;
GRANT SELECT ON sigsm.compatibilidad    TO rol_sigsm_app;
GRANT SELECT ON sigsm.canal_solicitud   TO rol_sigsm_app;
GRANT SELECT ON sigsm.proveedor_externo TO rol_sigsm_app;
GRANT SELECT ON sigsm.estado_traslado   TO rol_sigsm_app;
GRANT SELECT ON sigsm.v_estado_actual_traslado   TO rol_sigsm_app;
GRANT SELECT ON sigsm.v_traslados_activos        TO rol_sigsm_app;
GRANT SELECT ON sigsm.v_documentos_por_categoria TO rol_sigsm_app;

-- Usuarios: cada uno con su rol activo por defecto
CREATE USER 'sigsm_admin'@'localhost' IDENTIFIED BY 'CAMBIAR_clave_admin';
CREATE USER 'sigsm_admin'@'%'         IDENTIFIED BY 'CAMBIAR_clave_admin';
CREATE USER 'sigsm_app'@'localhost'   IDENTIFIED BY 'CAMBIAR_clave_app';
CREATE USER 'sigsm_app'@'%'           IDENTIFIED BY 'CAMBIAR_clave_app';

GRANT rol_sigsm_admin TO 'sigsm_admin'@'localhost', 'sigsm_admin'@'%';
GRANT rol_sigsm_app   TO 'sigsm_app'@'localhost', 'sigsm_app'@'%';
SET DEFAULT ROLE rol_sigsm_admin FOR 'sigsm_admin'@'localhost';
SET DEFAULT ROLE rol_sigsm_admin FOR 'sigsm_admin'@'%';
SET DEFAULT ROLE rol_sigsm_app   FOR 'sigsm_app'@'localhost';
SET DEFAULT ROLE rol_sigsm_app   FOR 'sigsm_app'@'%';

FLUSH PRIVILEGES;
