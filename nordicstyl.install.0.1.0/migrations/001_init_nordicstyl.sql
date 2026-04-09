-- NordicStyl migrations
-- 001_init_nordicstyl

-- Baseline safety: ensure controller exists (no-op if already present)
INSERT IGNORE INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`)
VALUES ('NordicStyl', 'nordicstyl', 1, '', 'Nordic Builder', 'https://nordic-builder.store', '0.1.0', 1);

-- Baseline schema (no-op if already present)
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
