CREATE TABLE IF NOT EXISTS `{#}nordicbuilder_page_documents` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`document_key` varchar(190) NOT NULL,
	`title` varchar(255) NOT NULL,
	`schema_version` varchar(16) NOT NULL DEFAULT '1.0',
	`page_type` varchar(32) NOT NULL DEFAULT '',
	`editor_mode` varchar(32) NOT NULL DEFAULT '',
	`status` varchar(32) NOT NULL DEFAULT 'draft',
	`schema_json` mediumtext NOT NULL,
	`updated_by` int(10) unsigned NOT NULL DEFAULT '0',
	`created_at` datetime NOT NULL,
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `document_key` (`document_key`),
	KEY `status` (`status`),
	KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{#}nordicbuilder_preset_tokens` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`preset_key` varchar(190) NOT NULL,
	`title` varchar(255) NOT NULL,
	`scope` varchar(32) NOT NULL DEFAULT 'site',
	`schema_version` varchar(16) NOT NULL DEFAULT '1.0',
	`tokens_json` mediumtext NOT NULL,
	`updated_by` int(10) unsigned NOT NULL DEFAULT '0',
	`created_at` datetime NOT NULL,
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `preset_key` (`preset_key`),
	KEY `scope` (`scope`),
	KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{#}nordicbuilder_binding_options` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`binding_key` varchar(190) NOT NULL,
	`page_key` varchar(190) NOT NULL DEFAULT '',
	`title` varchar(255) NOT NULL DEFAULT '',
	`schema_version` varchar(16) NOT NULL DEFAULT '1.0',
	`options_json` mediumtext NOT NULL,
	`updated_by` int(10) unsigned NOT NULL DEFAULT '0',
	`created_at` datetime NOT NULL,
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `binding_key` (`binding_key`),
	KEY `page_key` (`page_key`),
	KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{#}nordicbuilder_page_renders` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`page_key` varchar(190) NOT NULL,
	`title` varchar(255) NOT NULL DEFAULT '',
	`schema_version` varchar(16) NOT NULL DEFAULT '1.0',
	`meta_json` mediumtext NOT NULL,
	`html` mediumtext NOT NULL,
	`content_hash` char(64) NOT NULL DEFAULT '',
	`published_by` int(10) unsigned NOT NULL DEFAULT '0',
	`published_at` datetime NOT NULL,
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `page_key` (`page_key`),
	KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE IF NOT EXISTS `{#}nordicbuilder_migrations` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`version` varchar(64) NOT NULL,
	`description` varchar(255) NOT NULL DEFAULT '',
	`applied_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;