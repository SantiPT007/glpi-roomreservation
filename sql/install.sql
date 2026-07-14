-- Asset "Sala": campos especificos da sala (reunioes, formacao, etc.).
-- O nome da tabela tem de bater certo com o derivado da classe
-- GlpiPlugin\Roomreservation\Room -> glpi_plugin_roomreservation_rooms
CREATE TABLE IF NOT EXISTS `glpi_plugin_roomreservation_rooms` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entities_id`   INT UNSIGNED NOT NULL DEFAULT 0,
    `is_recursive`  TINYINT(1)   NOT NULL DEFAULT 0,
    `name`          VARCHAR(255) DEFAULT NULL,            -- nome/etiqueta da sala (ex.: Sala de Reuniões Grande)
    `building`      VARCHAR(255) DEFAULT NULL,            -- edificio
    `floor`         VARCHAR(64)  DEFAULT NULL,            -- piso (ex.: 2, R/C, -1)
    `capacity`      SMALLINT     DEFAULT NULL,            -- lotacao (numero de pessoas)
    `room_number`   VARCHAR(32)  NOT NULL,                -- numero da sala (obrigatorio)
    `locations_id`  INT UNSIGNED NOT NULL DEFAULT 0,      -- localizacao (dropdown nativo); exigida pela lista de reservas nativa
    `comment`       TEXT         DEFAULT NULL,
    `is_deleted`    TINYINT(1)   NOT NULL DEFAULT 0,
    `date_creation` TIMESTAMP    NULL DEFAULT NULL,
    `date_mod`      TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `entities_id`   (`entities_id`),
    KEY `locations_id`  (`locations_id`),
    KEY `is_deleted`    (`is_deleted`),
    UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
