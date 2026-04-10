INSERT IGNORE INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`)
VALUES ('NordicStyl', 'nordicstyl', 1, '', 'Nordic Builder', 'https://nordic-builder.store', '0.1.0', 1);

CREATE TABLE IF NOT EXISTS `{#}nordicstyl_styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `path` varchar(1024) DEFAULT NULL,
  `styles` text,
  `custom` text,
  `is_enabled` tinyint(3) unsigned DEFAULT '1',
  `is_important` tinyint(3) unsigned DEFAULT '0',
  `ordering` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `is_enabled` (`is_enabled`),
  KEY `ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{#}nordicstyl_layout_revisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(64) NOT NULL,
  `target_uri` varchar(191) NOT NULL DEFAULT '/',
  `revision_type` varchar(32) NOT NULL DEFAULT 'live_save',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `layout_state` mediumtext NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `template_uri` (`template`, `target_uri`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
