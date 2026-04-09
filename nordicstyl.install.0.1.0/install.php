<?php

function install_package() {
    // Core installer imports install.sql and registers controller from manifest.
    return true;
}

function after_install_package() {

    try {
        NordicstylPackageMigrations::apply(
            cmsDatabase::getInstance(),
            __DIR__ . '/migrations'
        );
    } catch (Throwable $exception) {
        return 'Nordicstyl migrations failed: ' . $exception->getMessage();
    }

    return true;
}

class NordicstylPackageMigrations {

    public static function apply($db, $migrations_dir) {

        self::ensureMigrationTable($db);

        if (!is_dir($migrations_dir)) {
            return true;
        }

        $files = glob(rtrim($migrations_dir, '/') . '/*.sql') ?: [];
        sort($files, SORT_NATURAL);

        foreach ($files as $file) {

            $version = pathinfo($file, PATHINFO_FILENAME);
            if ($version === '') {
                continue;
            }

            if (self::isApplied($db, $version)) {
                continue;
            }

            $imported = $db->importDump($file);
            if ($imported === false) {
                return 'Failed to import migration: ' . basename($file);
            }

            $description = self::buildDescriptionFromFilename($version);
            $db->query(
                "INSERT INTO `{#}nordicstyl_migrations` (`version`, `description`, `applied_at`) VALUES ('%s', '%s', NOW())",
                [$version, $description]
            );
        }

        return true;
    }

    protected static function ensureMigrationTable($db) {

        $db->query(
            "CREATE TABLE IF NOT EXISTS `{#}nordicstyl_migrations` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `version` varchar(64) NOT NULL,
                `description` varchar(255) NOT NULL DEFAULT '',
                `applied_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `version` (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
    }

    protected static function isApplied($db, $version) {

        $escaped = $db->escape($version);
        $where = "version = '" . $escaped . "'";

        return (int) $db->getRowsCount('nordicstyl_migrations', $where) > 0;
    }

    protected static function buildDescriptionFromFilename($version) {

        $parts = explode('_', $version, 2);
        if (count($parts) < 2) {
            return $version;
        }

        return str_replace('_', ' ', $parts[1]);
    }
}
