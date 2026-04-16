#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

if (!class_exists('cmsCore')) {
    fwrite(STDERR, "InstantCMS bootstrap failed in nordicblocks FAQ smoke\n");
    exit(1);
}

require_once $rootDir . '/system/controllers/nordicblocks/libs/BlockContractNormalizer.php';

$model = cmsCore::getModel('nordicblocks');
$contentModel = cmsCore::getModel('content');

if (!$model) {
    fwrite(STDERR, "NordicBlocks model is not available\n");
    exit(1);
}

if (!$contentModel || !method_exists($contentModel, 'getContentTypes')) {
    fwrite(STDERR, "Content model is not available\n");
    exit(1);
}

$blockId = 0;

try {
    $ctypeName = '';
    foreach ((array) $contentModel->getContentTypes() as $ctype) {
        $name = (string) ($ctype['name'] ?? '');
        if ($name === '') {
            continue;
        }

        $count = (int) $contentModel->getContentItemsCount($name);
        if ($count > 0) {
            $ctypeName = $name;
            break;
        }
    }

    if ($ctypeName === '') {
        throw new RuntimeException('No content type with items found for FAQ adapter smoke');
    }

    $title = 'Smoke FAQ ' . date('Ymd-His');
    $blockId = (int) $model->createBlock('faq', $title);
    if ($blockId <= 0) {
        throw new RuntimeException('createBlock failed for FAQ');
    }

    $contract = NordicblocksBlockContractNormalizer::normalize([
        'id'     => $blockId,
        'type'   => 'faq',
        'title'  => $title,
        'status' => 'active',
        'props'  => [
            'eyebrow' => 'FAQ',
            'heading' => 'Smoke FAQ heading',
            'intro'   => 'Smoke intro',
            'items'   => [
                [
                    'question' => 'Manual fallback question',
                    'answer'   => 'Manual fallback answer',
                ],
            ],
        ],
    ]);

    $contract['data']['listSource'] = [
        'type'          => 'content_list',
        'ctype'         => $ctypeName,
        'limit'         => 3,
        'sort'          => 'date_pub_desc',
        'map'           => [
            'question' => 'title',
            'answer'   => 'date_pub',
        ],
        'emptyBehavior' => 'fallback',
    ];

    $model->saveBlockContract($blockId, $title, $contract);

    $saved = $model->getBlockById($blockId);
    if (!$saved) {
        throw new RuntimeException('getBlockById failed after FAQ save');
    }

    $savedContract = (array) ($saved['contract'] ?? []);
    if (($savedContract['data']['listSource']['type'] ?? '') !== 'content_list') {
        throw new RuntimeException('FAQ listSource was not persisted');
    }

    if (($savedContract['data']['listSource']['ctype'] ?? '') !== $ctypeName) {
        throw new RuntimeException('FAQ listSource ctype mismatch after save');
    }

    $hydrated = $model->hydrateBlockForRender($saved, ['mode' => 'smoke']);
    $hydratedContract = (array) ($hydrated['contract'] ?? []);
    $items = is_array($hydratedContract['content']['items'] ?? null) ? $hydratedContract['content']['items'] : [];

    if (!$items) {
        throw new RuntimeException('FAQ adapter returned empty items');
    }

    if (($items[0]['question'] ?? '') === 'Manual fallback question') {
        throw new RuntimeException('FAQ adapter did not replace manual fallback items');
    }

    $renderFile = $rootDir . '/system/controllers/nordicblocks/blocks/faq/render.php';
    if (!is_file($renderFile)) {
        throw new RuntimeException('FAQ render file is missing');
    }

    $props = isset($hydrated['props']) && is_array($hydrated['props']) ? $hydrated['props'] : [];
    $block_contract = $hydratedContract;
    $block_uid = 'smoke-faq';
    $block_type = 'faq';

    ob_start();
    require $renderFile;
    $html = (string) ob_get_clean();

    if (strpos($html, 'nb-faq__list') === false) {
        throw new RuntimeException('FAQ render output does not contain FAQ list markup');
    }

    echo "FAQ content_list smoke: OK\n";
    echo " - ctype: {$ctypeName}\n";
    echo ' - items: ' . count($items) . "\n";
    echo ' - first question: ' . (string) ($items[0]['question'] ?? '') . "\n";

    $model->deleteBlock($blockId);
    exit(0);
} catch (Throwable $exception) {
    if ($blockId > 0) {
        $model->deleteBlock($blockId);
    }

    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}