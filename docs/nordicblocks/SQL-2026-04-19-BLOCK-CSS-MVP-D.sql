CREATE TABLE IF NOT EXISTS `cms_nordicblocks_block_css_revision` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_css_id` BIGINT UNSIGNED NOT NULL,
    `version` INT UNSIGNED NOT NULL,
    `css_text` MEDIUMTEXT NOT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_block_css_id` (`block_css_id`),
    KEY `idx_block_css_version` (`block_css_id`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cms_nordicblocks_block_css_revision` (`block_css_id`, `version`, `css_text`, `created_by`, `created_at`)
SELECT
        bc.`id`,
        bc.`version`,
        bc.`css_text`,
        bc.`updated_by`,
        COALESCE(bc.`updated_at`, bc.`created_at`, NOW())
FROM `cms_nordicblocks_block_css` bc
LEFT JOIN `cms_nordicblocks_block_css_revision` rev
        ON rev.`block_css_id` = bc.`id`
     AND rev.`version` = bc.`version`
WHERE rev.`id` IS NULL
    AND COALESCE(bc.`version`, 0) > 0;