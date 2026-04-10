-- NordicStyl: history of applied layout revisions for live-save rollback

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