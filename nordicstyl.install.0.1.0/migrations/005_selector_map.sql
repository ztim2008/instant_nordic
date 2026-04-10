-- NordicStyl: selector map imported from instyler.json (dictionary of elements)

CREATE TABLE IF NOT EXISTS `{#}nordicstyl_selector_map` (
  `uid` char(40) NOT NULL,
  `source` varchar(64) NOT NULL DEFAULT 'instyler.json',
  `source_hash` char(40) NOT NULL DEFAULT '',
  `group_path` varchar(1024) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `selector` varchar(1024) NOT NULL DEFAULT '',
  `ordering` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `source` (`source`),
  KEY `ordering` (`ordering`),
  KEY `title` (`title`(191)),
  KEY `selector` (`selector`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
