CREATE TABLE IF NOT EXISTS `{#}landingbuilder_pages` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `title` varchar(255) NOT NULL,
    `status` varchar(32) NOT NULL DEFAULT 'draft',
    `page_mode` varchar(32) NOT NULL DEFAULT 'full_takeover',
    `template` varchar(32) NOT NULL DEFAULT 'nordic',
    `current_version_id` int(10) unsigned DEFAULT NULL,
    `schema_json` mediumtext,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    KEY `status` (`status`),
    KEY `page_mode` (`page_mode`),
    KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{#}landingbuilder_page_versions` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `page_id` int(10) unsigned NOT NULL,
    `version_note` varchar(255) NOT NULL DEFAULT '',
    `schema_json` mediumtext NOT NULL,
    `created_by` int(10) unsigned NOT NULL DEFAULT '0',
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `page_id` (`page_id`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{#}landingbuilder_page_widgets` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `page_id` int(10) unsigned NOT NULL,
    `node_uid` varchar(64) NOT NULL,
    `widget_id` int(10) unsigned NOT NULL DEFAULT '0',
    `widget_name` varchar(64) NOT NULL DEFAULT '',
    `widget_controller` varchar(64) DEFAULT NULL,
    `widget_title` varchar(255) NOT NULL DEFAULT '',
    `options` mediumtext,
    `device_visibility` mediumtext,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `page_node_uid` (`page_id`, `node_uid`),
    KEY `widget_id` (`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `{#}landingbuilder_pages` (`name`, `title`, `status`, `page_mode`, `template`, `created_at`, `updated_at`) VALUES
('homepage', 'Главная страница', 'draft', 'full_takeover', 'nordic', NOW(), NOW()),
('ads-category', 'Категория Объявлений', 'prototype', 'hybrid_overlay', 'nordic', NOW(), NOW()),
('profile-cover', 'Профиль пользователя', 'idea', 'zone_injection', 'nordic', NOW(), NOW());