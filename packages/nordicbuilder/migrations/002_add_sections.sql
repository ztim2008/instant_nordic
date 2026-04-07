CREATE TABLE IF NOT EXISTS `{#}nordicbuilder_sections` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `document_key` varchar(190) NOT NULL,
    `section_uid` varchar(64) NOT NULL,
    `section_type` varchar(64) NOT NULL DEFAULT 'content',
    `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
    `payload_json` mediumtext NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `document_section_uid` (`document_key`, `section_uid`),
    KEY `document_key` (`document_key`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
