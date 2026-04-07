<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$installSql = $rootDir . '/packages/nordicbuilder/install.sql';
$migrationsDir = $rootDir . '/packages/nordicbuilder/migrations';

require $rootDir . '/bootstrap.php';

if (!class_exists('cmsDatabase') || !class_exists('cmsConfig')) {
    fwrite(STDERR, "InstantCMS bootstrap failed in DB smoke runner\n");
    exit(1);
}

$db = cmsDatabase::getInstance();
$configPrefix = (string) cmsConfig::get('db_prefix');
$tablePrefix = $configPrefix . 'nbsmoke_' . time() . '_';

$smokeTables = [
    $tablePrefix . 'nordicbuilder_page_documents',
    $tablePrefix . 'nordicbuilder_preset_tokens',
    $tablePrefix . 'nordicbuilder_binding_options',
    $tablePrefix . 'nordicbuilder_page_renders',
    $tablePrefix . 'nordicbuilder_sections',
    $tablePrefix . 'nordicbuilder_migrations',
];

$migrationFiles = glob($migrationsDir . '/*.sql') ?: [];
natsort($migrationFiles);
$migrationFiles = array_values($migrationFiles);

if (!$migrationFiles) {
    fwrite(STDERR, "No migrations found in {$migrationsDir}\n");
    exit(1);
}

$runSqlWithPrefix = static function (string $sourceFile) use ($db, $tablePrefix): void {
    $sql = file_get_contents($sourceFile);
    if ($sql === false) {
        throw new RuntimeException("Unable to read SQL file: {$sourceFile}");
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'nbsmoke_');
    if ($tmpFile === false) {
        throw new RuntimeException('Unable to create temporary SQL file');
    }

    $patched = str_replace('{#}', $tablePrefix, $sql);
    if (file_put_contents($tmpFile, $patched) === false) {
        @unlink($tmpFile);
        throw new RuntimeException("Unable to write temporary SQL file: {$tmpFile}");
    }

    $ok = $db->importDump($tmpFile);
    @unlink($tmpFile);

    if (!$ok) {
        throw new RuntimeException("SQL import failed for: {$sourceFile}");
    }
};

$tableExists = static function (string $tableName) use ($db): bool {
    $result = $db->query("SHOW TABLES LIKE '%s'", [$tableName], true);
    if (!$result) {
        return false;
    }

    return $result->num_rows > 0;
};

$fetchCount = static function (string $sql, array $params = []) use ($db): int {
    $result = $db->query($sql, $params, true);
    if (!$result) {
        throw new RuntimeException("Count query failed: {$sql}");
    }

    $row = $result->fetch_assoc();
    return (int) ($row['cnt'] ?? 0);
};

$applyMigrations = static function () use ($db, $migrationFiles, $runSqlWithPrefix, $tablePrefix, $fetchCount): int {
    $applied = 0;
    $migrationTable = $tablePrefix . 'nordicbuilder_migrations';

    foreach ($migrationFiles as $file) {
        $version = basename($file, '.sql');
        $description = str_replace('_', ' ', preg_replace('/^\d+_/', '', $version) ?? $version);

        $already = $fetchCount(
            "SELECT COUNT(*) AS cnt FROM `{$migrationTable}` WHERE version = '%s'",
            [$version]
        );

        if ($already > 0) {
            continue;
        }

        $runSqlWithPrefix($file);

        $inserted = $db->query(
            "INSERT INTO `{$migrationTable}` (`version`, `description`, `applied_at`) VALUES ('%s', '%s', NOW())",
            [$version, $description],
            true
        );

        if (!$inserted) {
            throw new RuntimeException("Failed to record migration: {$version}");
        }

        $applied++;
    }

    return $applied;
};

$cleanup = static function () use ($db, $smokeTables): void {
    foreach ($smokeTables as $table) {
        $db->query("DROP TABLE IF EXISTS `{$table}`", false, true);
    }
};

try {
    $runSqlWithPrefix($installSql);

    $existing = 0;
    foreach ($smokeTables as $tableName) {
        if ($tableExists($tableName)) {
            $existing++;
        }
    }

    if ($existing < 5) {
        throw new RuntimeException('Install smoke failed: expected nordicbuilder smoke tables were not created');
    }

    $firstApply = $applyMigrations();
    $secondApply = $applyMigrations();

    $migrationRows = $fetchCount(
        "SELECT COUNT(*) AS cnt FROM `{$tablePrefix}nordicbuilder_migrations`"
    );

    if ($migrationRows !== count($migrationFiles)) {
        throw new RuntimeException(
            'Update smoke failed: expected ' . count($migrationFiles) . ' migration rows, got ' . $migrationRows
        );
    }

    if ($secondApply !== 0) {
        throw new RuntimeException('Update smoke failed: migrations are not idempotent');
    }

    echo "DB migration smoke: OK\n";
    echo " - temp prefix: {$tablePrefix}\n";
    echo " - first apply count: {$firstApply}\n";
    echo " - second apply count: {$secondApply}\n";

    $cleanup();
    exit(0);
} catch (Throwable $e) {
    $cleanup();
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
