#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

if (!class_exists('cmsCore')) {
    fwrite(STDERR, "InstantCMS bootstrap failed in bento_feed smoke\n");
    exit(1);
}

$model = cmsCore::getModel('nordicblocks');
$contentModel = cmsCore::getModel('content');
$widgetsBackendModel = cmsCore::getModel('backend_widgets');
$db = cmsDatabase::getInstance();

if (!$model || !$contentModel || !$widgetsBackendModel || !$db) {
    fwrite(STDERR, "Required model is not available for bento_feed smoke\n");
    exit(1);
}

function bento_smoke_fail($message) {
    throw new RuntimeException($message);
}

function bento_smoke_assert($condition, $message) {
    if (!$condition) {
        bento_smoke_fail($message);
    }
}

function bento_smoke_pick_ctype($contentModel) {
    foreach ((array) $contentModel->getContentTypes() as $ctype) {
        $name = trim((string) ($ctype['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $count = (int) $contentModel->getContentItemsCount($name);
        if ($count >= 3) {
            return [$name, $count];
        }
    }

    return ['', 0];
}

function bento_smoke_find_widget($db) {
    $result = $db->query(
        "SELECT `id`, `title`
         FROM `{#}widgets`
         WHERE `name` = 'nordicblocks_block' AND (`controller` IS NULL OR `controller` = '')
         LIMIT 1",
        [],
        true
    );

    if (!$result || $result->num_rows < 1) {
        return null;
    }

    return $result->fetch_assoc();
}

function bento_smoke_resolve_position($db, $widgetsBackendModel, $templateName) {
    $hasPos = $db->query(
        "SELECT `name` FROM `{#}layout_cols` lc
         INNER JOIN `{#}layout_rows` lr ON lr.id = lc.row_id
         WHERE lr.template = '%s' AND lc.name = 'pos_38'
         LIMIT 1",
        [$templateName],
        true
    );

    if ($hasPos && $hasPos->num_rows > 0) {
        return 'pos_38';
    }

    $rows = $widgetsBackendModel->getLayoutRows($templateName);
    if (is_array($rows)) {
        foreach ($rows as $row) {
            foreach ((array) ($row['positions'] ?? []) as $position) {
                if (!is_string($position) || $position === '' || $position === '_unused' || $position === '_copy') {
                    continue;
                }
                return $position;
            }
        }
    }

    return '';
}

function bento_smoke_fetch($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 20,
            'ignore_errors' => true,
            'header' => "User-Agent: NordicBlocks Bento Feed Smoke\r\n",
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $html = @file_get_contents($url, false, $context);
    return is_string($html) ? $html : '';
}

$blockId = 0;
$bindId = 0;
$bpId = 0;

try {
    [$ctypeName, $ctypeCount] = bento_smoke_pick_ctype($contentModel);
    if ($ctypeName === '') {
        bento_smoke_fail('No content type with at least 3 published records found for bento_feed smoke');
    }

    $title = 'Smoke Bento Feed ' . date('Ymd-His');
    $blockId = (int) $model->createBlock('bento_feed', $title);
    bento_smoke_assert($blockId > 0, 'createBlock failed for bento_feed');

    $block = $model->getBlockById($blockId);
    bento_smoke_assert(is_array($block) && !empty($block['contract']), 'Initial bento_feed contract is missing');

    $contract = (array) $block['contract'];
    $renderHeading = 'Smoke Bento Feed Heading ' . $blockId;
    $limit = min(6, max(3, $ctypeCount));

    $contract['content']['title'] = $renderHeading;
    $contract['content']['subtitle'] = 'Smoke render for managed bento news block with content_list and load_more.';
    $contract['content']['primaryButton']['label'] = 'Все материалы';
    $contract['content']['primaryButton']['url'] = '/news';
    $contract['content']['items'] = [
        [
            'category' => 'Manual fallback',
            'title' => 'Manual fallback title',
            'excerpt' => 'Manual fallback excerpt',
            'linkLabel' => 'Fallback CTA',
            'url' => '/fallback',
        ],
    ];
    $contract['layout']['preset'] = 'editorial_mix';
    $contract['layout']['desktop']['columns'] = 3;
    $contract['layout']['mobile']['columns'] = 1;
    $contract['runtime']['collectionMode'] = 'load_more';
    $contract['runtime']['initialItemsCount'] = 2;
    $contract['runtime']['itemsPerPage'] = 2;
    $contract['runtime']['loadMoreLabel'] = 'Показать ещё';
    $contract['runtime']['showBottomNavigation'] = true;
    $contract['runtime']['visibility']['image'] = true;
    $contract['runtime']['visibility']['category'] = true;
    $contract['runtime']['visibility']['excerpt'] = true;
    $contract['runtime']['visibility']['itemLink'] = true;
    $contract['runtime']['visibility']['date'] = true;
    $contract['runtime']['visibility']['views'] = true;
    $contract['runtime']['visibility']['comments'] = true;
    $contract['runtime']['visibility']['moreLink'] = true;
    $contract['data']['listSource'] = [
        'type' => 'content_list',
        'ctype' => $ctypeName,
        'limit' => $limit,
        'sort' => 'date_pub_desc',
        'map' => [
            'title' => 'title',
            'excerpt' => 'teaser',
            'image' => 'record_image_url',
            'imageAlt' => 'title',
            'category' => 'category.title',
            'categoryUrl' => 'category.url',
            'date' => 'date_pub',
            'views' => 'hits_count',
            'comments' => 'comments_count',
            'url' => 'record_url',
            'ctaLabel' => 'cta_label',
        ],
        'emptyBehavior' => 'fallback',
    ];

    $model->saveBlockContract($blockId, $title, $contract);

    $saved = $model->getBlockById($blockId);
    bento_smoke_assert(is_array($saved), 'getBlockById failed after bento_feed save');

    $savedContract = (array) ($saved['contract'] ?? []);
    bento_smoke_assert(($savedContract['runtime']['collectionMode'] ?? '') === 'load_more', 'collectionMode was not persisted');
    bento_smoke_assert((int) ($savedContract['layout']['desktop']['columns'] ?? 0) === 3, 'desktop columns were not persisted');
    bento_smoke_assert((int) ($savedContract['layout']['mobile']['columns'] ?? 0) === 1, 'mobile columns were not persisted');
    bento_smoke_assert(($savedContract['data']['listSource']['type'] ?? '') === 'content_list', 'listSource was not persisted');
    bento_smoke_assert(($savedContract['data']['listSource']['ctype'] ?? '') === $ctypeName, 'listSource ctype mismatch after save');

    $hydratedBlock = $model->hydrateBlockForRender($saved, ['mode' => 'smoke']);
    $hydratedContract = (array) ($hydratedBlock['contract'] ?? []);
    $items = is_array($hydratedContract['content']['items'] ?? null) ? $hydratedContract['content']['items'] : [];

    bento_smoke_assert(count($items) >= 3, 'Hydrated bento_feed items are insufficient for load_more smoke');
    bento_smoke_assert(($items[0]['title'] ?? '') !== 'Manual fallback title', 'content_list adapter did not replace fallback items');
    bento_smoke_assert(($hydratedContract['runtime']['adapter']['source'] ?? '') === 'content_list', 'Runtime adapter source should be content_list');

    $renderFile = $rootDir . '/system/controllers/nordicblocks/blocks/bento_feed/render.php';
    bento_smoke_assert(is_file($renderFile), 'bento_feed render file is missing');

    $props = is_array($hydratedBlock['props'] ?? null) ? $hydratedBlock['props'] : [];
    $block_contract = $hydratedContract;
    $block_uid = 'smoke-bento-feed-' . $blockId;
    $block_type = 'bento_feed';

    ob_start();
    require $renderFile;
    $html = (string) ob_get_clean();

    bento_smoke_assert($html !== '', 'bento_feed render returned empty HTML');
    bento_smoke_assert(strpos($html, 'nb-bento-feed__grid') !== false, 'bento_feed render does not contain grid markup');
    bento_smoke_assert(strpos($html, 'data-role="bento-more"') !== false, 'bento_feed render does not contain load_more control');
    bento_smoke_assert(strpos($html, 'data-nb-collection-mode="load_more"') !== false, 'bento_feed render does not expose collection mode marker');
    bento_smoke_assert(strpos($html, htmlspecialchars($renderHeading, ENT_QUOTES, 'UTF-8')) !== false, 'bento_feed render does not contain heading');

    $widget = bento_smoke_find_widget($db);
    bento_smoke_assert(is_array($widget) && !empty($widget['id']), 'Widget nordicblocks_block is not registered');

    $templateName = (string) cmsConfig::get('template');
    $position = bento_smoke_resolve_position($db, $widgetsBackendModel, $templateName);
    bento_smoke_assert($position !== '', 'No available widget position found for bento_feed smoke');

    $binding = $widgetsBackendModel->addWidgetBinding($widget, 1, $position, $templateName);
    bento_smoke_assert(is_array($binding) && !empty($binding['id']), 'Failed to create widget binding for bento_feed smoke');

    $bindId = (int) $binding['id'];
    $bpId = (int) ($binding['bp_id'] ?? 0);
    $widgetBind = $widgetsBackendModel->getWidgetBinding($bindId);
    bento_smoke_assert(is_array($widgetBind), 'Failed to load widget binding after creation');

    $widgetBind['options'] = ['block_id' => $blockId];
    $widgetsBackendModel->updateWidgetBinding($bindId, $widgetBind);

    $homepageHtml = bento_smoke_fetch((string) cmsConfig::get('host') . '/');
    bento_smoke_assert($homepageHtml !== '', 'Failed to fetch homepage during public widget smoke');
    bento_smoke_assert(strpos($homepageHtml, 'Fatal error') === false, 'Homepage contains Fatal error during bento_feed smoke');
    bento_smoke_assert(strpos($homepageHtml, 'Warning') === false, 'Homepage contains Warning during bento_feed smoke');
    bento_smoke_assert(strpos($homepageHtml, 'Notice') === false, 'Homepage contains Notice during bento_feed smoke');
    bento_smoke_assert(strpos($homepageHtml, htmlspecialchars($renderHeading, ENT_QUOTES, 'UTF-8')) !== false, 'Homepage does not contain bento_feed heading after placement');
    bento_smoke_assert(strpos($homepageHtml, 'data-nb-block="bento_feed"') !== false, 'Homepage does not contain bento_feed block marker');

    echo "Bento Feed content_list smoke: OK\n";
    echo " - block_id: {$blockId}\n";
    echo " - bind_id: {$bindId}\n";
    echo " - bp_id: {$bpId}\n";
    echo " - ctype: {$ctypeName}\n";
    echo ' - items: ' . count($items) . "\n";
    echo " - position: {$position}\n";
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
} finally {
    if ($bindId > 0) {
        $widgetsBackendModel->deleteWidgetBinding($bindId);
    } elseif ($bpId > 0) {
        $widgetsBackendModel->deleteWidgetPageBind($bpId);
    }

    if ($blockId > 0) {
        $model->deleteBlock($blockId);
    }
}