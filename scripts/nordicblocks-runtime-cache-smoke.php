#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

function smoke_fail(string $message, int $code = 1): void {
    fwrite(STDERR, $message . "\n");
    exit($code);
}

function smoke_assert(bool $condition, string $message): void {
    if (!$condition) {
        smoke_fail($message);
    }
}

$model = cmsCore::getModel('nordicblocks');
$contentModel = cmsCore::getModel('content');

if (!$model || !$contentModel || !method_exists($contentModel, 'getContentTypes')) {
    smoke_fail('Не удалось поднять модели для runtime cache smoke');
}

$heroBlockId = 0;
$faqBlockId = 0;
$manualBlockId = 0;

try {
    $sampleCtype = '';
    $sampleItems = [];

    foreach ((array) $contentModel->getContentTypes() as $ctype) {
        $ctypeName = trim((string) ($ctype['name'] ?? ''));
        if ($ctypeName === '') {
            continue;
        }

        $probeModel = cmsCore::getModel('content');
        $probeModel->orderBy('date_pub', 'desc');
        $probeModel->limit(3);
        $items = $probeModel->getContentItems($ctypeName);

        if (!is_array($items) || count($items) < 2) {
            continue;
        }

        $first = reset($items);
        $second = next($items);
        if (!is_array($first) || !is_array($second) || empty($first['id']) || empty($second['id'])) {
            continue;
        }

        $sampleCtype = $ctypeName;
        $sampleItems = [$first, $second];
        break;
    }

    if ($sampleCtype === '' || count($sampleItems) < 2) {
        throw new RuntimeException('Не найден content type минимум с двумя записями для cache smoke');
    }

    $heroBlockId = (int) $model->createBlock('hero', 'Smoke Hero current context');
    if ($heroBlockId <= 0) {
        throw new RuntimeException('Не удалось создать hero-блок для cache smoke');
    }

    $heroBlock = $model->getBlockById($heroBlockId);
    $heroContract = (array) ($heroBlock['contract'] ?? []);
    $heroContract['content']['title'] = 'Manual title';
    $heroContract['data']['source'] = [
        'type' => 'content_item',
        'ctype' => $sampleCtype,
        'resolver' => [
            'mode' => 'current',
        ],
    ];
    $heroContract['data']['bindings']['title']['field'] = 'title';
    $model->saveBlockContract($heroBlockId, 'Smoke Hero current context', $heroContract);

    $baseHero = $model->getBlockById($heroBlockId);

    $heroContextA = [
        'mode'            => 'legacy_view',
        'surface'         => 'legacy_view',
        'page_id'         => 901,
        'uid'             => 'hero_a',
        'current_ctype'   => $sampleCtype,
        'current_item_id' => (int) $sampleItems[0]['id'],
    ];
    $heroContextB = [
        'mode'            => 'legacy_view',
        'surface'         => 'legacy_view',
        'page_id'         => 901,
        'uid'             => 'hero_a',
        'current_ctype'   => $sampleCtype,
        'current_item_id' => (int) $sampleItems[1]['id'],
    ];

    $heroA = $model->hydrateBlockForRender($baseHero, $heroContextA);
    $heroB = $model->hydrateBlockForRender($baseHero, $heroContextB);
    $profileA = $model->buildRenderCacheProfile($heroA, $heroContextA);
    $profileB = $model->buildRenderCacheProfile($heroB, $heroContextB);

    smoke_assert(!empty($profileA['cacheEligible']), 'Hero current context A не получил cache profile');
    smoke_assert(!empty($profileB['cacheEligible']), 'Hero current context B не получил cache profile');
    smoke_assert(($profileA['cacheKey'] ?? '') !== ($profileB['cacheKey'] ?? ''), 'Cache key для двух current-item контекстов совпал');
    smoke_assert(
        (($profileA['adapterContext']['resultIdentityHash'] ?? '') !== ($profileB['adapterContext']['resultIdentityHash'] ?? '')),
        'Result identity hash для двух current-item контекстов совпал'
    );

    $faqBlockId = (int) $model->createBlock('faq', 'Smoke FAQ cache');
    if ($faqBlockId <= 0) {
        throw new RuntimeException('Не удалось создать faq-блок для cache smoke');
    }

    $faqBlock = $model->getBlockById($faqBlockId);
    $faqContract = (array) ($faqBlock['contract'] ?? []);
    $faqContract['data']['listSource'] = [
        'type'          => 'content_list',
        'ctype'         => $sampleCtype,
        'limit'         => 3,
        'sort'          => 'date_pub_desc',
        'map'           => [
            'question' => 'title',
            'answer'   => 'date_pub',
        ],
        'emptyBehavior' => 'fallback',
    ];
    $model->saveBlockContract($faqBlockId, 'Smoke FAQ cache', $faqContract);

    $faqHydrated = $model->hydrateBlockForRender($model->getBlockById($faqBlockId), [
        'mode'    => 'widget',
        'surface' => 'widget',
    ]);
    $faqProfile = $model->buildRenderCacheProfile($faqHydrated, [
        'mode'    => 'widget',
        'surface' => 'widget',
    ]);

    smoke_assert(!empty($faqProfile['cacheEligible']), 'FAQ content_list не получил cache profile');
    smoke_assert(($faqProfile['adapterContext']['sourceType'] ?? '') === 'content_list', 'FAQ cache profile потерял sourceType=content_list');
    smoke_assert(($faqProfile['adapterContext']['resultIdentityHash'] ?? '') !== '', 'FAQ cache profile не собрал result identity hash');

    $manualBlockId = (int) $model->createBlock('hero', 'Smoke Hero manual');
    if ($manualBlockId <= 0) {
        throw new RuntimeException('Не удалось создать manual hero-блок для cache smoke');
    }

    $manualHydrated = $model->hydrateBlockForRender($model->getBlockById($manualBlockId), [
        'mode'    => 'widget',
        'surface' => 'widget',
    ]);
    $manualProfile = $model->buildRenderCacheProfile($manualHydrated, [
        'mode'    => 'widget',
        'surface' => 'widget',
    ]);

    smoke_assert(!empty($manualProfile['cacheEligible']), 'Manual-first блок потерял cache eligibility');
    smoke_assert(($manualProfile['adapterContext']['hash'] ?? '') === 'manual', 'Manual-first блок неожиданно получил dynamic adapter hash');

    echo "Runtime cache smoke: OK\n";
    echo ' - ctype: ' . $sampleCtype . "\n";
    echo ' - hero context A key: ' . (string) ($profileA['cacheKey'] ?? '') . "\n";
    echo ' - hero context B key: ' . (string) ($profileB['cacheKey'] ?? '') . "\n";
    echo ' - faq key: ' . (string) ($faqProfile['cacheKey'] ?? '') . "\n";
    echo ' - manual key: ' . (string) ($manualProfile['cacheKey'] ?? '') . "\n";

    if ($heroBlockId > 0) {
        $model->deleteBlock($heroBlockId);
    }
    if ($faqBlockId > 0) {
        $model->deleteBlock($faqBlockId);
    }
    if ($manualBlockId > 0) {
        $model->deleteBlock($manualBlockId);
    }

    exit(0);
} catch (Throwable $exception) {
    if ($heroBlockId > 0) {
        $model->deleteBlock($heroBlockId);
    }
    if ($faqBlockId > 0) {
        $model->deleteBlock($faqBlockId);
    }
    if ($manualBlockId > 0) {
        $model->deleteBlock($manualBlockId);
    }

    smoke_fail($exception->getMessage());
}