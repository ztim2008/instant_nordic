SET @table_name := 'cms_nordicblocks_block_css';

SET @add_published_css_text := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
    )
    AND NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
          AND COLUMN_NAME = 'published_css_text'
    ),
    'ALTER TABLE `cms_nordicblocks_block_css` ADD COLUMN `published_css_text` MEDIUMTEXT NULL AFTER `css_text`',
    'SELECT 1'
);
PREPARE stmt FROM @add_published_css_text;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_published_version := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
    )
    AND NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
          AND COLUMN_NAME = 'published_version'
    ),
    'ALTER TABLE `cms_nordicblocks_block_css` ADD COLUMN `published_version` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `version`',
    'SELECT 1'
);
PREPARE stmt FROM @add_published_version;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_published_by := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
    )
    AND NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
          AND COLUMN_NAME = 'published_by'
    ),
    'ALTER TABLE `cms_nordicblocks_block_css` ADD COLUMN `published_by` INT UNSIGNED DEFAULT NULL AFTER `updated_by`',
    'SELECT 1'
);
PREPARE stmt FROM @add_published_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_published_at := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
    )
    AND NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = @table_name
          AND COLUMN_NAME = 'published_at'
    ),
    'ALTER TABLE `cms_nordicblocks_block_css` ADD COLUMN `published_at` DATETIME DEFAULT NULL AFTER `updated_at`',
    'SELECT 1'
);
PREPARE stmt FROM @add_published_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `cms_nordicblocks_block_css`
SET
    `published_css_text` = `css_text`,
    `published_version`  = CASE
        WHEN TRIM(COALESCE(`css_text`, '')) <> '' THEN GREATEST(`version`, 1)
        ELSE `published_version`
    END,
    `published_by`       = COALESCE(`published_by`, `updated_by`),
    `published_at`       = COALESCE(`published_at`, `updated_at`)
WHERE COALESCE(`published_version`, 0) = 0
  AND TRIM(COALESCE(`css_text`, '')) <> '';