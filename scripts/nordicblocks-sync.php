#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';
require_once $rootDir . '/packages/nordicblocks/install.php';

if (!class_exists('cmsDatabase') || !class_exists('cmsConfig')) {
    fwrite(STDERR, "InstantCMS bootstrap failed in nordicblocks sync\n");
    exit(1);
}

$apply = in_array('--apply', $argv, true);
$db = cmsDatabase::getInstance();
$prefix = (string) cmsConfig::get('db_prefix');

$tableExists = static function (string $tableName) use ($db): bool {
    $result = $db->query("SHOW TABLES LIKE '%s'", [$tableName], true);
    return (bool) ($result && $result->num_rows > 0);
};

$findWidgetId = static function (string $widgetName) use ($db): ?int {
    $result = $db->query(
        "SELECT `id` FROM `{#}widgets` WHERE `name` = '%s' AND (`controller` IS NULL OR `controller` = '') LIMIT 1",
        [$widgetName],
        true
    );

    if (!$result || $result->num_rows < 1) {
        return null;
    }

    $row = $result->fetch_assoc();
    return isset($row['id']) ? (int) $row['id'] : null;
};

$state = static function () use ($tableExists, $findWidgetId, $prefix): array {
    return [
        'table_nordicblocks_blocks' => $tableExists($prefix . 'nordicblocks_blocks'),
        'widget_nordicblocks_block_id' => $findWidgetId('nordicblocks_block'),
        'widget_nordicblocks_page_id' => $findWidgetId('nordicblocks_page'),
    ];
};

try {
    $before = $state();

    echo "NordicBlocks sync state (before):\n";
    echo json_encode($before, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

    if (!$apply) {
        echo "\nDry-run mode. Use --apply to execute sync.\n";
        exit(0);
    }

    NordicblocksPackageSync::apply($db);

    $after = $state();

    echo "\nNordicBlocks sync state (after):\n";
    echo json_encode($after, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

    echo "\nNordicBlocks sync: OK\n";
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
