-- NordicStyl: persisted builder layout state by template and target uri

CREATE TABLE IF NOT EXISTS `{#}nordicstyl_layout_state` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(64) NOT NULL,
  `target_uri` varchar(191) NOT NULL DEFAULT '/',
  `layout_state` mediumtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_uri` (`template`, `target_uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;