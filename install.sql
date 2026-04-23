-- NordicBlocks: таблица страниц
CREATE TABLE IF NOT EXISTS `{#}nordicblocks_pages` (
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
    `key`         varchar(64)  NOT NULL,
    `title`       varchar(255) NOT NULL DEFAULT '',
    `status`      varchar(16)  NOT NULL DEFAULT 'draft',
    `blocks_json` mediumtext,
    `created_at`  datetime     NOT NULL,
    `updated_at`  datetime     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `key` (`key`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- NordicBlocks: библиотека standalone блоков (новая архитектура)
CREATE TABLE IF NOT EXISTS `{#}nordicblocks_blocks` (
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `type`       varchar(64)  NOT NULL,
    `title`      varchar(255) NOT NULL DEFAULT '',
    `props_json` mediumtext,
    `status`     varchar(16)  NOT NULL DEFAULT 'active',
    `created_at` datetime,
    `updated_at` datetime,
    PRIMARY KEY (`id`),
    KEY `type` (`type`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- NordicBlocks: глобальные настройки дизайн‑системы (одна строка)
CREATE TABLE IF NOT EXISTS `{#}nordicblocks_design` (
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
    `tokens_json` mediumtext,
    `updated_at`  datetime     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- NordicBlocks: кэш рендера блоков (SSR‑фрагменты)
CREATE TABLE IF NOT EXISTS `{#}nordicblocks_cache` (
    `cache_key`  varchar(128) NOT NULL,
    `html`       mediumtext   NOT NULL,
    `expires_at` datetime     NOT NULL,
    PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- NordicBlocks: регистрация виджета для размещения одиночного блока
INSERT INTO `{#}widgets`
    (`controller`, `name`, `title`, `author`, `url`, `version`, `is_external`, `files`, `addon_id`, `image_hint_path`)
SELECT
    NULL,
    'nordicblocks_block',
    'NordicBlocks: блок',
    NULL,
    NULL,
    '1.0.0',
    1,
    NULL,
    NULL,
    NULL
WHERE NOT EXISTS (
    SELECT 1
    FROM `{#}widgets`
    WHERE `name` = 'nordicblocks_block' AND (`controller` IS NULL OR `controller` = '')
    LIMIT 1
);

-- NordicBlocks: legacy виджет страниц сохраняем для обратной совместимости
INSERT INTO `{#}widgets`
    (`controller`, `name`, `title`, `author`, `url`, `version`, `is_external`, `files`, `addon_id`, `image_hint_path`)
SELECT
    NULL,
    'nordicblocks_page',
    'NordicBlocks: страница блоков',
    NULL,
    NULL,
    '1.0.0',
    1,
    NULL,
    NULL,
    NULL
WHERE NOT EXISTS (
    SELECT 1
    FROM `{#}widgets`
    WHERE `name` = 'nordicblocks_page' AND (`controller` IS NULL OR `controller` = '')
    LIMIT 1
);
