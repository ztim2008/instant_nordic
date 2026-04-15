#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

if (!class_exists('cmsCore')) {
    fwrite(STDERR, "InstantCMS bootstrap failed in nordicblocks smoke\n");
    exit(1);
}

$model = cmsCore::getModel('nordicblocks');
if (!$model) {
    fwrite(STDERR, "NordicBlocks model is not available\n");
    exit(1);
}

$db = cmsDatabase::getInstance();

$blockId = 0;
$createdTitle = 'Smoke Block ' . date('Ymd-His');

try {
    $blockId = (int) $model->createBlock('hero', $createdTitle);
    if ($blockId <= 0) {
        throw new RuntimeException('createBlock failed');
    }

    $props = [
        'heading'  => 'Smoke heading',
        'subheading' => 'Smoke subheading',
        'cta_label'  => 'Smoke CTA',
        'cta_url'    => '#',
    ];

    $model->saveBlock($blockId, $createdTitle . ' Updated', $props);

    $row = $model->getBlockById($blockId);
    if (!$row) {
        throw new RuntimeException('getBlockById failed after save');
    }

    if ((string) ($row['title'] ?? '') !== $createdTitle . ' Updated') {
        throw new RuntimeException('saved title mismatch');
    }

    $savedProps = (array) ($row['props'] ?? []);
    if (($savedProps['heading'] ?? '') !== 'Smoke heading') {
        throw new RuntimeException('saved props mismatch for heading');
    }

    $widgetQuery = $db->query(
        "SELECT `id` FROM `{#}widgets` WHERE `name` = 'nordicblocks_block' AND (`controller` IS NULL OR `controller` = '') LIMIT 1",
        false,
        true
    );

    if (!$widgetQuery || $widgetQuery->num_rows < 1) {
        throw new RuntimeException('widget nordicblocks_block is not registered');
    }

    echo "NordicBlocks flow smoke: OK\n";
    echo " - created block id: {$blockId}\n";

    $model->deleteBlock($blockId);
    exit(0);
} catch (Throwable $exception) {
    if ($blockId > 0) {
        $model->deleteBlock($blockId);
    }
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
