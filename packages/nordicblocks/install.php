<?php

function install_package() {
    return true;
}

function after_install_package() {

    try {
        NordicblocksPackageSync::apply(cmsDatabase::getInstance());
    } catch (Throwable $exception) {
        return 'NordicBlocks post-install sync failed: ' . $exception->getMessage();
    }

    return true;
}

class NordicblocksPackageSync {

    private static function getTableCharset() {
        $charset = (string) cmsConfig::get('db_charset');

        if ($charset === '') {
            return 'utf8mb4';
        }

        return preg_match('/^[a-zA-Z0-9_]+$/', $charset) ? $charset : 'utf8mb4';
    }

    public static function apply($db) {
        self::ensureBlocksTable($db);
        self::ensureWidget($db, 'nordicblocks_block', 'NordicBlocks: блок');
        self::ensureWidget($db, 'nordicblocks_page', 'NordicBlocks: страница блоков');
        return true;
    }

    private static function ensureBlocksTable($db) {

        $charset = self::getTableCharset();

        $db->query(
            "CREATE TABLE IF NOT EXISTS `{#}nordicblocks_blocks` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `type` varchar(64) NOT NULL,
                `title` varchar(255) NOT NULL DEFAULT '',
                `props_json` mediumtext,
                `status` varchar(16) NOT NULL DEFAULT 'active',
                `created_at` datetime,
                `updated_at` datetime,
                PRIMARY KEY (`id`),
                KEY `type` (`type`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset}"
        );
    }

    private static function ensureWidget($db, $name, $title) {

        $exists = $db->query(
            "SELECT `id` FROM `{#}widgets` WHERE `name` = '%s' AND (`controller` IS NULL OR `controller` = '') LIMIT 1",
            [$name],
            true
        );

        if ($exists && $exists->num_rows > 0) {
            return;
        }

        $db->query(
            "INSERT INTO `{#}widgets`
                (`controller`, `name`, `title`, `author`, `url`, `version`, `is_external`, `files`, `addon_id`, `image_hint_path`)
             VALUES (NULL, '%s', '%s', NULL, NULL, '1.0.0', 1, NULL, NULL, NULL)",
            [$name, $title],
            true
        );
    }
}
