-- Gestor de clientes · esquema MySQL 5.x / MariaDB

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario       VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    creado_en     DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS clientes (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    razon_social   VARCHAR(150) NOT NULL,
    cuit           VARCHAR(13)  NULL,
    email          VARCHAR(150) NULL,
    telefono       VARCHAR(30)  NULL,
    ciudad         VARCHAR(80)  NULL,
    estado         VARCHAR(10)  NOT NULL DEFAULT 'activo',
    notas          TEXT         NULL,
    creado_por     INT UNSIGNED NULL,
    creado_en      DATETIME     NOT NULL,
    actualizado_en DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cuit (cuit),
    KEY idx_razon_social (razon_social),
    KEY idx_estado (estado),
    CONSTRAINT fk_clientes_usuario FOREIGN KEY (creado_por) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Usuario de demostración: demo / demo1234 (cambiar en producción)
INSERT INTO usuarios (usuario, password_hash, nombre, creado_en)
VALUES ('demo', '$2y$10$Y00RER5NJ02pNHY887InteBXuN8pfH28fx0rRIg.qMnbJ7Qi4wD2e', 'Usuario Demo', NOW());
