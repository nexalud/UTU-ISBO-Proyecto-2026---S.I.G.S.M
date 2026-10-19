-- =====================================================================
-- S.I.G.S.M. - Cambios en la base para la tercera entrega
-- Se puede correr mas de una vez sin romper nada (usa IF NOT EXISTS).
--   XAMPP:    phpMyAdmin > base sigsm > pestaña SQL > pegar y Continuar
--   Servidor: sudo docker exec -i sigsm_db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" sigsm' < actualizacion_tercera_entrega.sql
--             (solo en el master: la replica lo copia sola)
-- =====================================================================

SET NAMES utf8mb4;

-- 1) Preguntas obligatorias u opcionales en las encuestas
ALTER TABLE pregunta ADD COLUMN IF NOT EXISTS obligatoria TINYINT(1) NOT NULL DEFAULT 1;

-- 2) Enlaces para crear o restablecer la contraseña (llegan por correo).
--    Se guarda el hash SHA-256 del codigo, nunca el codigo en si.
CREATE TABLE IF NOT EXISTS recuperacion_contrasena (
    id_recuperacion INT          NOT NULL AUTO_INCREMENT,
    id_usuario      INT          NOT NULL,
    token_hash      CHAR(64)     NOT NULL,
    tipo            ENUM('restablecer', 'bienvenida') NOT NULL DEFAULT 'restablecer',
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expira    DATETIME     NOT NULL,
    fecha_uso       DATETIME     NULL,
    CONSTRAINT pk_recuperacion PRIMARY KEY (id_recuperacion),
    CONSTRAINT uq_recuperacion_token UNIQUE (token_hash),
    CONSTRAINT fk_recuperacion_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_recuperacion_usuario (id_usuario, fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
