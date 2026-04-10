-- NordicStyl: picker tokens stored in DB to work reliably in iframe/frontend

CREATE TABLE IF NOT EXISTS `{#}nordicstyl_picker_tokens` (
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`token`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
